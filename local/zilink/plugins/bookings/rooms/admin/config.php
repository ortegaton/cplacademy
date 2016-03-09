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
 * Defines the capabilities for the ZiLink block
 *
 * @package     local_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(__FILE__) .'/forms/config.php');
require_once($CFG->dirroot.'/local/zilink/lib.php');

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey());
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/bookings/rooms/admin/config.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($CFG->httpswwwroot.'/local/zilink/plugins/bookings/rooms/admin/config.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('bookings_rooms_page_title', 'local_zilink');

admin_externalpage_setup('zilink_bookings_rooms_settings_config',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/bookings/rooms/admin/config.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->set_pagelayout('report');
    
$form = new zilink_bookings_room_settings_form(null,array('bookings_rooms_weeks_in_advance' => $CFG->zilink_bookings_rooms_weeks_in_advance,
                                                          'bookings_rooms_system' => $CFG->zilink_bookings_rooms_system));

$toform = new stdClass();
$toform->bookings_rooms_alternative_link = $CFG->zilink_bookings_rooms_alternative_link;
$toform->bookings_rooms_schoolbooking_link = $CFG->zilink_bookings_rooms_schoolbooking_link;

$rooms = zilinkdeserialise($CFG->zilink_bookings_rooms_allowed_rooms);
if(!empty($rooms))
{
    foreach($rooms as $room => $value)
    {
       $toform->{'bookings_rooms_allowed_rooms_'.$room} = $value;
    }
}

$form->set_data($toform);

$fromform = $form->get_data();

if (!empty($fromform) and confirm_sesskey()) {
 
    if(isset($fromform->bookings_rooms_system))
    {
        $CFG->zilink_bookings_rooms_system = $fromform->bookings_rooms_system;
        set_config('zilink_bookings_rooms_system',$fromform->bookings_rooms_system);
    }
    
    if(isset($fromform->bookings_rooms_alternative_link))
    {
        $CFG->zilink_bookings_rooms_alternative_link = $fromform->bookings_rooms_alternative_link;
        set_config('zilink_bookings_rooms_alternative_link',$fromform->bookings_rooms_alternative_link);
    }
    
    if(isset($fromform->bookings_rooms_schoolbooking_link))
    {
        $CFG->zilink_bookings_rooms_schoolbooking_link = $fromform->bookings_rooms_schoolbooking_link;
        set_config('zilink_bookings_rooms_schoolbooking_link',$fromform->bookings_rooms_schoolbooking_link);
    }
    
    if(isset($fromform->bookings_rooms_weeks_in_advance))
    {
        $CFG->zilink_bookings_rooms_weeks_in_advance = $fromform->bookings_rooms_weeks_in_advance;
        set_config('zilink_bookings_rooms_weeks_in_advance',$fromform->bookings_rooms_weeks_in_advance);
    }
    
    if(isset($fromform->bookings_rooms_email_notifications))
    {
        $CFG->zilink_bookings_rooms_email_notifications = $fromform->bookings_rooms_email_notifications;
        set_config('zilink_bookings_rooms_email_notifications',$fromform->bookings_rooms_email_notifications);
    } 
    
    $rooms = array();
    foreach($fromform as $name => $value)
    {
        if(!strstr($name,'bookings_rooms_allowed_rooms_') === false)
        {
            $rooms[str_replace('bookings_rooms_allowed_rooms_','',$name)] = $value;
        }
    }
     
    $CFG->zilink_bookings_rooms_allowed_rooms = json_encode($rooms);
    set_config('zilink_bookings_rooms_allowed_rooms',json_encode($rooms));

    $form->set_data($fromform);  
}

//OUTPUT
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('bookings_rooms', 'local_zilink'));
echo $OUTPUT->box(get_string('bookings_rooms_title_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('bookings_support_desc', 'local_zilink').html_writer::link('http://support.zilink.co.uk/hc/',get_string('support_site','local_zilink'),array('target'=> '_blank')));
echo $form->display();
echo $OUTPUT->footer();

