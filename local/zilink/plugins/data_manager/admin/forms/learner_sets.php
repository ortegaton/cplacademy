<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class zilink_data_manager_component_groups_settings_form extends moodleform {
    
    function definition() {

        global $CFG,$DB;
 
        $mform =& $this->_form;
        
        $data = new ZiLinkData();
        
        $mform->addElement('header','general', get_string('settings'));
        
        $component = $data->GetGlobalData('assessment_learner_sets');

        $list = array();
        
        foreach($component->componentgroups->AssessmentLets as $group)
        {
            $list[$group->Attribute('RefId')] = $group->Name;
        }
        
        $select = $mform->addElement('select', 'data_manager_componentgroups_allowed', get_string('data_manager_component_groups_allowed','local_zilink'),$list, array('size' => 20));
        $select->setMultiple(true);
        
        $this->add_action_buttons(false, get_string('savechanges'));
    }
    
    function Display()
    {
        return $this->_form->toHtml();
    }
}