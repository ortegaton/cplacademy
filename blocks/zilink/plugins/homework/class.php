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
 * Defines the class for the ZiLink block sub plugin timetable_week
 *
 * @package     block_zilink
 * @subpackage    timetable_week
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/core/class.php');
require_once(dirname(dirname(__FILE__)) . '/core/interfaces.php');

class homework extends ZiLinkPluginBase implements iZiLinkBlockPlugin {

    public function __construct($courseid = null, $instanceid = null) {
        global $DB;

        parent::__construct($courseid, $instanceid,false);
    }

    public function GetBlockContent($block) {
        global $CFG, $OUTPUT, $PAGE, $USER, $COURSE, $DB;

        $content = $block->content;
        $courses = array();
        if (isloggedin()) {

            if ($this->person->Security()->IsAllowed('local/zilink:homework_report_subject_teacher') ||
                $this->person->Security()->IsAllowed('local/zilink:homework_report_subject_leader') ||
                $this->person->Security()->IsAllowed('local/zilink:homework_report_senior_management_team')) {
                $content->icons[] = '<img src="' . $OUTPUT->pix_url('c/event') . '" class="icon" alt="" />';

                $params = array('cid' => $this->course->id, 'sesskey' => sesskey());
                $url = new moodle_url($this->httpswwwroot.'/local/zilink/plugins/homework/report/interfaces/'.
                                      $CFG->zilink_homework_report_interface . '/pages/view.php',
                                      $params);
                $content->items[] = html_writer::link($url,
                                                      get_string('homework_report_link', 'local_zilink'),
                                                      array('title' => get_string('homework_report_link', 'local_zilink')));
            } else {
                if (!empty($USER->realuser)) {
                    $userid = $USER->realuser;
                } else {
                    $userid = $USER->id;
                }

                if (has_capability('moodle/site:config',
                    context_course::instance(1),
                    $DB->get_record('user', array('id' => $userid)))) {
                        $content->icons[] = '<img src="' . $OUTPUT->pix_url('i/warning') . '" class="icon" alt="" />';
                        $content->items[] = 'Failed Security Check';
                }
            }
            
            //if ($this->person->Security()->IsAllowed('local/zilink:homework_report_subject_teacher') ||
            //    $this->person->Security()->IsAllowed('local/zilink:homework_report_subject_leader') ||
            //    $this->person->Security()->IsAllowed('local/zilink:homework_report_senior_management_team')) {
            
                $block->content = $content;
                if (!isset($block->config->homeworkoverview)) {
                    $block->config->homeworkoverview = 1;
                }
                $content = $this->show_zilinkhomework_overview($block);
                
        
                
            //}
            
        }
        return $content;
    }

    public function RequireContentRegion() {
        return false;
    }

    public function SetTitle() {
        return get_string('homework', 'block_zilink');
    }

    public function HideHeader() {
        return false;
    }

    public function Cron() {
        return true;
    }
    
    protected function show_zilinkhomework_overview($block) {
        global $COURSE, $DB;

        $content = $block->content;
        $content->items[] ='<hr>';
        $content->items[] ='Homework Progress';
        
        $allcourses = ($COURSE->format == 'site');
        if ($allcourses) {
            $mycourses = enrol_get_my_courses();
        } else {
            $mycourses = array($COURSE->id => $COURSE);
        }

        if (empty($mycourses)) {
            $content->items[] = get_string('homework_notenrolled', 'block_zilink');
            return $content;
        }

        $courseids = array();
        foreach ($mycourses as $id => $course) {
            $courseids[] = $id;
        }
        list($inorequal, $params) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $params['moduleid'] = $DB->get_field('modules', 'id', array('name' => 'zilinkhomework'));
        $sql = "SELECT ch.*, c.shortname, cm.id AS cmid
                    FROM {zilinkhomework} ch
                    JOIN {course} c ON ch.course = c.id
                    JOIN {course_modules} cm ON cm.instance = ch.id AND cm.module = :moduleid
                    WHERE ch.course $inorequal AND c.visible = 1";
        $zilinkhomeworks = $DB->get_records_sql($sql, $params);
        foreach ($zilinkhomeworks as $zilinkhomework) {
            $modinfo = get_fast_modinfo($zilinkhomework->course);
            $cminfo = $modinfo->get_cm($zilinkhomework->cmid);
            if (!$cminfo->uservisible) {
                // Hidden by show/hide, groupings, conditional access, etc.
                unset($zilinkhomeworks[$zilinkhomework->id]);
            }
        }

        //$content->items = array();

        $zilinkhomeworks = $this->get_user_progress($zilinkhomeworks);
        $zilinkhomeworks = $this->sort_zilinkhomeworks($zilinkhomeworks, 20);

        foreach ($zilinkhomeworks as $zilinkhomework) {
            $viewurl = new moodle_url('/mod/zilinkhomework/report.php', array('id'=>$zilinkhomework->cmid, 'action' => 'showprogressbars', 'submit' => 'Show progress bars', 'sesskey' => sesskey()));
            if ($allcourses) {
                $content->items[] = html_writer::tag('h4', $zilinkhomework->shortname);
            }
            $info = format_string($zilinkhomework->name);
            $info .= html_writer::empty_tag('br', array('class' => 'clearer'));
            $info .= $this->print_user_progressbar($zilinkhomework);
            $content->items[] = html_writer::link($viewurl, $info);
        }

        return $content;
    }

