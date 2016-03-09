<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class zilink_tools_effective_date_form extends moodleform {
    
    function definition() {

        global $CFG,$DB;
 
        $mform =& $this->_form;
        
        $mform->addElement('header', 'moodle', get_string('settings'));
        
        $availablefromgroup=array();
        $availablefromgroup[] =& $mform->createElement('date_selector', 'tools_effective_date', '');
        $availablefromgroup[] =& $mform->createElement('checkbox', 'tools_effective_date_enabled', '', get_string('enable'));
        $mform->addGroup($availablefromgroup, 'availablefromgroup', get_string('tools_effective_date','local_zilink'), ' ', false);
        $mform->disabledIf('availablefromgroup', 'tools_effective_date_enabled');
       
        $this->add_action_buttons(false, get_string('savechanges'));
    }
    
    function Display()
    {
        return $this->_form->toHtml();
    }
    
}