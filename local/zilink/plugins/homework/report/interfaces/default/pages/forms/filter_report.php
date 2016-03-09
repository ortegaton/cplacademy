<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class zilink_homework_report_filter_report_form extends moodleform {
    function definition() {

        global $CFG,$USER, $DB;
 
        $mform =& $this->_form;
        
        $display = FALSE;
        
        $mform->addElement('hidden', 'cid',$this->_customdata['cid']);
        $mform->addElement('hidden', 'action','list');
        $mform->setType('cid',PARAM_INT);
        $mform->setType('action',PARAM_RAW);
        
        $mform->addElement('header', 'zilink_report_filter_header', get_string('homework_report_filter', 'local_zilink'));
        
        $mform->addElement('html', '<table class="generaltable boxaligncenter" cellpadding="10px">');
        $mform->addElement('html', '<thead><tr>');
        $mform->addElement('html', '<th class="header c0" style="text-align:center;" scope="col">'.get_string('category').'</th>');
        $mform->addElement('html', '<th class="header c0" style="text-align:center;" scope="col">'.get_string('cohort','cohort').'</th>');
        $mform->addElement('html', '<th class="header c0" style="text-align:center;" scope="col">'.get_string('teacher', 'local_zilink').'</th>');
        $mform->addElement('html', '<th class="header c0" style="text-align:center;" scope="col">'.get_string('timeframe', 'local_zilink').'</th>');
        $mform->addElement('html', '</tr></thead>');
        $mform->addElement('html', '<tbody>');
            $mform->addElement('html', '<tr class="r0">');
                $mform->addElement('html', '<td class="cell c0" style="text-align:center;">');
                    $select = $mform->addElement('select', 'category', null, $this->_customdata['categories']);
                    $select->setSelected($this->_customdata['categoryid']);
                    //$mform->addElement('select', 'registration', null,$this->_customdata['registration']);
                $mform->addElement('html', '</td>');
                $mform->addElement('html', '<td class="cell c0" style="text-align:center;">');
                    $select =$mform->addElement('select', 'cohort', null, $this->_customdata['cohorts']);
                    $select->setSelected($this->_customdata['cohortid']);
                    //$mform->addElement('select', 'house', null,$this->_customdata['house']);
                $mform->addElement('html', '</td>');
                $mform->addElement('html', '<td class="cell c0" style="text-align:center;">');
                    $select = $mform->addElement('select', 'teacher', null, $this->_customdata['teachers']);
                    $select->setSelected($this->_customdata['teacherid']);
                $mform->addElement('html', '</td>');
                $mform->addElement('html', '<td class="cell c0" style="text-align:center;">');
                    
                    
                    $availablefromgroup=array();
                    $availablefromgroup[] =& $mform->createElement('date_selector', 'homeworksetperiodstart', 'From');
                    $availablefromgroup[] =& $mform->createElement('date_selector', 'homeworksetperiodend', '');
                    $mform->addGroup($availablefromgroup, 'availablefromgroup', '', ' To ', false);
                    
                    $mform->addElement('checkbox', 'datefilterenabled', '', get_string('enable'));
                    
                    $mform->disabledIf('availablefromgroup', 'datefilterenabled');
                $mform->addElement('html', '</td>');
            $mform->addElement('html', '</tr>');
        $mform->addElement('html', '</tbody>');  
        $mform->addElement('html', '</table>');

        $this->add_action_buttons(null,'Search');
        
    }
    
     function display()
    {
        return $this->_form->toHtml();
    }
}