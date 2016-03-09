<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class zilink_data_manager_sessions_settings_form extends moodleform {
    
    function definition() {

        global $CFG,$DB,$OUTPUT;
 
        $mform =& $this->_form;
        
        $data = new ZiLinkData();
        
        $session = $data->GetGlobalData('assessment_sessions');

        $list = array();
        
        if(isset($session->sessions->AssessmentSession))
        {
            foreach($session->sessions->AssessmentSession as $resultset)
            {
                foreach($resultset->SIF_ExtendedElements->SIF_ExtendedElement as $extendedElement)
                {
                    if($extendedElement->Attribute("Name") == "Name") {
                        $list[$resultset->Attribute('RefId')]  = (string)$extendedElement;
                    }
                }
            }
            
            if (isset($this->_customdata['sessions_allowed']))
            {
                $sessions_allowed = explode(',',$this->_customdata['sessions_allowed']);
            }
            else 
            {
                $sessions_allowed = array();
            }
            
            if (isset($this->_customdata['sessions_order'])) {
                
                $sessions_order = array();
                
                foreach(explode(",",$this->_customdata['sessions_order']) as $key)
                {
                    if(isset($list[$key]))
                    {
                        $sessions_order[$key] = $list[$key];
                        unset($list[$key]);
                    }
                }
                
                $sessions_order = array_merge($sessions_order,$list);
            } else {
                $sessions_order = $list;
            }
            
            $mform->addElement('header','general', get_string('settings'));
            
            $select = $mform->addElement('select', 'data_manager_sessions_allowed', get_string('data_manager_sessions_allowed','local_zilink'),$sessions_order, array('size' => 20, 'style' => 'min-width:200px;'));
            $select->setMultiple(true);
            
            if(!empty($sessions_allowed))
            {
                $select->setSelected($sessions_allowed);
            }
            $mform->addHelpButton('data_manager_sessions_allowed', 'data_manager_sessions_allowed', 'local_zilink');
            
            $mform->addElement('html','<div id="fitem_id_data_manager_sessions_order_buttons" class="fitem fitem_fselect"><div class="fitemtitle"><label></label></div><div class="felement fselect"><input id="id_assessment_session_up" type="button" value="up" style="width:100px; height:30px;"><input id="id_assessment_session_down" type="button" value="Down" style="width:100px; height:30px;"></div></div>');
            
            $mform->addElement('hidden', 'sessions_order',implode(',',array_keys($sessions_order)),array('id' => 'id_sessions_order'));
            $mform->setType('sessions_order',PARAM_TEXT);
            $mform->setType('sessions_order',PARAM_TEXT);
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