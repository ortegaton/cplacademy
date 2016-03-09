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

class zilink_guardian_account_manage_step_three_form extends moodleform {
    
    function definition() {
        
        global $CFG,$DB;
 
        $mform = &$this->_form;
        
        $mform->addElement('header', 'moodle', get_string('students'));
        
        $mform->addElement('html', '<table class="generaltable boxaligncenter" cellpadding="10px">');
        $mform->addElement('html', '<thead><tr>');
        $mform->addElement('html', '<th class="header c0" style="text-align:left;" scope="col">'.get_string('student','local_zilink').'</th>');
        $mform->addElement('html', '<th class="header c0" style="text-align:left;" scope="col">'.get_string('guardian', 'local_zilink').'</th>');
        $mform->addElement('html', '<th class="header c0" style="text-align:center;" scope="col">'.get_string('unlinked', 'local_zilink').'</th>');
        $mform->addElement('html', '</tr></thead>');
        $mform->addElement('html', '<tbody>');
            
            foreach ($this->_customdata['students'] as $student)
            {
                $count = 0;
                foreach ($student->guardians as $guardian) {
                    
                        $mform->addElement('html', '<tr class="r0">');
                        if($count == 0) {
                            $mform->addElement('html', '<td class="cell c0" style="text-align:left; vertical-align: middle;" rowspan="'.$this->GuardianCount($student).'">');
                            $mform->addElement('html',$student->fullname);
                            $mform->addElement('html', '</td>');
                        }
                        $mform->addElement('html', '<td class="cell c0" style="text-align:left; vertical-align: middle;">');
                        $mform->addElement('html',$guardian->fullname);
                        $mform->addElement('html', '</td>');
                        
                        $mform->addElement('html', '<td class="cell c0" style="text-align:center; vertical-align: middle;">');
                        $mform->addElement('html', 'Yes');
                        $mform->addElement('html', '</td>');
                        
                        $mform->addElement('html', '</tr>');
                        $count++;

                }
                
            }

            
        $mform->addElement('html', '</tbody>');  
        $mform->addElement('html', '</table>');
       
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
