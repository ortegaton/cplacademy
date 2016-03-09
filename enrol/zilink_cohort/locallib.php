<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Defines the settings for the ZiLink local
 *
 * @package     enrol_zilink_cohort
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


class enrol_zilink_cohort_handler {
    
    public function member_added($ca) {
        global $DB;

        if (!enrol_is_enabled('zilink_cohort')) {
            return true;
        }

        // Does any enabled cohort instance want to sync with this cohort?
        $sql = "SELECT e.*, r.id as roleexists
                  FROM {enrol} e
             LEFT JOIN {role} r ON (r.id = e.roleid)
                 WHERE e.customint1 = :cohortid AND e.enrol = 'zilink_cohort'
              ORDER BY e.id ASC";
        if (!$instances = $DB->get_records_sql($sql, array('cohortid'=>$ca->cohortid))) {
            return true;
        }

        $plugin = enrol_get_plugin('zilink_cohort');
        
        foreach ($instances as $instance) {
            if ($instance->status != ENROL_INSTANCE_ENABLED ) {
                // No roles for disabled instances.
                $instance->roleid = 0;
            } else if ($instance->roleid and !$instance->roleexists) {
                // Invalid role - let's just enrol, they will have to create new sync and delete this one.
                $instance->roleid = 0;
            }
            unset($instance->roleexists);
            // No problem if already enrolled.
            $plugin->enrol_user($instance, $ca->userid, $instance->roleid, 0, 0, ENROL_USER_ACTIVE);

            // Sync groups.
            if (isset($instance->customint2) && !empty($instance->customint2)) {
                if (!groups_is_member($instance->customint2, $ca->userid)) {
                    if ($group = $DB->get_record('groups', array('id'=>$instance->customint2, 'courseid'=>$instance->courseid))) {
                        groups_add_member($group->id, $ca->userid, 'enrol_zilink_cohort', $instance->id);
                    }
                }
            }
        }

        return true;
    }

    public function member_removed($ca) {
        global $DB;

        // Does anything want to sync with this cohort?
        if (!$instances = $DB->get_records('enrol', array('customint1'=>$ca->cohortid, 'enrol'=>'zilink_cohort'), 'id ASC')) {
            return true;
        }
        
        $plugin = enrol_get_plugin('zilink_cohort');
        $unenrolaction = $plugin->get_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);
        
        
        foreach ($instances as $instance) {
            if (!$ue = $DB->get_record('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$ca->userid))) {
                continue;
            }
            if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                $plugin->unenrol_user($instance, $ca->userid);

            } else {
                if ($ue->status != ENROL_USER_SUSPENDED) {
                    $plugin->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                    $context = context_course::instance($instance->courseid);
                    role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$context->id, 'component'=>'enrol_zilink_cohort', 'itemid'=>$instance->id));
                }
            }
        }

        return true;
    }

    public function deleted($cohort) {
        global $DB;


        // Does anything want to sync with this cohort?
        if (!$instances = $DB->get_records('enrol', array('customint1'=>$cohort->id, 'enrol'=>'zilink_cohort'), 'id ASC')) {
            return true;
        }
        
        $plugin = enrol_get_plugin('zilink_cohort');
        $unenrolaction = $plugin->get_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);

        foreach ($instances as $instance) {
            if ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                $context = context_course::instance($instance->courseid);
                role_unassign_all(array('contextid'=>$context->id, 'component'=>'enrol_cohort', 'itemid'=>$instance->id));
                $plugin->update_status($instance, ENROL_INSTANCE_DISABLED);
            } else {
                $plugin->delete_instance($instance);
            }
        }

        return true;
    }
}

/**
 * Sync all cohort course links.
 * @param int $courseid one course, empty mean all
 * @return void
 */
