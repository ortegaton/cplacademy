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


class zilink_class_view_general_settings_form extends moodleform {

    public function definition() {
        global $CFG, $DB;  
        
        $mform = & $this->_form;
        
        $mform->addElement('header', 'moodle', get_string('settings'));
        
        
        if(isset($CFG->zilink_category_root))
        {
            $categories = array();
            $mdl_categories =  $DB->get_records('course_categories',array('parent' => $CFG->zilink_category_root));
            if($mdl_categories)
            {
                foreach($mdl_categories as $mdl_category)
                {
                    if(!in_array($mdl_category->name,$categories) && $mdl_category->visible == 1)
                        $categories[$mdl_category->id] = $mdl_category->name;
                }
            }
            asort($categories);
            
            $mform->addElement('static', 'class_view_allowed_subjects', '<b>'.get_string('class_view_general_allowed_subjects', 'local_zilink').'</b>');
            
            $mform->addElement('html', '<table class="generaltable boxaligncenter" width="80%"><tbody><tr>');
            
            $count=0;
            foreach($categories as $id => $name)
            {
                if(!($count % 4)) {
                    $mform->addElement('html','</tr><tr>');
                }
                
                $mform->addElement('html','<td class="cell c1 " style="max-width:auto;">');
                $mform->addElement('advcheckbox', 'class_view_default_allowed_subjects_'.$id, null,$name, array('group' => 1));
                $mform->addElement('html', '</td>');
                $count++;
                
            }
            $mform->addElement('html', '</tr>
                                </tbody>    
                                </table>'); 
        }             

        $this->add_action_buttons(false, get_string('savechanges'));
    }

    function display()
    {
        return $this->_form->toHtml();
    }

}
