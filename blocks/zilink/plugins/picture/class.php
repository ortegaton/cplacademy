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


require_once(dirname(dirname(__FILE__)).'/core/class.php');
require_once(dirname(dirname(__FILE__)).'/core/interfaces.php');

class picture extends ZiLinkPluginBase implements iZiLinkBlockPlugin {

    public function __construct($courseid = null, $instanceid = null) {
        global $DB;

        parent::__construct($courseid, $instanceid,false);

    }

    public function GetBlockContent($block) {

        global $CFG, $OUTPUT, $PAGE, $USER, $COURSE, $DB;

        $content = $block->content;

        if ($this->person->Security()->IsAllowed('block/zilink:picture_view')) {
            
            $id = optional_param('id', 0, PARAM_INT);
            if ($id == 0) {
                $id = $USER->id;
            }
            
            try {
                if ($USER->id == $id) {
                    $picture = $this->person->GetPersonalData('picture');
                } else {
                    $user = $DB->get_record('user', array('id' => $id), 'idnumber', MUST_EXIST);
                    $picture = $this->person->GetPersonData('picture',$user->idnumber);
                }
    
                if (is_object($picture->picture)) {
                    $pic = (string)$picture->picture->picture->src;
                    if (!empty($pic)) {
                        $content->icons[] = '';
                        $content->items[] = '<img src="data:image/png;base64,'.$pic.' "/></p>';
                        return $content;
                    }
                }
            }catch (Exception $e) {

                if (!empty($USER->realuser)) {
                    if (    has_capability('moodle/site:config', context_course::instance(1),
                            $DB->get_record('user', array('id' => $USER->realuser)))) {

                        try {

                            if ($this->person->GetPersonalData('timetable') &&
                                !$this->person->Security()->IsAllowed('local/zilink:timetable_viewown')) {

                                $content->icons[] = $warningicon;
                                $content->items[] = get_string('timetable', 'block_zilink') . ': ' .
                                                    get_string('zilink_plugin_security_failed', 'block_zilink');
                            }

                        } catch (Exception $e) {

                            $content->icons[] = $warningicon;
                            $content->items[] = get_string('zilink_plugin_data_missing', 'block_zilink');
                        }
                    }
                }
            }
        }
        return $content;
    }

    function Display($idnumber = null) {

        if ($idnumber == null) {
            $picture = $this->person->GetPersonalData('picture');
        } else {
            $picture = $this->person->GetPersonData('picture', $idnumber);
        }

        if (is_object($picture->picture)) {
            $pic = (string)$picture->picture->picture->src;
            return '<img style="max-width:100%; max-height:100%;" src="data:image/jpeg;base64,'.$pic.'"/></p>';
        }
    }

    public function RequireContentRegion() {
        return false;
    }

    public function SetTitle() {
        return get_string('picture', 'block_zilink');
    }

    public function HideHeader() {
        return false;
    }

    public function Cron() {
        return false;
    }
}