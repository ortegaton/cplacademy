<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class zilink_tools_database_collation_form extends moodleform {
    
    function definition() {

        global $CFG,$DB;
 
        $mform =& $this->_form;
        
        $options = array();
        $options['utf8_unicode_ci'] = 'utf8_unicode_ci';
        $options['utf8_general_ci'] = 'utf8_general_ci';
        
        //$mform->addElement('html','<table class="generaltable tableleft boxaligncenter" width="100%" cellpadding="10px">
        //                            <tbody><tr><td class="cell c1" style="text-align:center; vertical-align: middle;">');
        
        $mform->addElement('header', 'moodle', get_string('settings'));
        
        $select = &$mform->addElement('select', 'tools_database_collation', get_string('tools_database_collation', 'local_zilink'), $options);
        $select->setSelected('utf8_unicode_ci');
        
        $options = array();
        $options['utf8'] = 'utf8';
        
        $select = &$mform->addElement('select', 'tools_database_charset', get_string('tools_database_charset', 'local_zilink'), $options);
        $select->setSelected('utf8');
        
        $this->add_action_buttons(false, get_string('tools_database_exceute','local_zilink'));
        /*
        $mform->addElement('html','</td></tr><tr><td colspan="1" style="text-align:center; border: 0; ">');
        $mform->addElement('html','<input name="submitbutton" value="Save changes" type="submit" id="id_submitbutton">');
        $mform->addElement('html','</td></tr>');
        $mform->addElement('html','</tbody></table>');
        $mform->addElement('html','<div><fieldset>');
         * 
         */
    }
    
    function Display()
    {
        return $this->_form->toHtml();
    }
    
}