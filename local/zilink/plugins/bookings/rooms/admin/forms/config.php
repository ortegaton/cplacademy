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

    defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/zilink/plugins/core/data.php');


class zilink_bookings_room_settings_form extends moodleform {

    public function definition() {
        global $CFG, $DB;  

        
        if (isset($this->_customdata['bookings_rooms_system'])) {
            $bookings_rooms_system = $this->_customdata['bookings_rooms_system'];
        } else {
            $bookings_rooms_system = 'internal';
        }
        
        if (isset($this->_customdata['bookings_rooms_weeks_in_advance'])) {
            $bookings_rooms_weeks_in_advance = $this->_customdata['bookings_rooms_weeks_in_advance'];
        } else {
            $bookings_rooms_weeks_in_advance = '1';
        }
        
        if (isset($this->_customdata['bookings_rooms_email_notifications'])) {
            $bookings_rooms_email_notifications = $this->_customdata['bookings_rooms_email_notifications'];
        } else {
            $bookings_rooms_email_notifications = 0;
        } 
        
        $mform = & $this->_form;

        $mform->addElement('header', 'moodle', get_string('bookings_rooms', 'local_zilink').' ' .get_string('config', 'local_zilink'));
        
        $systems = array('internal' => 'ZiLink','schoolbooking' => 'SchoolBooking','custom' => 'External');
        
        $select = $mform->addElement('select', 'bookings_rooms_system', get_string('bookings_rooms_system', 'local_zilink'), $systems);
        $select->setSelected($bookings_rooms_system);
        $mform->addElement('static', 'description', '',get_string('bookings_rooms_system_desc', 'local_zilink'));
        
        if($bookings_rooms_system == 'internal') {
            
            $mform->addElement('header', 'moodle', get_string('zilink', 'local_zilink'));
            $list = array();
            
            for($i = 1; $i <16; $i++)
            {
                $list[$i] = $i;
            }
            
            $select = $mform->addElement('select', 'bookings_rooms_weeks_in_advance', get_string('bookings_rooms_weeks_in_advance', 'local_zilink'), $list);
            $select->setSelected($bookings_rooms_weeks_in_advance);
            $mform->addHelpButton('bookings_rooms_weeks_in_advance', 'bookings_rooms_weeks_in_advance', 'local_zilink');
            $mform->setType('bookings_rooms_weeks_in_advance',PARAM_INT);
            
            $options = array();
            $options[0] = get_string('disable');
            $options[1] = get_string('enable');   
            
            $select = $mform->addElement('select', 'bookings_rooms_email_notifications', get_string('bookings_rooms_email_notifications', 'local_zilink'), $options);
            $select->setSelected('bookings_rooms_email_notifications');
            
            $data = new ZiLinkData();
            $timetable = $data->GetGlobalData('timetable');
            
            if($timetable)
            {
                $timetablerooms = $timetable->timetable->xpath('//room');
                $rooms = array();
                $mform->addElement('static', 'bookable_rooms', get_string('bookings_rooms_bookable_rooms', 'local_zilink'));
                $mform->addHelpButton('bookable_rooms', 'bookings_rooms_bookable_rooms', 'local_zilink');
                foreach ($timetablerooms as $timetableroom) 
                {
                    if (!in_array($timetableroom->Attribute('code'), $rooms))
                    {
                        $rooms[trim($timetableroom->Attribute('code'))] = trim($timetableroom->Attribute('description')).' ( '.trim($timetableroom->Attribute('code')).' )';
                    }
                }
                ksort($rooms);
                
                $mform->addElement('html', '<table class="generaltable boxaligncenter" width="80%"><tbody><tr>');
                                
                $count = 0;
                foreach ($rooms as $code => $name)
                {
                    if(!($count % 4)) {
                        $mform->addElement('html','</tr><tr>');
                    }
                    
                    $mform->addElement('html','<td class="cell c1 " style="max-width:auto;">');
                    $mform->addElement('advcheckbox', 'bookings_rooms_allowed_rooms_'.$code, null, $name, array('group' => 1));
                    $mform->disabledIf('bookings_rooms_allowed_rooms_'.$code, 'bookings_rooms_system', 'neq','internal');
                    $mform->addElement('html', '</td>');
                    $count++;
                }
    
                $mform->addElement('html', '</tr>
                                    </tbody>    
                                    </table>'); 
            } 
    
            $mform->disabledIf('bookings_rooms_weeks_in_advance', 'bookings_rooms_system', 'neq','internal');
            $mform->disabledIf('bookings_rooms_email_notifications', 'bookings_rooms_system', 'neq','internal');
             
        } 
        else if($bookings_rooms_system == 'external') 
        {
            
            $mform->addElement('text', 'bookings_rooms_alternative_link', get_string('bookings_rooms_alternativelink', 'local_zilink'), array('size'=>'100'));
            $mform->setType('bookings_rooms_alternative_link',PARAM_URL);
            $mform->disabledIf('bookings_rooms_alternative_link', 'bookings_rooms_system', 'neq','custom');
            
        }
        else if($bookings_rooms_system == 'schoolbooking') 
        {
            $mform->addElement('text', 'bookings_rooms_schoolbooking_link', get_string('bookings_rooms_schoolbooking_siteid', 'local_zilink'), array('size'=>'100'));
            $mform->setType('bookings_rooms_schoolbooking_link',PARAM_TEXT);
            $mform->disabledIf('bookings_rooms_schoolbooking_link', 'bookings_rooms_system', 'neq','schoolbooking');
            $mform->addHelpButton('bookings_rooms_schoolbooking_link', 'bookings_rooms_schoolbooking_siteid', 'local_zilink');
        }
        
        $this->add_action_buttons(false, get_string('savechanges'));
    }

    function display()
    {
        return $this->_form->toHtml();
    }

}

