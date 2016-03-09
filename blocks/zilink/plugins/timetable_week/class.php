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

require_once(dirname(dirname(__FILE__)).'/core/class.php');

class timetable_week extends ZiLinkPluginBase implements iZiLinkBlockPlugin {

    public function __construct($courseid = null, $instanceid = null) {
        global $DB;

        parent::__construct($courseid, $instanceid);
    }

    public function GetBlockContent($block) {
        global $CFG, $OUTPUT, $PAGE, $USER, $COURSE, $DB;

        $content = $block->content;
        $params = array('course' => $this->course->id,
                        'context' => $block->context->id,
                        'instance' => $block->instance->id,
                        'sesskey' => sesskey());

        $editurl = new moodle_url('/blocks/zilink/plugins/timetable_week/edit.php', $params);

        if ($block->page->user_is_editing() && $this->person->Security()->IsAllowed('moodle/site:config')) {
            $content->icons[] = '<img src="' . $OUTPUT->pix_url('t/add') . '" class="smallicon" alt="" />';
            $content->items[] = html_writer::link($editurl, get_string('timetable_week_configure_images', 'block_zilink'));
        } else {
            try {
                $timetable = $this->data->GetGlobalData('timetable', true);

                if ($timetable->Attribute('weeks') == 2) {
                    $content->icons[] = '';
                    if ($this->isCurrentWeekHoliday()) {
                        if (isset($block->config->holiday_imagename) && isset($block->config->holiday)) {
                            $image  = $block->config->holiday_imagename;
                            $imageid = $block->config->holiday;
                            $content->items[] = '<img style="max-width: 170px; max-height: 45px;" align="middle" src="'.
                                                $CFG->httpswwwroot.'/pluginfile.php/'.$block->context->id.
                                                '/block_zilink/content/'.$imageid.'/'.rawurlencode($image).'" />';
                        } else {
                            $content->icons[] = '<img src="' . $OUTPUT->pix_url('t/add') . '" class="smallicon" alt="" />';
                            $content->items[] = html_writer::link($editurl,
                                                                    get_string('timetable_week_configure_images', 'block_zilink'));
                        }
                    } else if ($this->GetWeek() == 1) {
                        if (isset($block->config->week1_imagename) && isset($block->config->week1)) {
                            $image  = $block->config->week1_imagename;
                            $imageid = $block->config->week1;
                            $content->items[] = '<img style="max-width: 170px; max-height: 45px;" align="middle" src="'.
                                                $CFG->httpswwwroot.'/pluginfile.php/'.$block->context->id.
                                                '/block_zilink/content/'.$imageid.'/'.rawurlencode($image).'" />';
                        } else {
                            $content->icons[] = '<img src="' . $OUTPUT->pix_url('t/add') . '" class="smallicon" alt="" />';
                            $content->items[] = html_writer::link($editurl,
                                                                    get_string('timetable_week_configure_images', 'block_zilink'));
                        }
                    } else if ($this->GetWeek() == 2) {
                        if (isset($block->config->week2_imagename) && isset($block->config->week2)) {
                            $image = $block->config->week2_imagename;
                            $imageid = $block->config->week2;
                            $content->items[] = '<img style="max-width: 170px; max-height: 45px;" align="middle" src="'.
                                                $CFG->httpswwwroot.'/pluginfile.php/'.$block->context->id.
                                                '/block_zilink/content/'.$imageid.'/'.rawurlencode($image).'" />';
                        } else {
                            $content->icons[] = '<img src="' . $OUTPUT->pix_url('t/add') . '" class="smallicon" alt="" />';
                            $content->items[] = html_writer::link($editurl,
                                                                    get_string('timetable_week_configure_images', 'block_zilink'));
                        }
                    } else {
                        return $content = null;
                    }
                } else {
                    $content = null;
                }
            } catch (Exception $e) {
                // Hide if error.
            }
        }
        return $content;
    }
    public function RequireContentRegion() {
        return false;
    }
    public function SetTitle() {
        return get_string('timetable_week', 'block_zilink');
    }
    public function HideHeader() {
        return false;
    }
    public function Cron() {
        return false;
    }
}