function enrol_zilink_cohort_sync($courseid = NULL, $verbose = false) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/group/lib.php');
    
    // Purge all roles if cohort sync disabled, those can be recreated later here by cron or CLI.
    if (!enrol_is_enabled('zilink_cohort')) {
        if ($verbose) {
            mtrace('ZiLink Cohort sync plugin is disabled');
        }
        return 2;
    }
    
    @set_time_limit(0);
    raise_memory_limit(MEMORY_HUGE);

    $allroles = get_all_roles();
    $instances = array(); //cache
    
    $plugin = enrol_get_plugin('zilink_cohort');
    $unenrolaction = $plugin->get_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);

    //$onecourse = $courseid ? "AND e.courseid = :courseid" : "";

    if($verbose) {
        mtrace('Starting ZiLink student course enrolment synchronisation');
    }

    // Iterate through all not enrolled yet users.
    $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    $sql = "SELECT cm.userid, e.id AS enrolid, ue.status, e.courseid as courseid, cm.cohortid as cohortid
              FROM {cohort_members} cm
              JOIN {enrol} e ON (e.customint1 = cm.cohortid AND e.enrol = 'zilink_cohort' $onecourse)
         LEFT JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = cm.userid)
             WHERE ue.id IS NULL OR ue.status = :suspended";
    $params = array();
    $params['courseid'] = $courseid;
    $params['suspended'] = ENROL_USER_SUSPENDED;
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $ue) {
        if (!isset($instances[$ue->enrolid])) {
            $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
        }
        
        if(!$DB->record_exists('course',array('id' => $instances[$ue->enrolid]->courseid)))
                continue;
        
        if(!$DB->record_exists('user',array('id' => $ue->userid,'deleted' => 0)))
                continue;
        
        $instance = $instances[$ue->enrolid];
        if ($ue->status == ENROL_USER_SUSPENDED) {
            $plugin->update_user_enrol($instance, $ue->userid, ENROL_USER_ACTIVE);
            if ($verbose) {
                mtrace("  unsuspending: $ue->userid ==> $instance->courseid via cohort $instance->customint1");
            }
        } else {
            $plugin->enrol_user($instance, $ue->userid);
            if ($verbose) {
                mtrace("  enrolling: $ue->userid ==> $instance->courseid via cohort $instance->customint1");
            }
            
            if($mdl_cohort = $DB->get_record('cohort',array('id' => $ue->cohortid)))
            {
                $groups = $DB->get_records('groups',array('courseid' => $ue->courseid, 'name' => $mdl_cohort->name ));
                
                foreach($groups as $group)
                {
                    if(!is_object($group))
                    {
                        $mdl_course = $DB->get_record('course',array('id' => $ue->courseid));
                        if($verbose) {
                            mtrace('   creating group '.$mdl_cohort->name. ' in course '.$mdl_course->shortname);
                        }
                        $group = new stdClass();
                        $group->courseid = $ue->courseid;
                        $group->name = $mdl_cohort->name;
                        $group->id = $DB->insert_record('groups', $group);
                    }
                        
                    groups_add_member($group->id, $ue->userid);
                    
                    $list[$ue->courseid][$group->id] = $mdl_cohort->name;
                                 
                   
                }
            }
        }
    }

    if(!empty($list))
    {
        foreach($list as $courseid => $groups) {
                
            foreach($groups as $groupid => $name) {
                
                $count = 0;
                
                $groupings = $DB->get_records('groupings', array('courseid'=>$courseid, 'name'=> $name));
                
                if(count($groupings) > 1) {
                    
                    foreach($groupings as $grouping)
                    {
        
                        if($count == 0) {
                            $count++;
                            continue;
                        }
                        
                        $count++;
                        groups_delete_grouping($grouping);
                    }
                }
                    
                $grouping = $DB->get_record('groupings', array('courseid'=>$courseid, 'name'=>$name));
        
                if(!is_object($grouping))
                {
                    $grouping = new stdClass();
                    $grouping->courseid = $courseid;
                    $grouping->name = $name;
                    
                    $grouping->id = groups_create_grouping($grouping);
                }
                
                if(!$DB->record_exists('groupings_groups', array('groupingid'=>$grouping->id, 'groupid'=>$groupid))) {
                    groups_assign_grouping($grouping->id, $groupid);
                    $course = $DB->get_record('course', array('id' => $courseid));
                    
                    if($verbose) {
                        mtrace('    * creating grouping '. $name . ' in course '. $course->shortname .'.');
                    }
                }
                
            }
        }
    }
    $rs->close();

    // Unenrol as necessary.
    $sql = "SELECT ue.*, e.courseid
              FROM {user_enrolments} ue
              JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'zilink_cohort' $onecourse)
         LEFT JOIN {cohort_members} cm ON (cm.cohortid = e.customint1 AND cm.userid = ue.userid)
             WHERE cm.id IS NULL";
    $rs = $DB->get_recordset_sql($sql, array('courseid'=>$courseid));
    foreach($rs as $ue) {
        if (!isset($instances[$ue->enrolid])) {
            $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
        }
        $instance = $instances[$ue->enrolid];
        if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
            // Remove enrolment together with group membership, grades, preferences, etc.
            
         if(!$DB->record_exists('course',array('id' => $ue->courseid)))
                continue;
        
            if(!$DB->record_exists('user',array('id' => $ue->userid,'deleted' => 0)))
                continue;
            
            $plugin->unenrol_user($instance, $ue->userid);
            if ($verbose) {
                mtrace("  unenrolling: $ue->userid ==> $instance->courseid via cohort $instance->customint1");
            }

        } else { // ENROL_EXT_REMOVED_SUSPENDNOROLES
            // Just disable and ignore any changes.
            if ($ue->status != ENROL_USER_SUSPENDED) {
                $plugin->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                $context = context_course::instance($instance->courseid);
                role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$context->id, 'component'=>'enrol_zilink_cohort', 'itemid'=>$instance->id));
                if ($verbose) {
                    mtrace("  suspending and unsassigning all roles: $ue->userid ==> $instance->courseid");
                }
            }
        }
    }
    $rs->close();
    unset($instances);


    // Now assign all necessary roles to enrolled users - skip suspended instances and users.
    $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    $sql = "SELECT e.roleid, ue.userid, c.id AS contextid, e.id AS itemid, e.courseid
              FROM {user_enrolments} ue
              JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'zilink_cohort' AND e.status = :statusenabled $onecourse)
              JOIN {role} r ON (r.id = e.roleid)
              JOIN {context} c ON (c.instanceid = e.courseid AND c.contextlevel = :coursecontext)
         LEFT JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.userid = ue.userid AND ra.itemid = e.id AND ra.component = 'enrol_zilink_cohort' AND e.roleid = ra.roleid)
             WHERE ue.status = :useractive AND ra.id IS NULL";
    $params = array();
    $params['statusenabled'] = ENROL_INSTANCE_ENABLED;
    $params['useractive'] = ENROL_USER_ACTIVE;
    $params['coursecontext'] = CONTEXT_COURSE;
    $params['courseid'] = $courseid;

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $ra) {
        
        if(!$DB->record_exists('user',array('id' => $ra->userid,'deleted' => 0)))
                continue;
                
        role_assign($ra->roleid, $ra->userid, $ra->contextid, 'enrol_zilink_cohort', $ra->itemid);
        if ($verbose) {
            mtrace("  assigning role: $ra->userid ==> $ra->courseid as ".$allroles[$ra->roleid]->shortname);
        }
    }
    $rs->close();

    // Remove unwanted roles - sync role can not be changed, we only remove role when unenrolled.
    $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    $sql = "SELECT ra.roleid, ra.userid, ra.contextid, ra.itemid, e.courseid
              FROM {role_assignments} ra
              JOIN {context} c ON (c.id = ra.contextid AND c.contextlevel = :coursecontext)
              JOIN {enrol} e ON (e.id = ra.itemid AND e.enrol = 'zilink_cohort' $onecourse)
         LEFT JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = ra.userid AND ue.status = :useractive)
             WHERE ra.component = 'enrol_zilink_cohort' AND (ue.id IS NULL OR e.status <> :statusenabled)";
    $params = array();
    $params['statusenabled'] = ENROL_INSTANCE_ENABLED;
    $params['useractive'] = ENROL_USER_ACTIVE;
    $params['coursecontext'] = CONTEXT_COURSE;
    $params['courseid'] = $courseid;

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $ra) {
        
        if(!$DB->record_exists('user',array('id' => $ra->userid,'deleted' => 0)))
                continue;
        
        role_unassign($ra->roleid, $ra->userid, $ra->contextid, 'enrol_zilink_cohort', $ra->itemid);
        if ($verbose) {
            mtrace("  unassigning role: $ra->userid ==> $ra->courseid as ".$allroles[$ra->roleid]->shortname);
        }
    }
    $rs->close();
    
    if($CFG->version >= 2012120300)
    {
        // Finally sync groups.
        $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    
        // Remove invalid.
        $sql = "SELECT gm.*, e.courseid, g.name AS groupname
                  FROM {groups_members} gm
                  JOIN {groups} g ON (g.id = gm.groupid)
                  JOIN {enrol} e ON (e.enrol = 'zilink_cohort' AND e.courseid = g.courseid $onecourse)
                  JOIN {user_enrolments} ue ON (ue.userid = gm.userid AND ue.enrolid = e.id)
                 WHERE gm.component='enrol_zilink_cohort' AND gm.itemid = e.id AND g.id <> e.customint2";
        $params = array();
        $params['courseid'] = $courseid;
    
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach($rs as $gm) {
            groups_remove_member($gm->groupid, $gm->userid);
            if ($verbose) {
                mtrace("  removing user from group: $gm->userid ==> $gm->courseid - $gm->groupname");
            }
        }
        $rs->close();
        
        // Add missing.
        $sql = "SELECT ue.*, g.id AS groupid, e.courseid, g.name AS groupname
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'zilink_cohort' $onecourse)
                  JOIN {groups} g ON (g.courseid = e.courseid AND g.id = e.customint2)
             LEFT JOIN {groups_members} gm ON (gm.groupid = g.id AND gm.userid = ue.userid)
                 WHERE gm.id IS NULL";
        $params = array();
        $params['courseid'] = $courseid;
    
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach($rs as $ue) {
            groups_add_member($ue->groupid, $ue->userid, 'enrol_zilink_cohort', $ue->enrolid);
            if ($verbose) {
                mtrace("  adding user to group: $ue->userid ==> $ue->courseid - $ue->groupname");
            }
        }
        $rs->close();
        
        if ($verbose) {
            mtrace('...ZiLink Cohort enrolment synchronisation finished.');
        }
    }
    return 0;
}