    /**
     * Get the progress for each zilinkhomework.
     *
     * @param object[] $zilinkhomeworks
     * @return object[]
     */
    protected function get_user_progress($zilinkhomeworks) {
        global $DB, $USER, $CFG;

        if (empty($zilinkhomeworks)) {
            return $zilinkhomeworks;
        }

        // Get all the items for all the zilinkhomeworks.
        list($csql, $params) = $DB->get_in_or_equal(array_keys($zilinkhomeworks), SQL_PARAMS_NAMED);
        $items = $DB->get_records_select('zilinkhomework_item', "zilinkhomework $csql AND userid = 0 AND itemoptional = ".ZILINKHOMEWORK_OPTIONAL_NO." AND hidden = ".ZILINKHOMEWORK_HIDDEN_NO, $params, 'zilinkhomework', 'id, zilinkhomework, grouping');
        if (empty($items)) {
            return $zilinkhomeworks;
        }

        // Get all the checks for this user for these items.
        list($isql, $params) = $DB->get_in_or_equal(array_keys($items), SQL_PARAMS_NAMED);
        $params['userid'] = $USER->id;
        $checkmarks = $DB->get_records_select('zilinkhomework_check', "item $isql AND userid = :userid", $params, 'item',
                                              'item, usertimestamp, teachermark');

        // If 'groupmembersonly' is enabled, get a list of groupings the user is a member of.
        $groupings = !empty($CFG->enablegroupmembersonly) && !empty($CFG->enablegroupmembersonly);
        $groupingids = array();
        if ($groupings) {
            $sql = "
            SELECT gs.groupingid
              FROM {groupings_groups} gs
              JOIN {groups_members} gm ON gm.groupid = gs.groupid
             WHERE gm.userid = ?
            ";
            $groupingids = $DB->get_fieldset_sql($sql, array($USER->id));
        }
        $zilinkhomework = null;

        // Loop through all items, counting those visible to the user and the total number of checkmarks for them.
        foreach ($items as $item) {
            $zilinkhomework = $zilinkhomeworks[$item->zilinkhomework];
            if ($groupings && $zilinkhomework->autopopulate) {
                // If the item has a grouping, check against the grouping memberships for this user.
                if ($item->grouping && !in_array($item->grouping, $groupingids)) {
                    continue;
                }
            }
            if (!isset($zilinkhomework->totalitems)) {
                $zilinkhomework->totalitems = 0;
                $zilinkhomework->checked = 0;
            }
            $zilinkhomework->totalitems++;
            if (isset($checkmarks[$item->id])) {
                if ($zilinkhomework->teacheredit == CHECKLIST_MARKING_STUDENT) {
                    if ($checkmarks[$item->id]->usertimestamp) {
                        $zilinkhomework->checked++;
                    }
                } else {
                    if ($checkmarks[$item->id]->teachermark == CHECKLIST_TEACHERMARK_YES) {
                        $zilinkhomework->checked++;
                    }
                }
            }
        }

        // Calculate the percentage for each zilinkhomework.
        foreach ($zilinkhomeworks as $zilinkhomework) {
            if (empty($zilinkhomework->totalitems)) {
                $zilinkhomework->percent = 0;
            } else {
                $zilinkhomework->percent = $zilinkhomework->checked * 100.0 / $zilinkhomework->totalitems;
            }
        }

        return $zilinkhomeworks;
    }

    /**
     * Sort the zilinkhomeworks (incomplete first, in ascending order of completeness,
     * unstarted next, then complete zilinkhomeworks).
     *
     * @param object[] $zilinkhomeworks
     * @param int $maxdisplay only return this many zilinkhomeworks
     * @return object[] the sorted zilinkhomeworks
     */
    protected function sort_zilinkhomeworks($zilinkhomeworks, $maxdisplay) {
        uasort($zilinkhomeworks, function($a, $b) {
            if (!is_object($a) || !is_object($b) || !isset($a->percent) || !isset($b->percent) || $a->percent == $b->percent) {
                return 0; // Same, so no defined sort order.
            }
            if ($a->percent == 0) {
                if ($b->percent == 100) {
                    return -1; // Completed zilinkhomeworks at the end.
                }
                return 1; // Incomplete zilinkhomework always before unstarted zilinkhomeworks.
            }
            if ($a->percent == 100) {
                return 1; // Completed zilinkhomeworks at the end.
            }
            if ($b->percent == 0) {
                return -1; // Unstarted zilinkhomeworks after incomplete zilinkhomeworks (but before completed).
            }
            if ($a->percent > $b->percent) {
                return 1;
            }
            return -1;
        });

        return array_slice($zilinkhomeworks, 0, $maxdisplay);
    }

    protected function print_user_progressbar($zilinkhomework) {
        global $OUTPUT;
        if (empty($zilinkhomework->totalitems)) {
            return '';
        }

        $percent = $zilinkhomework->checked * 100 / $zilinkhomework->totalitems;
        $width = '150px';

        $output = '<div class="zilinkhomework_progress_outer" style="width: '.$width.';" >';
        $output .= '<div class="zilinkhomework_progress_inner" style="width:'.$percent.'%; background-image: url('.$OUTPUT->pix_url('progress','zilinkhomework').');" >&nbsp;</div>';
        $output .= '</div>';
        $output .= '<br style="clear:both;" />';

        return $output;
    }

    function import_zilinkhomework_plugin() {
        global $CFG, $DB;

        $chk = $DB->get_record('modules', array('name'=>'zilinkhomework'));
        if (!$chk) {
            return false;
        }

        $version = get_config('mod_zilinkhomework', 'version');
        if (!$version && isset($chk->version)) {
            $version = $chk->version;
        }

        if ($version < 2010041800) {
            return false;
        }

        if (!file_exists($CFG->dirroot.'/mod/zilinkhomework/locallib.php')) {
            return false;
        }

        require_once($CFG->dirroot.'/mod/zilinkhomework/locallib.php');
        return true;
    }

    

    
    

}