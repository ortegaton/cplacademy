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
require_once(dirname(__FILE__) .'/forms/attendance.php');

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey());
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/deafult/admin/attendance.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($CFG->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/deafult/admin/attendance.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('guardian_view_page_title', 'local_zilink');

admin_externalpage_setup('zilink_guardian_view_default_attendance_settings',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/deafult/admin/attednance.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->set_pagelayout('report');

$form = new zilink_guardian_view_attendance_settings_form(null, array('guardian_view_default_attendance_overview_delay' => $CFG->zilink_guardian_view_default_attendance_overview_delay,
                                                                      'guardian_view_default_attendance_overview_present_below_trigger' => $CFG->zilink_guardian_view_default_attendance_overview_present_below_trigger,
                                                                      'guardian_view_default_attendance_overview_present_above_trigger' => $CFG->zilink_guardian_view_default_attendance_overview_present_above_trigger,
                                                                      'guardian_view_default_attendance_overview_late_below_trigger' => $CFG->zilink_guardian_view_default_attendance_overview_late_below_trigger,
                                                                      'guardian_view_default_attendance_overview_late_above_trigger' => $CFG->zilink_guardian_view_default_attendance_overview_late_above_trigger,
                                                                      'guardian_view_default_attendance_overview_authorised_absence_below_trigger' => $CFG->zilink_guardian_view_default_attendance_overview_authorised_absence_below_trigger,
                                                                      'guardian_view_default_attendance_overview_authorised_absence_above_trigger' => $CFG->zilink_guardian_view_default_attendance_overview_authorised_absence_above_trigger,
                                                                      'guardian_view_default_attendance_overview_unauthorised_absence_below_trigger' => $CFG->zilink_guardian_view_default_attendance_overview_unauthorised_absence_below_trigger,
                                                                      'guardian_view_default_attendance_overview_unauthorised_absence_above_trigger' => $CFG->zilink_guardian_view_default_attendance_overview_unauthorised_absence_above_trigger,
                                                                        ));

$fromform = $form->get_data();

if (!empty($fromform) and confirm_sesskey()) {

    if(isset($fromform->guardian_view_default_attendance_overview_delay))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_delay = $fromform->guardian_view_default_attendance_overview_delay;
        set_config('zilink_guardian_view_default_attendance_overview_delay',$fromform->guardian_view_default_attendance_overview_delay);
    }
    
    if(isset($fromform->guardian_view_default_attendance_overview_present_above_trigger))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_present_above_trigger = $fromform->guardian_view_default_attendance_overview_present_above_trigger;
        set_config('zilink_guardian_view_default_attendance_overview_present_above_trigger',$fromform->guardian_view_default_attendance_overview_present_above_trigger);
    }
    
    if(isset($fromform->guardian_view_default_attendance_overview_present_below_trigger))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_present_below_trigger = $fromform->guardian_view_default_attendance_overview_present_below_trigger;
        set_config('zilink_guardian_view_default_attendance_overview_present_below_trigger',$fromform->guardian_view_default_attendance_overview_present_below_trigger);
    }
    
    if(isset($fromform->guardian_view_default_attendance_overview_late_above_trigger))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_late_above_trigger = $fromform->guardian_view_default_attendance_overview_late_above_trigger;
        set_config('zilink_guardian_view_default_attendance_overview_late_above_trigger',$fromform->guardian_view_default_attendance_overview_late_above_trigger);
    }
    
    if(isset($fromform->guardian_view_default_attendance_overview_late_below_trigger))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_late_below_trigger = $fromform->guardian_view_default_attendance_overview_late_below_trigger;
        set_config('zilink_guardian_view_default_attendance_overview_late_below_trigger',$fromform->guardian_view_default_attendance_overview_late_below_trigger);
    }

    if(isset($fromform->guardian_view_default_attendance_overview_authorised_absence_above_trigger))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_authorised_absence_above_trigger = $fromform->guardian_view_default_attendance_overview_authorised_absence_above_trigger;
        set_config('zilink_guardian_view_default_attendance_overview_authorised_absence_above_trigger',$fromform->guardian_view_default_attendance_overview_authorised_absence_above_trigger);
    }
    
    if(isset($fromform->guardian_view_default_attendance_overview_authorised_absence_below_trigger))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_authorised_absence_below_trigger = $fromform->guardian_view_default_attendance_overview_authorised_absence_below_trigger;
        set_config('zilink_guardian_view_default_attendance_overview_authorised_absence_below_trigger',$fromform->guardian_view_default_attendance_overview_authorised_absence_below_trigger);
    }

    if(isset($fromform->guardian_view_default_attendance_overview_unauthorised_absence_above_trigger))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_unauthorised_absence_above_trigger = $fromform->guardian_view_default_attendance_overview_unauthorised_absence_above_trigger;
        set_config('zilink_guardian_view_default_attendance_overview_unauthorised_absence_above_trigger',$fromform->guardian_view_default_attendance_overview_unauthorised_absence_above_trigger);
    }
    
    if(isset($fromform->guardian_view_default_attendance_overview_unauthorised_absence_below_trigger))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_unauthorised_absence_below_trigger = $fromform->guardian_view_default_attendance_overview_unauthorised_absence_below_trigger;
        set_config('zilink_guardian_view_default_attendance_overview_unauthorised_absence_below_trigger',$fromform->guardian_view_default_attendance_overview_unauthorised_absence_below_trigger);
    }
    
    ////////
    
    if(isset($fromform->guardian_view_default_attendance_overview_present_above_comment))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_present_above_comment = $fromform->guardian_view_default_attendance_overview_present_above_comment;
        set_config('zilink_guardian_view_default_attendance_overview_present_above_comment',$fromform->guardian_view_default_attendance_overview_present_above_comment);
    }
    
    if(isset($fromform->guardian_view_default_attendance_overview_present_below_comment))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_present_below_comment = $fromform->guardian_view_default_attendance_overview_present_below_comment;
        set_config('zilink_guardian_view_default_attendance_overview_present_below_comment',$fromform->guardian_view_default_attendance_overview_present_below_comment);
    }
    
    if(isset($fromform->guardian_view_default_attendance_overview_late_above_comment))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_late_above_comment = $fromform->guardian_view_default_attendance_overview_late_above_comment;
        set_config('zilink_guardian_view_default_attendance_overview_late_above_comment',$fromform->guardian_view_default_attendance_overview_late_above_comment);
    }
    
    if(isset($fromform->guardian_view_default_attendance_overview_late_below_comment))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_late_below_comment = $fromform->guardian_view_default_attendance_overview_late_below_comment;
        set_config('zilink_guardian_view_default_attendance_overview_late_below_comment',$fromform->guardian_view_default_attendance_overview_late_below_comment);
    }

    if(isset($fromform->guardian_view_default_attendance_overview_authorised_absence_above_comment))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_authorised_absence_above_comment = $fromform->guardian_view_default_attendance_overview_authorised_absence_above_comment;
        set_config('zilink_guardian_view_default_attendance_overview_authorised_absence_above_comment',$fromform->guardian_view_default_attendance_overview_authorised_absence_above_comment);
    }
    
    if(isset($fromform->guardian_view_default_attendance_overview_authorised_absence_below_comment))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_authorised_absence_below_comment = $fromform->guardian_view_default_attendance_overview_authorised_absence_below_comment;
        set_config('zilink_guardian_view_default_attendance_overview_authorised_absence_below_comment',$fromform->guardian_view_default_attendance_overview_authorised_absence_below_comment);
    }

    if(isset($fromform->guardian_view_default_attendance_overview_unauthorised_absence_above_comment))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_unauthorised_absence_above_comment = $fromform->guardian_view_default_attendance_overview_unauthorised_absence_above_comment;
        set_config('zilink_guardian_view_default_attendance_overview_unauthorised_absence_above_comment',$fromform->guardian_view_default_attendance_overview_unauthorised_absence_above_comment);
    }
    
    if(isset($fromform->guardian_view_default_attendance_overview_unauthorised_absence_below_comment))
    {
        $CFG->zilink_guardian_view_default_attendance_overview_unauthorised_absence_below_comment = $fromform->guardian_view_default_attendance_overview_unauthorised_absence_below_comment;
        set_config('zilink_guardian_view_default_attendance_overview_unauthorised_absence_below_comment',$fromform->guardian_view_default_attendance_overview_unauthorised_absence_below_comment);
    }
}
else {
    
    $toform = new stdClass();
    $toform->guardian_view_default_attendance_overview_present_below_comment                = $CFG->zilink_guardian_view_default_attendance_overview_present_below_comment;
    $toform->guardian_view_default_attendance_overview_present_above_comment                = $CFG->zilink_guardian_view_default_attendance_overview_present_above_comment;
    $toform->guardian_view_default_attendance_overview_late_below_comment                   = $CFG->zilink_guardian_view_default_attendance_overview_late_below_comment;
    $toform->guardian_view_default_attendance_overview_late_above_comment                   = $CFG->zilink_guardian_view_default_attendance_overview_late_above_comment;
    $toform->guardian_view_default_attendance_overview_authorised_absence_below_comment     = $CFG->zilink_guardian_view_default_attendance_overview_authorised_absence_below_comment;
    $toform->guardian_view_default_attendance_overview_authorised_absence_above_comment     = $CFG->zilink_guardian_view_default_attendance_overview_authorised_absence_above_comment;
    $toform->guardian_view_default_attendance_overview_unauthorised_absence_below_comment   = $CFG->zilink_guardian_view_default_attendance_overview_unauthorised_absence_below_comment;
    $toform->guardian_view_default_attendance_overview_unauthorised_absence_above_comment   = $CFG->zilink_guardian_view_default_attendance_overview_unauthorised_absence_above_comment;
            
    $form->set_data($toform);                                                          
}

//OUTPUT
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('guardian_view_default_attendance_title', 'local_zilink'));
//echo $OUTPUT->box(get_string('guardian_view_default_attendance_title_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('guardian_view_support_desc', 'local_zilink').html_writer::link('http://support.zilink.co.uk/hc/',get_string('support_site','local_zilink'),array('target'=> '_blank')));
echo $form->display();
echo $OUTPUT->footer();

