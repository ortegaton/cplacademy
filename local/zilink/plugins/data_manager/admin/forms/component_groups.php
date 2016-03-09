<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class zilink_data_manager_component_groups_settings_form extends moodleform {
    
    function definition() {

        global $CFG,$DB;
 
        $mform =& $this->_form;
        
        $data = new ZiLinkData();
        
        $mform->addElement('header','general', get_string('settings'));

        if (isset($this->_customdata['component_groups_allowed']))
        {
            $component_groups_allowed = explode(',',$this->_customdata['component_groups_allowed']);
        }
        else 
        {
            $component_groups_allowed = array();
        }
        
        $component = $data->GetGlobalData('assessment_result_component_groups');

        $list = array();
        
        if(isset($component->componentgroups->AssessmentResultComponentGroup))
        {
            foreach($component->componentgroups->AssessmentResultComponentGroup as $group)
            {
                $list[$group->Attribute('RefId')] = $group->Name;
            }
            
            $select = $mform->addElement('select', 'data_manager_component_groups_allowed', get_string('data_manager_component_groups_allowed','local_zilink'),$list, array('size' => 20));
            $select->setMultiple(true);
            
            if(!empty($component_groups_allowed))
            {
                $select->setSelected($component_groups_allowed);
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