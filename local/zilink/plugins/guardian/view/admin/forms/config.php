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


class zilink_guardian_view_config_settings_form extends moodleform {

    public function definition() {
        global $CFG, $DB;  
        
        if (isset($this->_customdata['interface'])) {
            $interface = $this->_customdata['interface'];
        } else {
            $interface = '';
        }
        
        $mform = & $this->_form;
        
        $mform->addElement('header', 'moodle', get_string('settings'));
        
        $path = $CFG->dirroot.'/local/zilink/plugins/guardian/view/interfaces';
        $directories = array();
        $ignore = array( '.', '..','db','lang');
        $dh = @opendir( $path );
        
        while( false !== ( $file = readdir( $dh ) ) )
        {
            if( !in_array( $file, $ignore ) )
            {
                if(is_dir( "$path/$file" ) )
                {
                    $directories[$file] = ucwords($file);
                }
            }
        }
        
        closedir( $dh );
                
        $select = $mform->addElement('select', 'interface', get_string("guardian_view_interface", "local_zilink"), $directories);
        $select->setSelected($interface);
        $this->add_action_buttons(false, get_string('savechanges'));
    }

    function display()
    {
        return $this->_form->toHtml();
    }

}
