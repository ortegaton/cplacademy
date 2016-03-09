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

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)) . '/core/class.php');
require_once(dirname(dirname(__FILE__)) . '/core/interfaces.php');
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/local/zilink/plugins/timetable/lib.php');

class timetable extends ZiLinkPluginBase implements iZiLinkBlockPlugin {

    public function GetBlockContent($block) {
        global $CFG, $OUTPUT, $PAGE, $USER, $COURSE, $DB;

        $content = $block->content;
        $region = $block->instance->region;

        $urlparams  = array('sesskey' => sesskey(), 'cid' => $this->course->id);
        $timetableurl = new moodle_url($this->httpswwwroot.'/local/zilink/plugins/timetable/view.php', $urlparams);
        $roombookingurl = new moodle_url($this->httpswwwroot.'/local/zilink/plugins/bookings/rooms/view.php', $urlparams);
        $maintenceurl = new moodle_url($this->httpswwwroot.'/local/zilink/plugins/bookings/rooms/maintenance.php', $urlparams);

        $css = html_writer::empty_tag('link', array('rel' => 'stylesheet',
                                                    'type' => 'text/css',
                                                    'href' => new moodle_url('/local/zilink/plugins/timetable/styles.css')));

        $warningicon   = html_writer::empty_tag('img', array(  'src' => $OUTPUT->pix_url('i/warning'),
                                                                'class' => 'icon',
                                                                'alt' => '' ));
        $eventicon     = html_writer::empty_tag('img', array(  'src' => $OUTPUT->pix_url('c/event'),
                                                                'class' => 'icon',
                                                                'alt' => '' ));

        try {
            if (isloggedin()) {
                if (!$this->person->Security()->IsAllowed('local/zilink:timetable_viewown')) {
                    if (!empty($USER->realuser)) {

                        $content->icons[] = '';
                        $content->items[] = '<hr style="width:170px">';

                        if (has_capability( 'moodle/site:config',
                                            context_course::instance(1),
                                            $DB->get_record('user', array('id' => $USER->realuser)))) {
                            try {

                                if ($this->person->GetPersonalData('timetable', true) &&
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
                } else {

                    if ($region == 'content' && in_array($region, $PAGE->blocks->get_regions())) {

                        $id = optional_param('id', 0, PARAM_INT);
                        if ($id == 0) {
                            $id = $USER->id;
                        }
                        
                        try {
                            
                            if ($USER->id == $id) {

                                $timetable = new ZiLinkTimetable($this->course->id);
                                $content->icons[] = '';
                                $content->items[] = $css . $timetable->GetTimetable(array(''));
                                return $content;
                            } else if ($this->person->Security()->IsAllowed('local/zilink:timetable_viewothers') && !empty($id)) {

                                $user = $DB->get_record('user', array('id' => $id), 'idnumber', MUST_EXIST);
                                try {

                                    $timetable = new ZiLinkTimetable($this->course->id);

                                    $content->icons[] = '';
                                    $content->items[] = $css . $timetable->GetTimetable(array('user_idnumber' => $user->idnumber));
                                    return $content;

                                } catch (Exception $e) {
                                    $content->icons[] = $warningicon;
                                    $content->items[] = get_string('zilink_plugin_data_missing', 'block_zilink');
                                }

                            }

                        } catch (Exception $e) {
                            
                            if (!empty($USER->realuser)) {                              
                                if (    has_capability('moodle/site:config', context_course::instance(1),
                                        $DB->get_record('user', array('id' => $USER->realuser)))) {

                                    try {

                                        if ($this->person->GetPersonalData('timetable') &&
                                            !$this->person->Security()->IsAllowed('local/zilink:timetable_viewown')) {

                                            $content->icons[] = $warningicon;
                                            $content->items[] = get_string('timetable', 'block_zilink') . ': ' .
                                                                get_string('zilink_plugin_security_failed', 'block_zilink');
                                        } else {
                                            $content->items[] = ' <div id="zilinkweekselector" />';
                                        }

                                    } catch (Exception $e) {
                                        
                                        $content->icons[] = $warningicon;
                                        $content->items[] = get_string('zilink_plugin_data_missing', 'block_zilink');
                                    }
                                } else {
                                   $content->items[] = ' <div id="zilinkweekselector" />'; 
                                }
                            } else {
                                $content->items[] = ' <div id="zilinkweekselector" />';
                            }
                            
                        }
                    } else {

                        try {
                            if ($this->person->GetPersonalData('timetable', true)) {

                                $content->icons[] = $eventicon;
                                $content->items[] = html_writer::link(  $timetableurl,
                                                                        'My Timetable');

                                if ($this->person->Security()->IsAllowed('local/zilink:bookings_rooms_viewown') || $this->person->Security()->IsAllowed('local/zilink:bookings_rooms_viewalternative')) {
                                        
                                    if ($CFG->zilink_bookings_system == 'internal') {
                                        $content->icons[] = $eventicon;
                                        $content->items[] = html_writer::link($roombookingurl,
                                                                              get_string('roombooking_myroombooking', 'block_zilink'));
                                    } else if ($CFG->zilink_bookings_system == 'external') {
                                        
                                        if (!empty($CFG->zilink_bookings_rooms_alternative_link)) {
                                            $content->icons[] = $eventicon;
                                            $content->items[] = html_writer::link(  $CFG->zilink_bookings_rooms_alternative_link,
                                                                              get_string('roombooking_myroombooking', 'block_zilink'),
                                                                              array( 'target' => '_new'));
                                        }
                                    } else if ($CFG->zilink_bookings_system == 'schoolbooking'){
                                        
                                        if (!empty($CFG->zilink_bookings_rooms_schoolbooking_link)) {
                                            $content->icons[] = $eventicon;
                                            $content->items[] = html_writer::link( 'https://secure.schoolbooking.com/'. $CFG->zilink_bookings_rooms_schoolbooking_link,
                                                                                  get_string('roombooking_myroombooking', 'block_zilink'),
                                                                                  array( 'target' => '_new'));
                                        }
                                    }

                                } else {

                                    if (!empty($USER->realuser)) {

                                        $content->icons[] = '';
                                        $content->items[] = '<hr style="width:170px">';

                                        if ($this->person->Security()->IsAllowed('moodle/site:config')) {
                                            $content->icons[] = $warningicon;
                                            $content->items[] = get_string('bookings_rooms', 'local_zilink') . ': ' .
                                                                get_string('zilink_plugin_security_failed', 'block_zilink');
                                        }
                                    }
                                }
                            }
                        } catch (Exception $e) {

                            $content->icons[] = $warningicon;
                            $content->items[] = get_string('timetable_not_available', 'block_zilink');
                        }
                    }
                }

                if ($this->person->Security()->IsAllowed('local/zilink:bookings_rooms_maintenance_manage')
                    && ($region <> 'content' ||  ($region == 'content' &&
                    !in_array($region, $PAGE->blocks->get_regions())))) {

                    $content->icons[] = $eventicon;
                    $content->items[] = html_writer::link($maintenceurl,
                                                        get_string('bookings_rooms_maintenance', 'local_zilink'),
                                                        array('title' => get_string('bookings_rooms_maintenance', 'local_zilink')));

                } else {
                    if (!empty($USER->realuser)) {

                        $content->icons[] = '';
                        $content->items[] = '<hr style="width:170px">';

                        if ($this->person->Security()->IsAllowed('moodle/site:config')) {
                            $content->icons[] = $warningicon;
                            $content->items[] = get_string('bookings_rooms_maintenance', 'local_zilink') . ': ' .
                            get_string('zilink_plugin_security_failed', 'block_zilink');
                        }
                    }
                }
            } else {
                $content = 'null';
                
            }
        } catch (Exception $e) {

            if ($CFG->debug == DEBUG_DEVELOPER && $this->person->Security()->IsAllowed('moodle/site:config')) {

                $content->icons[] = $warningicon;
                $content->items[] = $e->getMessage();

            } else {
                $content = null;
            }

        }

        return $content;
    }

    public function RequireContentRegion() {
        return true;
    }

    public function SetTitle() {
        return get_string('timetable', 'block_zilink');
    }

    public function HideHeader() {
        return false;
    }

    public function Cron() {
        return false;
    }

}
