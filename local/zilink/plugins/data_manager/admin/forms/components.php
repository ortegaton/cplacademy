<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class zilink_data_manager_components_settings_form extends moodleform {
    
    function definition() {

        global $CFG,$DB;
 
        $mform =& $this->_form;
        
        $data = new ZiLinkData();
        
        $mform->addElement('header','general', get_string('settings'));

        if (isset($this->_customdata['components_allowed']))
        {
            $components_allowed = explode(',',$this->_customdata['components_allowed']);
        }
        else 
        {
            $components_allowed = array();
        }
        
        $component = $data->GetGlobalData('assessment_result_components');

        $list = array();
        if(!empty($component))
        {
            foreach($component->components->AssessmentResultComponent as $aspect)
            {
                $list[$aspect->Attribute('RefId')] = $aspect->Name;
            }
            
            $select = $mform->addElement('select', 'data_manager_components_allowed', get_string('data_manager_components_allowed','local_zilink'),$list, array('size' => 20));
            $select->setMultiple(true);
            
            if(!empty($components_allowed))
            {
                $select->setSelected($components_allowed);
            }
        }
        else
        {
            $mform->addElement('static', 'guardian_view_default_awaiting_data','',get_string('data_manager_awaiting_data','local_zilink'));
        } 
        
        $this->add_action_buttons(false, get_string('savechanges'));
    }
    
    function Display()
    {
        return $this->_form->toHtml();
    }
    
}