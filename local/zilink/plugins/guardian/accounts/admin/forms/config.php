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

class zilink_guardian_account_settings_form extends moodleform {
    
    function definition() {
        
        global $CFG,$DB;
 
        $mform = &$this->_form;
        
        $mform->addElement('header', 'moodle', get_string('guardian_accounts', 'local_zilink').' ' .get_string('config', 'local_zilink'));
        
        $table = new html_table();
        $table->cellpadding = '10px';
        $table->width = '49%';
        $table->head = array(get_string('guardian_accounts_mandatory_settings', 'local_zilink'), '');
        $table->align = array('left', 'left');
        $table->border = '2px';
        $table->tablealign = 'center';
        
        $cells = array();
        
        $cells[] = '<b>User Profile Fields</b>';
        $cells[] = '<b>Action</b>';
        
        $cells[] = 'Firstname';
        $cells[] = 'Locked';
        
        $cells[] = 'Lastname';
        $cells[] = 'Locked';
        
        $cells[] = 'ID Number';
        $cells[] = 'Locked';
        
        $cells[] = 'Email Display';
        $cells[] = 'Locked';
        
        $cells[] = 'Language';
        $cells[] = 'Locked';
        
        $cells[] = 'Timezone';
        $cells[] = 'Locked';
        
        $table->data = array_chunk($cells, 2);
        $mform->addElement('html', html_writer::table($table));
        
        $mform->addElement('html', '<table class="generaltable boxaligncenter" width="49%" cellpadding="10px">');
        $mform->addElement('html', '<thead><tr>');
        $mform->addElement('html', '<th class="header c0" style="text-align:left;" scope="col">'.get_string('guardian_accounts_optional_settings', 'local_zilink').'</th>');
        $mform->addElement('html', '</tr></thead>');
        $mform->addElement('html', '<tbody>');
            $mform->addElement('html', '<tr class="r0">');
                $mform->addElement('html', '<td class="cell c0" style="text-align:left;"><b>Guardian Accounts</b></td>');
            $mform->addElement('html', '</tr>');
            $mform->addElement('html', '<tr class="r0">');
                $mform->addElement('html', '<td class="cell c0" style="text-align:left;">');
                    $mform->addElement('text', 'username_prefix', get_string('guardian_accounts_username_prefix', 'local_zilink'));
                    $mform->setType('username_prefix',PARAM_TEXT);
                $mform->addElement('html', '</td>');
            $mform->addElement('html', '</tr>');
            $mform->addElement('html', '<tr class="r0">');
                $mform->addElement('html', '<td class="cell c0" style="text-align:left;">');
                    $mform->addElement('selectyesno', 'email_required', get_string('guardian_accounts_email_required', 'local_zilink'));
                $mform->addElement('html', '</td>');
            $mform->addElement('html', '</tr>');
            $mform->addElement('html', '<tr class="r0">');
                $mform->addElement('html', '<td class="cell c0" style="text-align:left;"><b>User Profile Fields</b></td>');
            $mform->addElement('html', '</tr>');
            $mform->addElement('html', '<tr class="r0">');
                $mform->addElement('html', '<td class="cell c0" style="text-align:left;">');
                    $mform->addElement('text', 'default_city', get_string('guardian_accounts_default_city', 'local_zilink'));
                    $mform->setType('default_city',PARAM_TEXT);
                $mform->addElement('html', '</td>');
            $mform->addElement('html', '</tr>');
            $mform->addElement('html', '<tr class="r0">');
                $mform->addElement('html', '<td class="cell c0" style="text-align:left;" >');
                    $mform->addElement('select', 'default_country', get_string('guardian_accounts_default_country', 'local_zilink'),get_string_manager()->get_list_of_countries());
                $mform->addElement('html', '</td>');
            $mform->addElement('html', '<tr class="r0">');
                $mform->addElement('html', '<td class="cell c0" style="text-align:left;" >');
                    $mform->addElement('select', 'default_language', get_string('guardian_accounts_default_language', 'local_zilink'),get_string_manager()->get_list_of_languages());
                $mform->addElement('html', '</td>');
            $mform->addElement('html', '</tr>');
        $mform->addElement('html', '</tbody>');
        $mform->addElement('html', '</table>'); 

        $this->add_action_buttons(false, get_string('savechanges'));
    }
    
    function Display()
    {
        return $this->_form->toHtml();
    }
}
