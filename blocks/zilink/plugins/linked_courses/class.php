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

class linked_courses extends ZiLinkPluginBase implements iZiLinkBlockPlugin {

    public function __construct($courseid = null, $instanceid = null) {

        global $DB;

        parent::__construct($courseid, $instanceid,false);
        
    }

    public function GetBlockContent($block) {
        global $CFG, $OUTPUT, $PAGE, $USER, $COURSE, $DB;

        $content = $block->content;
        $courses = array();

        if (isloggedin() && ($block->page->course->id != SITEID)) {
            $cohortrecords = $DB->get_records('enrol',
                                              array('courseid' => $block->page->course->id,
                                                    'roleid' => 5,
                                                    'enrol' => 'zilink_cohort'));
            foreach ($cohortrecords as $record) {
                $courserecords = $DB->get_records('enrol',
                                                    array('customint1' => $record->customint1,
                                                            'roleid' => 5,
                                                            'enrol' => 'zilink_cohort'));

                foreach ($courserecords as $courserecord) {
                    if (!in_array($courserecord->courseid, $courses)) {
                        $courses[] = $courserecord->courseid;
                    }
                }
            }

            $path = $DB->get_record('course_categories', array('id' => $block->page->course->category));
            foreach ($courses as $course) {
                $mdlcourse = $DB->get_record('course', array('id' => $course));
                $cats = explode('/', $path->path);
                $context = context_coursecat::instance($mdlcourse->category);
                if (in_array($context->id, $cats)) {
                    $context = context_course::instance($mdlcourse->id);
                    if ($mdlcourse->visible || has_capability('moodle/course:viewhiddencourses', $context)) {
                        $icon = '';
                        $content->icons[] = $icon;
                        $content->items[] = '<a title=""'. s($mdlcourse->fullname). '"'.
                                             'href="'.$this->httpswwwroot.'/course/view.php?id='.$mdlcourse->id.'">'.
                                             $mdlcourse->fullname .'</a>';
                    }
                }
            }
        }
        return $content;
    }

    public function SetTitle() {
        return get_string('linked_courses', 'block_zilink');
    }

    public function RequireContentRegion() {
        return true;
    }

    public function HideHeader() {
        return false;
    }

    public function Cron() {
        return false;
    }

}
