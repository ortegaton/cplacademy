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


class zilink_import_log_form extends moodleform {

    public function definition() {
        global $CFG, $DB;  
        
        
        $mform = & $this->_form;
        
        $mform->addElement('header', 'moodle', get_string('import_log','local_zilink'));
        $mform->addElement('html', '<div id="consolelog">');
        
        if(!empty($this->_customdata['response']) && $this->_customdata['error'] == false) 
        {
            
            $table              = new html_table();
            $table->cellpadding = '10px';    
            $table->width       = '68%';
            $table->head        = array('Date','Level','Message');
            $table->align       = array('left', 'left','left');
            $table->border      = '2px'; 
            $table->tablealign  = 'center';
            
            $table->attributes['class'] = 'generaltable boxaligncenter zilink_table_width';
            
            $items = simplexml_load_string(preg_replace('/(<\?xml[^?]+?)utf-16/i', '$1utf-8',$this->_customdata['response']));
            
            $calls = array();
            foreach($items as $item)
            {
                
                $cells[] = userdate(strtotime((string)$item['DateTime']));
                
                switch ((string)$item['Level'])
                {
                    case "40000": $cells[] = "Info"; break;
                    case "50000": $cells[] = "Notice";break;
                    case "50001": $cells[] = "Import Successful";break;
                    case "90000": $cells[] = "Error";break;
                    case "90001": $cells[] = "Import Failed";break;
                }
                $cells[] = (string)$item->Message;
            }
            
            $table->data = array_chunk($cells, 3);
            
            $mform->addElement('html',html_writer::table($table));
            $mform->addElement('html','<p><i>The log will update every 30 seconds<i></p>');    
            
        } 
        else 
        {
            $mform->addElement('html','<p>'.$this->_customdata['response'].'</p>');                
        }
        $mform->addElement('html', '</div>');
    }

    function display()
    {
        return $this->_form->toHtml();
    }

}
