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
 * @package     local_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey());
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/cohorts/admin/enrolments.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($CFG->httpswwwroot.'/local/zilink/plugins/cohorts/admin/cohort_enrolment.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('cohorts_page_title', 'local_zilink');

admin_externalpage_setup('zilink_cohorts_settings_enrolments',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/cohorts/admin/enrolments.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->set_pagelayout('report');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('cohorts_enrolment_title', 'local_zilink'));
echo $OUTPUT->box(get_string('cohorts_enrolment_title_desc', 'local_zilink'));

$table              = new html_table();
$table->head        = array(get_string('cohort','cohort'),get_string('course'));
$table->align       = array('left','left');
$table->tablealign  = 'center';
$table->width       = '80%';

$cohorts = $DB->get_records('cohort', array('component' => 'enrol_zilink'),'name ASC');


foreach ($cohorts as $cohort)
{
    $tmp = '';
    $enrolments = $DB->get_records('enrol', array('customint1' => $cohort->id,'enrol' => 'zilink_cohort'));
    if(!empty($enrolments))
    {
        foreach ($enrolments as $enrolment)
        {
            
            $course = $DB->get_record('course', array('id' => $enrolment->courseid));
            if (is_object($course)) { 
                $tmp .= (empty($tmp)) ? html_writer::link(new moodle_url($CFG->wwwroot.'/course/view.php',array('id' => $course->id)),$course->shortname) : ', '.html_writer::link(new moodle_url($CFG->wwwroot.'/course/view.php',array('id' => $course->id)),$course->shortname);
            }
        }
        $table->data[] = array($cohort->name,$tmp);
    }
}

echo html_writer::table($table,true);

echo $OUTPUT->footer();