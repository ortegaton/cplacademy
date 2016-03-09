<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class zilink_tools_permissions_override_form extends moodleform {
    
    function definition() {

        global $CFG,$DB;
        
        if (isset($this->_customdata['tools_permissions_override'])) {
            $tools_permissions_override = $this->_customdata['tools_permissions_override'];
        } else {
            $tools_permissions_override = '0';
        }
 
        $mform =& $this->_form;
        
        $mform->addElement('header', 'moodle', get_string('settings'));
        
        $options = array();
        $options['0'] = 'Disabled';
        $options['1'] = 'Enabled';
        
        $select = &$mform->addElement('select', 'tools_permissions_override', get_string('tools_permissions_override_desc', 'local_zilink'), $options);
        $mform->addHelpButton('tools_permissions_override', 'tools_permissions_override_desc', 'local_zilink');
        $select->setSelected($tools_permissions_override);
        
        $this->add_action_buttons(false, get_string('savechanges'));

    }
    
    function Display()
    {
        return $this->_form->toHtml();
    }
    
}