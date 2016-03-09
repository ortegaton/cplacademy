<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class zilink_zimbra_config_form extends moodleform {
    
    function definition() {

        global $CFG,$DB;
 
        $mform =& $this->_form;
        
        $select = &$mform->addElement('text', 'zimbra_link', get_string('zimbra_linktext', 'local_zilink'),array('size' => 100));
        $select->setType(PARAM_ALPHANUMEXT);
        
        $select = &$mform->addElement('text', 'zimbra_preauth_key', get_string('zimbra_preauthkey', 'local_zilink'),array('size' => 100));
        $select->setType(PARAM_RAW);
        
        $select = &$mform->addElement('text', 'zimbra_url', get_string('zimbra_linktext', 'local_zilink'),array('size' => 100));
        $select->setType(PARAM_LOCALURL);
        
        $this->add_action_buttons(false, get_string('savechanges'));
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