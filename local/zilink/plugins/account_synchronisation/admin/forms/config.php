<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class enrol_zilink_account_synchronisation_config_form extends moodleform {
    
    var $data;
    var $matcheduser;
    
    function definition() {

        global $CFG,$DB;
 
        $mform =& $this->_form;
        
        $options = array();
        $options[0] = get_string('disable');
        $options[1] = get_string('enable');
        
        $mform->addElement('html','<table class="generaltable tableleft boxaligncenter" width="100%" cellpadding="10px">
                                    <tbody><tr><td class="cell c1" style="text-align:left; margin-left 50px; vertical-align: middle;">');
        
        $select = &$mform->addElement('select', 'account_synchronisation_cron', get_string('account_synchronisation_cron', 'local_zilink'), $options, array('style' => 'margin-bottom: 3px; margin-left: 50px;'));
        $select->setSelected($CFG->zilink_account_synchronisation_cron);
        
        $mform->addElement('text', 'account_synchronisation_exclude_usernames', get_string('account_synchronisation_exclude_usernames', 'local_zilink'),array('size' => 75, 'style' => 'margin-left: 50px;'));
        $mform->setType('account_synchronisation_exclude_usernames',PARAM_RAW);
        $mform->addHelpButton('account_synchronisation_exclude_usernames', 'account_synchronisation_exclude_usernames', 'local_zilink');
        
        $mform->addElement('html','</td></tr><tr><td colspan="1" style="text-align:center; border: 0; ">');
        $mform->addElement('html','<input name="submitbutton" value="Save changes" type="submit" id="id_submitbutton">');
        $mform->addElement('html','</td></tr>');
        $mform->addElement('html','</tbody></table>');
        $mform->addElement('html','<div><fieldset>');
    }
    
    function Display()
    {
        return $this->_form->toHtml();
    }
    
}