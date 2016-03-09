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
require_once(dirname(__FILE__) .'/forms/delete.php');

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey());
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/cohorts/admin/delete.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($CFG->httpswwwroot.'/local/zilink/plugins/cohorts/admin/delete.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('cohorts_page_title', 'local_zilink');

admin_externalpage_setup('zilink_cohorts_settings_delete',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/cohorts/admin/delete.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->set_pagelayout('report');

$PAGE->requires->css('/local/zilink/plugins/cohorts/styles.css');

$list = array();

$cohorts = $DB->get_records('cohort',array('component' => 'enrol_zilink'));

foreach($cohorts as $cohort)
{
    if($DB->count_records('cohort_members', array('cohortid'=>$cohort->id)) == 0 ) {
        $list[] = $cohort;
    }    
}

$form = new zilink_cohorts_delete_form(null, array('cohorts'=> $list));

if($data = $form->get_data()) {
    
    foreach( $data->cohort_delete as $cohortid => $value)
    {
        
        if($value == '1') {
            
            if ($instances = $DB->get_records('enrol', array('enrol' => 'zilink_cohort', 'customint1' => $cohortid))) {
                
                $plugin = enrol_get_plugin('zilink_cohort');
                
                foreach ($instances as $instance) {
                        $plugin->delete_instance($instance);
                }
            }
        
            $DB->delete_records('cohort',array('id' => $cohortid));
        }
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('cohorts_delete_title', 'local_zilink'));
echo $OUTPUT->box(get_string('cohorts_support_desc', 'local_zilink').html_writer::link('http://support.zilink.co.uk/hc/',get_string('support_site','local_zilink'),array('target'=> '_blank')));
echo $form->display();
echo $OUTPUT->footer();