<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class zilink_tools_ldap_sync_form extends moodleform {
    
    function definition() {

        global $CFG,$DB,$OUTPUT;
 
        $mform =& $this->_form;
        
        
        $config =  get_config('auth/ldap');
        $content = get_string('tools_ldap_sync_ous','local_zilink');      
        
        if(isset($config->contexts))
        {
         
            foreach(explode(';',$config->contexts) as $context)
            {
                $content  .= $context.'<br>';
            }
        }
        else
        {
            $content .= get_string('tools_ldap_sync_non_contexts','local_zilink');
        }
        
        $mform->addElement('html', $OUTPUT->box($content));
                
        $mform->addElement('hidden', 'import', 1);
        $mform->setType('import',PARAM_INT);
        
        $this->add_action_buttons(false, get_string('tools_ldap_sync_import','local_zilink'));
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