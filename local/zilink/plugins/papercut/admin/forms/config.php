<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class zilink_papercut_config_form extends moodleform {
    
    function definition() {

        global $CFG,$DB;
 
        $mform =& $this->_form;
        
        if (isset($this->_customdata['papercut_external'])) {
            $papercut_external = $this->_customdata['papercut_external'];
        } else {
            $papercut_external = 0;
        }
        
        if (isset($this->_customdata['papercut_widget'])) {
            $papercut_widget = $this->_customdata['papercut_widget'];
        } else {
            $papercut_widget = 'balance';
        } 
        
        $select = &$mform->addElement('text', 'papercut_url', get_string('papercut_url', 'local_zilink'),array('size' => 100));
        $select->setType(PARAM_LOCALURL);
        $mform->addHelpButton('papercut_url','papercut_url','local_zilink');
        
        $list = array();           
        $list['0'] = 'No';
        $list['1'] = 'Yes';  
        
        $select = &$mform->addElement('select', 'papercut_external', get_string('papercut_external', 'local_zilink'),$list);
        $select->setSelected($papercut_external);
        $mform->addHelpButton('papercut_external','papercut_external','local_zilink');
        
        $list = array();           
        $list['balance'] = 'Balance';
        $list['environment'] = 'Environment'; 
        
        $select = &$mform->addElement('select', 'papercut_widget', get_string('papercut_widget', 'local_zilink'),$list);
        $select->setSelected($papercut_widget);
        $mform->addHelpButton('papercut_widget','papercut_widget','local_zilink');
        
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