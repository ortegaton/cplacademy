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
require_once(dirname(__FILE__) .'/forms/general.php');
require_once($CFG->dirroot .'/local/zilink/lib.php');


$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey());
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/student/view/interfaces/deafult/admin/general.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($CFG->httpswwwroot.'/local/zilink/plugins/student/view/interfaces/deafult/admin/general.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('student_view_page_title', 'local_zilink');

admin_externalpage_setup('zilink_student_view_default_general_settings',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/student/view/interfaces/deafult/admin/general.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->set_pagelayout('report');

$form = new zilink_student_view_general_settings_form();
                                                                     
$toform = new stdClass();

$toform->notification_message = $CFG->zilink_student_view_default_notification;

$subjects = zilinkdeserialise($CFG->zilink_student_view_default_subjects_allowed);
if(!empty($subjects))
{
    foreach($subjects as $subject => $value)
    {
       $toform->{'student_view_default_allowed_subjects_'.$subject} = $value;
    }
}

$pages = zilinkdeserialise($CFG->zilink_student_view_default_display_notification);
if(!empty($pages))
{
    foreach($pages as $page => $value)
    {
       $toform->{'student_view_default_display_notification_'.$page} = $value;
    }
}

$form->set_data($toform);

$fromform = $form->get_data();

if (!empty($fromform) and confirm_sesskey()) {
    
    $subjects = array();
    $pages = array();
    foreach($fromform as $name => $value)
    {
        if(!strstr($name,'student_view_default_allowed_subjects_') === false)
        {
            $subjects[str_replace('student_view_default_allowed_subjects_','',$name)] = $value;
        }
                     
        if(!strstr($name,'student_view_default_display_notification_') === false)
        {
                             
            $pages[str_replace('student_view_default_display_notification_','',$name)] = $value;
        }
    }
     
    $CFG->zilink_student_view_default_subjects_allowed = json_encode($subjects);
    set_config('zilink_student_view_default_subjects_allowed',json_encode($subjects));

    $CFG->zilink_student_view_default_display_notification = json_encode($pages);
    set_config('zilink_student_view_default_display_notification',json_encode($pages));
    
    $CFG->zilink_student_view_default_notification = $fromform->notification_message;
    set_config('zilink_student_view_default_notification',$fromform->notification_message);
    
    
} else {
    
}

//OUTPUT
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('student_view_general_title', 'local_zilink'));
echo $OUTPUT->box(get_string('student_view_general_title_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('student_view_support_desc', 'local_zilink').html_writer::link('http://support.schoolsict.net/hc',get_string('support_site','local_zilink'),array('target'=> '_blank')));
echo $form->display();
echo $OUTPUT->footer();

