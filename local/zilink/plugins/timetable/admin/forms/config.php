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


class zilink_timetable_settings_form extends moodleform {

    public function definition() {
        global $CFG, $DB;  

        if (isset($this->_customdata['timetable_week_format'])) {
            $timetable_week_format = $this->_customdata['timetable_week_format'];
        } else {
            $timetable_week_format = '0';
        }

        if (isset($this->_customdata['timetable_offset'])) {
            $timetable_offset = $this->_customdata['timetable_offset'];
        } else {
            $timetable_offset = '0';
        }
        
        if (isset($this->_customdata['timetable_start_day'])) {
            $timetable_start_day = $this->_customdata['timetable_start_day'];
        } else {
            $timetable_start_day = 'monday';
        }  
        
        if (isset($this->_customdata['timetable_period_time_offset'])) {
            $timetable_period_time_offset = $this->_customdata['timetable_period_time_offset'];
        } else {
            $timetable_period_time_offset = '0';
        }  
        
        if (isset($this->_customdata['timetable_display_period_time'])) {
            $timetable_display_period_time = $this->_customdata['timetable_display_period_time'];
        } else {
            $timetable_display_period_time = '1';
        } 
        
        if (isset($this->_customdata['timetable_first_week'])) {
            $timetable_first_week = $this->_customdata['timetable_first_week'];
        } else {
            $timetable_first_week = '1';
        } 

        if (isset($this->_customdata['timetable_room_label'])) {
            $timetable_room_label = $this->_customdata['timetable_room_label'];
        } else {
            $timetable_room_label = 'code';
        } 

        $options = array();
        $options[0] = get_string('disable');
        $options[1] = get_string('enable');   

        $strrequired = get_string('required');
        $mform = & $this->_form;

        $mform->addElement('header', 'moodle', get_string('timetable', 'local_zilink').' ' .get_string('config', 'local_zilink'));
        $data = new ZiLinkData();        
    
        try {
                if($data->GetGlobalData('timetable',true)->Attribute('weeks') > 1)
                {
                    $list = array();
                    $list[1] = get_string('timetable_week_a_b','local_zilink');
                    $list[2] = get_string('timetable_week_1_2','local_zilink');
                    
                    $select = $mform->addElement('select', 'timetable_week_format', get_string('timetable_week_format', 'local_zilink'), $list);
                    $select->setSelected($timetable_week_format);
                    $mform->addHelpButton('timetable_week_format', 'timetable_week_format', 'local_zilink');
                    
                    
                    $list = array();
                    $list[1] = '1';
                    $list[2] = '2';
                    
                    $select = $mform->addElement('select', 'timetable_first_week', get_string('timetable_first_week', 'local_zilink'), $list);
                    $select->setSelected($timetable_first_week);
                    $mform->addHelpButton('timetable_first_week', 'timetable_first_week', 'local_zilink');
                }
                else
                {
                    $mform->addElement('hidden', 'timetable_week_format', 0);
                    $mform->setType('timetable_week_format',PARAM_INT);
                    
                    $mform->addElement('hidden', 'timetable_first_week', 1);
                    $mform->setType('timetable_first_week',PARAM_INT);
                }
            } catch (Exception $e)
            {
                $mform->addElement('hidden', 'timetable_week_format', 0);
                $mform->setType('timetable_week_format',PARAM_INT);
                
                $mform->addElement('hidden', 'timetable_first_week', 1);
                $mform->setType('timetable_first_week',PARAM_INT);
            }      
             
        try{   
             $ttcount = (count($data->GetGlobalData('timetable-all',true))-1);
                
             if( $ttcount >= 1)
             {
                $list = array();
        
                for ($i = 0; $i <= $ttcount;$i++)
                    {
                            $list[$i] = $i;
                    }
                
                    $select = $mform->addElement('select', 'timetable_offset', get_string('timetable_offset', 'local_zilink'), $list);
                    $select->setSelected($timetable_offset);
                    $mform->addHelpButton('timetable_offset', 'timetable_offset', 'local_zilink');
             }
             else
             {
                $mform->addElement('hidden', 'timetable_offset', 0);
                $mform->setType('timetable_offset',PARAM_INT);
             }
         } catch (Exception $e)
         {
             $mform->addElement('hidden', 'timetable_offset', 0);
             $mform->setType('timetable_offset',PARAM_INT);
         }
        
        $list = array();
        $list['Monday'] = 'Monday'; 
        $list['Sunday'] = 'Sunday';
        
        $select = $mform->addElement('select', 'timetable_start_day', get_string('timetable_start_day', 'local_zilink'), $list);
        $select->setSelected($timetable_start_day);
        $mform->addHelpButton('timetable_start_day', 'timetable_start_day', 'local_zilink');
        
        $list = array();
        $list['1'] = 'Show';
        $list['0'] = 'Hide';
        
        $select = $mform->addElement('select', 'timetable_display_period_time', get_string('timetable_display_period_time', 'local_zilink'), $list);
        $select->setSelected($timetable_display_period_time);
        $mform->addHelpButton('timetable_display_period_time', 'timetable_display_period_time', 'local_zilink');
        
        $list = array();
        $list['0'] = 'None';
        $list['1'] = '+1 Hour';
        
        $select = $mform->addElement('select', 'timetable_period_time_offset', get_string('timetable_period_time_offset', 'local_zilink'), $list);
        $select->setSelected($timetable_period_time_offset);
        $mform->disabledIf('timetable_period_time_offset', 'timetable_display_period_time', 'eq', 0);
        $mform->addHelpButton('timetable_period_time_offset', 'timetable_period_time_offset', 'local_zilink');      

        $list = array();
        $list['code'] = 'Code';
        $list['description'] = 'Description';
        
        $select = $mform->addElement('select', 'timetable_room_label', get_string('timetable_room_label', 'local_zilink'), $list);
        $select->setSelected($timetable_room_label);
        
        
        $this->add_action_buttons(false, get_string('savechanges'));
 
    }

    function display()
    {
        return $this->_form->toHtml();
    }

}

