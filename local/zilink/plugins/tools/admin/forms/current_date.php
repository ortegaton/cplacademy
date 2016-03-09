<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class zilink_tools_current_date_form extends moodleform {
    
    function definition() {

        global $CFG,$DB;
 
        $mform =& $this->_form;
        
        $mform->addElement('header', 'moodle', get_string('settings'));
        
        
        $mform->addElement('static', 'current_date', get_string('tools_current_date_gmt', 'local_zilink'), date('d/m/Y H:i:s'));
        
        date_default_timezone_set('UTC');
        $current_date = date('d/m/Y H:i:s');
        
        $mform->addElement('static', 'current_date', get_string('tools_current_date_utc', 'local_zilink'), $current_date);
    
    }
    
    function Display()
    {
        return $this->_form->toHtml();
    }
    
}