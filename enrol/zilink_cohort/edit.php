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
 * Adds new instance of enrol_cohort to specified course.
 *
 * @package     enrol_zilink_cohort
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * */

require('../../config.php');
require_once("$CFG->dirroot/enrol/zilink_cohort/edit_form.php");
require_once("$CFG->dirroot/enrol/zilink_cohort/lib.php");
require_once("$CFG->dirroot/enrol/zilink_cohort/locallib.php");
require_once("$CFG->dirroot/group/lib.php");

$courseid = required_param('courseid', PARAM_INT);
$instanceid = optional_param('id', 0, PARAM_INT);

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('moodle/course:enrolconfig', $context);
require_capability('enrol/zilink_cohort:config', $context);

$PAGE->set_url('/enrol/zilink_cohort/edit.php', array('courseid'=>$course->id, 'id'=>$instanceid));
$PAGE->set_pagelayout('admin');

$jsdata = array(sesskey(),$CFG->httpswwwroot,$course->id);

$jsmodule = array(
                        'name'  =>  'enrol_zilink_cohort_list',
                        'fullpath'  =>  '/enrol/zilink_cohort/module.js',
                        'requires'  =>  array('base', 'node', 'io')
                    );

$PAGE->requires->js_init_call('M.enrol_zilink_cohort_list.init', $jsdata, false, $jsmodule);

$returnurl = new moodle_url('/enrol/instances.php', array('id'=>$course->id));
if (!enrol_is_enabled('zilink_cohort')) {
    redirect($returnurl);
}

$enrol = enrol_get_plugin('zilink_cohort');

if ($instanceid) {
    $instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'zilink_cohort', 'id'=>$instanceid), '*', MUST_EXIST);

} else {
    // No instance yet, we have to add new instance.
    if (!$enrol->get_newinstance_link($course->id)) {
        redirect($returnurl);
    }
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
    $instance = new stdClass();
    $instance->id         = null;
    $instance->courseid   = $course->id;
    $instance->enrol      = 'zilink_cohort';
    $instance->customint1 = ''; // Cohort id.
    $instance->customint2 = 0;  // Optional group id.
}

// Try and make the manage instances node on the navigation active.
$courseadmin = $PAGE->settingsnav->get('courseadmin');
if ($courseadmin && $courseadmin->get('users') && $courseadmin->get('users')->get('manageinstances')) {
    $courseadmin->get('users')->get('manageinstances')->make_active();
}

$mform = new enrol_zilink_cohort_edit_form(null, array($instance, $enrol, $course));

if ($mform->is_cancelled()) {
    redirect($returnurl);

} else if ($data = $mform->get_data()) {
    if ($data->id) {
        // NOTE: no cohort changes here!!!
        if ($data->roleid != $instance->roleid) {
            // The sync script can only add roles, for perf reasons it does not modify them.
            role_unassign_all(array('contextid'=>$context->id, 'roleid'=>$instance->roleid, 'component'=> 'enrol_zilink_cohort', 'itemid'=>$instance->id));
        }
        $instance->status       = 0;
        $instance->roleid       = $data->roleid;
        $instance->timemodified = time();
        $DB->update_record('enrol', $instance);
        
    }  else {
        
        foreach($data->cohortids as $cohortid)
        {
            $cohort = $DB->get_record('cohort',array('id' => $cohortid),'*', MUST_EXIST);
            $group = $DB->get_record('groups',array('name' => $cohort->name, 'courseid' => $course->id));
            if(empty($group)) 
            {
                $group = new stdClass();
                $group->courseid = $course->id;
                $group->idnumber = $cohort->idnumber;
                $group->name = $cohort->name;
                $group->id = $DB->insert_record('groups', $group);
            }
            $enrol->add_instance($course, array('name'=> $cohort->name, 'status'=> 0, 'customint1'=> $cohortid, 'roleid'=>$data->roleid, 'customint2'=>$group->id));
        }
    }
    enrol_zilink_cohort_sync($course->id);
    redirect($returnurl);
}

$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'enrol_zilink_cohort'));

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
