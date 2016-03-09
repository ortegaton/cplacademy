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
 * Defines the edit page for the ZiLink block sub plugin timetable_week
 *
 * @package     block_zilink
 * @subpackage     timetable_week
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once(dirname(__FILE__). '/edit_form.php');
require_once($CFG->libdir.'/formslib.php');

$courseid = required_param('course', PARAM_INT);
$contextid = required_param('context', PARAM_INT);
$instanceid = required_param('instance', PARAM_INT);
$sesskey = required_param('sesskey', PARAM_INT);

confirm_sesskey($sesskey);
require_login();

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);

$urlparams = array();
$PAGE->https_required();
$PAGE->set_url('/blocks/zilink/plugins/timetable_week/edit.php', $urlparams);
$PAGE->verify_https_required();
$mform = new block_zilink_timetable_week_edit_form('', array('course' => $courseid, 'context' => $contextid ));

if ($mform->is_cancelled()) {
    redirect("$CFG->httpswwwroot/course/view.php?id=$courseid");
} else if ($data = $mform->get_data()) {

    file_save_draft_area_files($data->week1_filemanager,
                                $contextid, 'block_zilink',
                                'content', $data->week1_filemanager,
                                array('subdirs' => 0,
                                    'maxbytes' => 0,
                                    'maxfiles' => 1));

    file_save_draft_area_files($data->week2_filemanager,
                               $contextid, 'block_zilink',
                               'content', $data->week2_filemanager,
                               array('subdirs' => 0,
                                    'maxbytes' => 0,
                                    'maxfiles' => 1));

    file_save_draft_area_files($data->holiday_filemanager,
                                $contextid,
                                'block_zilink',
                                'content',
                                $data->holiday_filemanager,
                                array('subdirs' => 0,
                                      'maxbytes' => 0,
                                      'maxfiles' => 1));

    $config = $DB->get_record('block_instances', array('id' => $instanceid));
    $config = unserialize(base64_decode($config->configdata));

    $files = $DB->get_records('files', array('component' => 'block_zilink',
                                             'filearea' => 'content',
                                             'contextid' => $contextid,
                                             'itemid' => $data->week1_filemanager));
    foreach ($files as $file) {
        if ($file->filesize > 0) {
            $config->week1_imagename = $file->filename;
        }
    }

    $files = $DB->get_records('files', array('component' => 'block_zilink',
                                             'filearea' => 'content',
                                             'contextid' => $contextid,
                                             'itemid' => $data->week2_filemanager));
    foreach ($files as $file) {
        if ($file->filesize > 0) {
            $config->week2_imagename = $file->filename;
        }
    }

    $files = $DB->get_records('files', array('component' => 'block_zilink',
                                             'filearea' => 'content',
                                             'contextid' => $contextid,
                                             'itemid' => $data->holiday_filemanager));
    foreach ($files as $file) {
        if ($file->filesize > 0) {
            $config->holiday_imagename = $file->filename;
        }
    }

    $config->week1 = $data->week1_filemanager;
    $config->week2 = $data->week2_filemanager;
    $config->holiday = $data->holiday_filemanager;

    $DB->set_field('block_instances', 'configdata', base64_encode(serialize($config)), array('id' => $instanceid));
    redirect("$CFG->httpswwwroot/course/view.php?id=$courseid", null, 0);
} else {
    $PAGE->navbar->add(get_string('mycourses'));
    $PAGE->navbar->add($course->shortname, new moodle_url('/course/view.php', array('id' => $course->id)));
    $PAGE->navbar->add(get_string('timetable_week_configure_images', 'block_zilink'));

    $PAGE->set_title(get_string('course') . ': ' . $course->fullname);
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();

    $config = $DB->get_record('block_instances', array('id' => $instanceid));
    $config = unserialize(base64_decode($config->configdata));

    $defaults = new stdClass;
    $defaults->course = $courseid;
    $defaults->context = $contextid;
    $defaults->instance = $instanceid;
    $defaults->config = $config;

    $mform->set_data($defaults);
    $mform->display();
    echo $OUTPUT->footer();
}

unset($block);
unset($zilik);