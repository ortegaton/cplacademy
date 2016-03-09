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

require_once(dirname(__FILE__) . '/../../../../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(__FILE__) .'/forms/assessment_overview.php');

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey());
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/class/view/interfaces/default/admin/assessment_overview.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($CFG->httpswwwroot.'/local/zilink/plugins/class/view/interfaces/default/admin/assessment_overview.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('class_view_page_title', 'local_zilink');

admin_externalpage_setup('zilink_class_view_default_assessment_overview_settings',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/class/view/interfaces/default/admin/assessment_overview.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->set_pagelayout('report');

$form = new zilink_class_view_assessment_overview_settings_form(null, array('class_view_default_assessment_overview_above_trigger' => $CFG->zilink_class_view_default_assessment_overview_above_trigger,
                                                        'class_view_default_assessment_overview_level_trigger' => $CFG->zilink_class_view_default_assessment_overview_level_trigger,
                                                        'class_view_default_assessment_overview_belowl_trigger' => $CFG->zilink_class_view_default_assessment_overview_below_trigger,
                                                        ));

$fromform = $form->get_data();

if (!empty($fromform) and confirm_sesskey()) {

    if(isset($fromform->class_view_default_assessment_overview_above_trigger))
    {
        $CFG->zilink_class_view_default_assessment_overview_above_trigger = $fromform->class_view_default_assessment_overview_above_trigger;
        set_config('zilink_class_view_default_assessment_overview_above_trigger',$fromform->class_view_default_assessment_overview_above_trigger);
    }
    
    if(isset($fromform->class_view_default_assessment_overview_level_trigger))
    {
        $CFG->zilink_class_view_default_assessment_overview_level_trigger = $fromform->class_view_default_assessment_overview_level_trigger;
        set_config('zilink_class_view_default_assessment_overview_level_trigger',$fromform->class_view_default_assessment_overview_level_trigger);
    }
    
    if(isset($fromform->class_view_default_assessment_overview_below_trigger))
    {
        $CFG->zilink_class_view_default_assessment_overview_below_trigger = $fromform->class_view_default_assessment_overview_below_trigger;
        set_config('zilink_class_view_default_assessment_overview_below_trigger',$fromform->class_view_default_assessment_overview_below_trigger);
    }
    
    if(isset($fromform->class_view_default_assessment_overview_general_comment))
    {
        $CFG->zilink_class_view_default_assessment_overview_general_comment = $fromform->class_view_default_assessment_overview_general_comment;
        set_config('zilink_class_view_default_assessment_overview_general_comment',$fromform->class_view_default_assessment_overview_general_comment);
    }
    
    if(isset($fromform->class_view_default_assessment_overview_above_comment))
    {
        $CFG->zilink_class_view_default_assessment_overview_above_comment = $fromform->class_view_default_assessment_overview_above_comment;
        set_config('zilink_class_view_default_assessment_overview_above_comment',$fromform->class_view_default_assessment_overview_above_comment);
    }
    
    if(isset($fromform->class_view_default_assessment_overview_level_comment))
    {
        $CFG->zilink_class_view_default_assessment_overview_level_comment = $fromform->class_view_default_assessment_overview_level_comment;
        set_config('zilink_class_view_default_assessment_overview_level_comment',$fromform->class_view_default_assessment_overview_level_comment);
    }
    
    if(isset($fromform->class_view_default_assessment_overview_below_comment))
    {
        $CFG->zilink_class_view_default_assessment_overview_below_comment = $fromform->class_view_default_assessment_overview_below_comment;
        set_config('zilink_class_view_default_assessment_overview_below_comment',$fromform->class_view_default_assessment_overview_below_comment);
    }
}
else {
    $toform = new stdClass();
    $toform->class_view_default_assessment_overview_general_comment = $CFG->zilink_class_view_default_assessment_overview_general_comment;
    $toform->class_view_default_assessment_overview_above_comment = $CFG->zilink_class_view_default_assessment_overview_above_comment;
    $toform->class_view_default_assessment_overview_level_comment = $CFG->zilink_class_view_default_assessment_overview_level_comment;
    $toform->class_view_default_assessment_overview_below_comment = $CFG->zilink_class_view_default_assessment_overview_below_comment;
    
    $form->set_data($toform);
}

//OUTPUT
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('class_view_assessment_overview_title', 'local_zilink'));
echo $OUTPUT->box(get_string('class_view_assessment_overview_title_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('class_view_support_desc', 'local_zilink').html_writer::link('https://schoolsict.zendesk.com/entries/30205076',get_string('support_site','local_zilink'),array('target'=> '_blank')));
echo $form->display();
echo $OUTPUT->footer();

