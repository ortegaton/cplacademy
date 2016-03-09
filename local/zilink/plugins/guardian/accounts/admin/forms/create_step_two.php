<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class zilink_guardian_account_create_step_two_form extends moodleform {
    
    function definition() {
        
        global $CFG,$DB;
 
        $mform = &$this->_form;
        
        $mform->addElement('header', 'moodle', get_string('filter', 'local_zilink'));
        
        $mform->addElement('html', '<table class="generaltable boxaligncenter" cellpadding="10px">');
        $mform->addElement('html', '<thead><tr>');
        $mform->addElement('html', '<th class="header c0" style="text-align:center;" scope="col">'.get_string('registration', 'local_zilink').'</th>');
        $mform->addElement('html', '<th class="header c0" style="text-align:center;" scope="col">'.get_string('house', 'local_zilink').'</th>');
        $mform->addElement('html', '<th class="header c0" style="text-align:center;" scope="col">'.get_string('year', 'local_zilink').'</th>');
        $mform->addElement('html', '</tr></thead>');
        $mform->addElement('html', '<tbody>');
            $mform->addElement('html', '<tr class="r0">');
                $mform->addElement('html', '<td class="cell c0" style="text-align:center;">');
                    $mform->addElement('html', $this->_customdata['filters']['registration']);
                $mform->addElement('html', '</td>');
                $mform->addElement('html', '<td class="cell c0" style="text-align:center;">');
                    $mform->addElement('html', $this->_customdata['filters']['house']);
                $mform->addElement('html', '</td>');
                $mform->addElement('html', '<td class="cell c0" style="text-align:center;">');
                    $mform->addElement('html', $this->_customdata['filters']['year']);
                $mform->addElement('html', '</td>');
            $mform->addElement('html', '</tr>');
        $mform->addElement('html', '</tbody>');  
        $mform->addElement('html', '</table>');
        
        $mform->addElement('header', 'moodle', get_string('students'));
        
        $mform->addElement('html', '<table class="generaltable boxaligncenter" cellpadding="10px">');
        $mform->addElement('html', '<thead><tr>');
        $mform->addElement('html', '<th class="header c0" style="text-align:left;" scope="col">'.get_string('student','local_zilink').'</th>');
        $mform->addElement('html', '<th class="header c0" style="text-align:left;" scope="col">'.get_string('guardian', 'local_zilink').'</th>');
        $mform->addElement('html', '<th class="header c0" style="text-align:center;" scope="col">'.get_string('relationship', 'local_zilink').'</th>');
        $mform->addElement('html', '<th class="header c0" style="text-align:center;" scope="col">'.get_string('priority', 'local_zilink').'</th>');
        $mform->addElement('html', '<th class="header c0" style="text-align:center;" scope="col">'.get_string('create', 'local_zilink').'</th>');
        $mform->addElement('html', '</tr></thead>');
        $mform->addElement('html', '<tbody>');
            
            
            foreach ($this->_customdata['students'] as $student)
            {
                //var_dump($student);
                //die();
                $count = 0;
                foreach ($student->guardians as $guardian) {
                    
                    if(!$guardian->linked) 
                    {
                        $mform->addElement('html', '<tr class="r0">');
                        if($count == 0) {
                            $mform->addElement('html', '<td class="cell c0" style="text-align:left; vertical-align: middle;" rowspan="'.$this->GuardianCount($student).'">');
                            $mform->addElement('html',fullname($student->user));
                            $mform->addElement('html', '</td>');
                        }
                        $mform->addElement('html', '<td class="cell c0" style="text-align:left; vertical-align: middle;">');
                        $mform->addElement('html',$guardian->fullname);
                        $mform->addElement('html', '</td>');
                        
                        $mform->addElement('html', '<td class="cell c0" style="text-align:center; vertical-align: middle;">');
                        $mform->addElement('html',$guardian->relationship);
                        $mform->addElement('html', '</td>');
                        
                        $mform->addElement('html', '<td class="cell c0" style="text-align:center; vertical-align: middle;">');
                        $mform->addElement('html',$guardian->priority);
                        $mform->addElement('html', '</td>');
                        
                        $mform->addElement('html', '<td class="cell c0" style="text-align:center; vertical-align: middle;">');
                        if(!$guardian->linked) {
                            $mform->addElement('advcheckbox', 'link['.$student->idnumber.']['.$guardian->idnumber.']', null, '', array('group' => 1));
                        }
                        $mform->addElement('html', '</td>');
                        
                        $mform->addElement('html', '</tr>');
                        $count++;
                    }
                }
                
            }

            $mform->addElement('html', '<tr class="r0">');
                    
                $mform->addElement('html', '<td class="cell c0" style="text-align:left;"></td>');
                $mform->addElement('html', '<td class="cell c0" style="text-align:left;"></td>');
                $mform->addElement('html', '<td class="cell c0" style="text-align:left;"></td>');
                $mform->addElement('html', '<td class="cell c0" style="text-align:left;"></td>');
                $mform->addElement('html', '<td class="cell c0" style="text-align:center; vertical-align: middle;">');
                    $this->add_checkbox_controller(1);
                $mform->addElement('html', '</td>');
                    
            $mform->addElement('html', '</tr>');
        $mform->addElement('html', '</tbody>');  
        $mform->addElement('html', '</table>');
       
        $mform->addElement('hidden','h', $this->_customdata['filters']['house'] );
        $mform->addElement('hidden','y', $this->_customdata['filters']['year'] );
        $mform->addElement('hidden','r', $this->_customdata['filters']['registration'] );
        
        $mform->setType('h',PARAM_TEXT);
        $mform->setType('y',PARAM_TEXT);
        $mform->setType('r',PARAM_TEXT);
        
                
        $mform->addElement('header', 'moodle', get_string('guardian_accounts_assign_role', 'local_zilink'));
        
        $where = $DB->sql_like('shortname', ':var', false, false, false);
        $params['var'] = '%zilink_guardian%';
        $roles = $DB->get_records_sql("SELECT id, name FROM {role} WHERE $where", $params);
        
        $list = array();
        
        foreach ($roles as $role) {
            $list[$role->id] = $role->name;
        }
        
        $mform->addElement('select', 'role', '',$list);
        
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('cancel');
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('next'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar'); 
        //$this->add_action_buttons(false, get_string('next'));
    }
    
    function Display()
    {
        return $this->_form->toHtml();
    }
    
    function GuardianCount($student) {
        
        $count = 0;
        
        foreach ($student->guardians as $guardian) {
            
            if(!$guardian->linked) 
            {
                    $count++;
            }
        
        }
        
        return $count;
    }
}
