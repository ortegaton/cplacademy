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

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/zilink/plugins/core/data.php');


class zilink_class_view_assessment_overview_settings_form extends moodleform {

    public function definition() {
        global $CFG, $DB;  

        if (isset($this->_customdata['class_view_default_assessment_overview_above_trigger'])) {
            $class_view_default_assessment_overview_above_trigger = $this->_customdata['class_view_default_assessment_overview_above_trigger'];
        } else {
            $class_view_default_assessment_overview_above_trigger = '-1';
        }
        
        if (isset($this->_customdata['class_view_default_assessment_overview_level_trigger'])) {
            $class_view_default_assessment_overview_level_trigger = $this->_customdata['class_view_default_assessment_overview_level_trigger'];
        } else {
            $class_view_default_assessment_overview_level_trigger = '-1';
        }
        
        if (isset($this->_customdata['class_view_default_assessment_overview_below_trigger'])) {
            $class_view_default_assessment_overview_below_trigger = $this->_customdata['class_view_default_assessment_overview_below_trigger'];
        } else {
            $class_view_default_assessment_overview_below_trigger = '-1';
        }

        
        $list = array(); 
        
        $mform = & $this->_form;
       
        $list = array();
                        
        $list['-1'] = get_string('disabled','local_zilink');
        $list['0'] = 0;                 
        for($i = 1; $i <15; $i++)
        {
            $list[$i] = $i;
        }
        
        $mform->addElement('header', 'moodle', get_string('general','local_zilink'));
        
        $mform->addElement('textarea', 'class_view_default_assessment_overview_general_comment', get_string("class_view_default_assessment_overview_general_comment", "local_zilink"), 'wrap="virtual" rows="5" cols="100"');
        $mform->addHelpButton('class_view_default_assessment_overview_general_comment', 'class_view_default_assessment_overview_general_comment', 'local_zilink');
        
        $mform->addElement('header', 'moodle', get_string('above','local_zilink'));
    
        $select = $mform->addElement('select', 'class_view_default_assessment_overview_above_trigger', get_string('class_view_default_assessment_overview_above_trigger', 'local_zilink'), $list);
        $mform->addHelpButton('class_view_default_assessment_overview_above_trigger', 'class_view_default_assessment_overview_above_trigger', 'local_zilink');
        $select->setSelected($class_view_default_assessment_overview_above_trigger);
        
        $mform->addElement('textarea', 'class_view_default_assessment_overview_above_comment', get_string("class_view_default_assessment_overview_above_comment", "local_zilink"), 'wrap="virtual" rows="5" cols="100"');
        $mform->addHelpButton('class_view_default_assessment_overview_above_comment', 'class_view_default_assessment_overview_above_comment', 'local_zilink');
        
        $mform->addElement('header', 'moodle', get_string('level','local_zilink'));
    
        $select = $mform->addElement('select', 'class_view_default_assessment_overview_level_trigger', get_string('class_view_default_assessment_overview_level_trigger', 'local_zilink'), $list);
        $mform->addHelpButton('class_view_default_assessment_overview_level_trigger', 'class_view_default_assessment_overview_level_trigger', 'local_zilink');
        $select->setSelected($class_view_default_assessment_overview_level_trigger);
        
        $mform->addElement('textarea', 'class_view_default_assessment_overview_level_comment', get_string("class_view_default_assessment_overview_level_comment", "local_zilink"), 'wrap="virtual" rows="5" cols="100"');
        $mform->addHelpButton('class_view_default_assessment_overview_level_comment', 'class_view_default_assessment_overview_level_comment', 'local_zilink');
        
        $mform->addElement('header', 'moodle', get_string('below','local_zilink'));
    
        $select = $mform->addElement('select', 'class_view_default_assessment_overview_below_trigger', get_string('class_view_default_assessment_overview_below_trigger', 'local_zilink'), $list);
        $mform->addHelpButton('class_view_default_assessment_overview_below_trigger','class_view_default_assessment_overview_below_trigger', 'local_zilink');
        $select->setSelected($class_view_default_assessment_overview_below_trigger);
    
        $mform->addElement('textarea', 'class_view_default_assessment_overview_below_comment', get_string("class_view_default_assessment_overview_below_comment", "local_zilink"), 'wrap="virtual" rows="5" cols="100"');
        $mform->addHelpButton('class_view_default_assessment_overview_below_comment', 'class_view_default_assessment_overview_below_comment', 'local_zilink');
        
             
        $this->add_action_buttons(false, get_string('savechanges'));
   }
}