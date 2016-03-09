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
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/cohorts/admin/view.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($CFG->httpswwwroot.'/local/zilink/plugins/cohorts/admin/view.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('cohorts_page_title', 'local_zilink');

admin_externalpage_setup('zilink_cohorts_settings_view',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/cohorts/admin/view.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->set_pagelayout('report');

$PAGE->requires->css('/local/zilink/plugins/cohorts/styles.css');

$jsdata = array(sesskey(),$CFG->httpswwwroot);

$jsmodule = array(
                        'name'  =>  'local_zilink_cohort_view',
                        'fullpath'  =>  '/local/zilink/plugins/cohorts/module.js',
                        'requires'  =>  array('base', 'node', 'io')
                    );

$PAGE->requires->js_init_call('M.local_zilink_cohorts_view.init', $jsdata, false, $jsmodule);

//$cohorts = $DB->get_records_sql("select c.id as id, c.name as name, count(cm.cohortid) as count from  mdl_cohort c inner join mdl_cohort_members cm on c.id=cm.cohortid group by name",array(null));
$cohorts = $DB->get_records_sql("select id, name from  {cohort} where component = 'enrol_zilink' ORDER BY NAME",array(null));

$list = array();

foreach($cohorts as $cohort)
{
    $list[$cohort->id] = $cohort->name;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('cohorts_view_title', 'local_zilink'));
echo $OUTPUT->box(get_string('cohorts_view_title_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('cohorts_support_desc', 'local_zilink').html_writer::link('https://schoolsict.zendesk.com/entries/65562623',get_string('support_site','local_zilink'),array('target'=> '_blank')));

echo $OUTPUT->box('<b>ZiLink Cohorts</b>','generalbox zilink_box_left');
echo $OUTPUT->box('<b>Cohort Members</b>','generalbox zilink_box_right');

echo $OUTPUT->box(html_writer::tag('b',get_string('search'), array('style' => 'width: 15%; height: 19.01px; float: left;')).html_writer::tag('input','',array('id' => 'zilink_cohorts_view_serach_term', 'name' => 'zilink_cohorts_view_serach_term', 'size' => '22','style' => 'width: 84%; height: 19.01px; float: right')),'generalbox zilink_box_left');
echo $OUTPUT->box(html_writer::select(array(),'zilink_cohorts_view_student_list','','',array('size' => '28','style' => 'width: 100%;')),'generalbox zilink_box_right');
echo $OUTPUT->box(html_writer::select($list,'zilink_cohorts_view_cohort_list','','',array('size' => '24','style' => 'width: 100%;')),'generalbox zilink_box_left');

echo $OUTPUT->footer();