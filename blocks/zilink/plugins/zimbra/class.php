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

class zimbra extends ZiLinkPluginBase implements iZiLinkBlockPlugin {

    public function __construct($courseid = null, $instanceid = null) {
        global $DB;

        parent::__construct($courseid, $instanceid,false);
    }

    public function GetBlockContent($block) {
        global $CFG, $OUTPUT, $PAGE, $USER, $COURSE, $DB;

        $content = $block->content;
        $courses = array();
        if (isloggedin()) {
            if (!isset($CFG->zilink_zimbra_link) || !isset($CFG->zilink_zimbra_url)) {
                $content->icons[] = '';
                $content->items[] = 'Please configure the block is block settings';
            } else {
                if ($this->person->Security()->IsAllowed('block/zilink:zimbra_view') || true) {
                    $params = array('cid' => $this->course->id, 'instanceid' => $this->instance->id, 'sesskey' => sesskey());
                    
                    if(!empty($CFG->zilink_zimbra_link)) {
                        $content->icons[] = $OUTPUT->pix_icon('i/email', get_string('zimbra_viewemail', 'block_zilink'));
                        $content->items[] = html_writer::link(new moodle_url('/local/zilink/plugins/zimbra/view.php', $params),
                                                         $CFG->zilink_zimbra_link);
                    }
                } else {
                    if (!empty($USER->realuser)) {
                        $userid = $USER->realuser;
                    } else {
                        $userid = $USER->id;
                    }

                    if (has_capability('moodle/site:config',
                                        context_course::instance(1),
                                        $DB->get_record('user', array('id' => $userid)))) {
                        $content->icons[] = '';
                        $content->items[] = 'Failed Security Check';
                    }
                }
            }
        }
        return $content;
    }

    public function Display() {
        global $CFG, $USER;
        $timestamp = time() * 1000;
        $preauthtoken = hash_hmac("sha1", $USER->email . "|name|0|" . $timestamp, $CFG->zilink_zimbra_preauth_key);
        $preauthurl = $CFG->zilink_zimbra_url . "?account=" . $USER->email .
                        "&by=name&timestamp=" . $timestamp . "&expires=0&preauth=" .
                        $preauthtoken . '&redirectURL=/zimbra/h/';
        header("Location: $preauthurl");
    }

    public function RequireContentRegion() {
        return false;
    }

    public function SetTitle() {
        return get_string('zimbra', 'block_zilink');
    }

    public function HideHeader() {
        return false;
    }

    public function Cron() {
        return false;
    }
}