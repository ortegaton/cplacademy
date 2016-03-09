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

class zilink_guardian_account_create_step_one_form extends moodleform {
    
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
                    $mform->addElement('select', 'registration', null,$this->_customdata['registration']);
                $mform->addElement('html', '</td>');
                $mform->addElement('html', '<td class="cell c0" style="text-align:center;">');
                    $mform->addElement('select', 'house', null,$this->_customdata['house']);
                $mform->addElement('html', '</td>');
                $mform->addElement('html', '<td class="cell c0" style="text-align:center;">');
                    $mform->addElement('select', 'year', null,$this->_customdata['year']);
                $mform->addElement('html', '</td>');
            $mform->addElement('html', '</tr>');
        $mform->addElement('html', '</tbody>');  
        $mform->addElement('html', '</table>');
        
        //$mform->addElement('hidden', 'step','create');
        
        $this->add_action_buttons(false, get_string('next'));
    }
    
    function Display()
    {
        return $this->_form->toHtml();
    }
}
