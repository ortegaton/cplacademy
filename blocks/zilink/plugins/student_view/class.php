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
 * Defines the student for the ZiLink block sub plugin timetable_week
 *
 * @package     block_zilink
 * @subpackage    timetable_week
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/core/class.php');
require_once(dirname(dirname(__FILE__)) . '/core/interfaces.php');

class student_view extends ZiLinkPluginBase implements iZiLinkBlockPlugin {

    public function __construct($courseid = null, $instanceid = null) {
        global $DB;

        parent::__construct($courseid, $instanceid);
    }

    public function GetBlockContent($block) {
        global $CFG, $OUTPUT, $PAGE, $USER, $COURSE, $DB;

        $content = $block->content;

        if (isloggedin()) {
            if (($this->person->Security()->IsAllowed('local/zilink:student_view'))) {

                    $content->icons[] = '<img src="' . $OUTPUT->pix_url('/i/user') . '" student="icon" alt="" />';
                    $content->items[] = html_writer::link(new moodle_url($this->httpswwwroot .
                                        '/local/zilink/plugins/student/view/interfaces/' .
                                        $CFG->zilink_student_view_interface . '/pages/index.php',
                                        array('sesskey' => sesskey(), 'cid' => $COURSE->id)),
                                        get_string('student_view_student', 'block_zilink'));
                
            } else {
                if (!empty($USER->realuser)) {
                    $userid = $USER->realuser;
                } else {
                    $userid = $USER->id;
                }

                if (has_capability('moodle/site:config', context_course::instance(1),
                    $DB->get_record('user', array('id' => $userid)))) {

                    $content->icons[] = '<img src="' . $OUTPUT->pix_url('i/warning') . '" class="icon" alt="" />';
                    $content->items[] = 'Failed Security Check';
                }
            }
        }
        return $content;
    }

    public function RequireContentRegion() {
        return false;
    }

    public function SetTitle() {
        return get_string('student_view', 'local_zilink');
    }

    public function HideHeader() {
        return false;
    }

    public function Cron() {
        return true;
    }

}
