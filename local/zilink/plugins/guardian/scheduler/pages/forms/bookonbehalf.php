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


/**
 * Defines forms for {@see edit.php}
 *
 * Defines {@see parentseve_form} and {@see parenteseve_teacher_form()} for displaying
 * forms in edit.php, used for creating and editing of parents' evenings.
 *
 * @package local_zilink
 * @author Mark Johnson <johnsom@tauntons.ac.uk>, Mike Worth <mike@mike-worth.com>
 * @copyright Copyright &copy; 2009, Taunton's College, Southampton, UK
 */

require_once($CFG->libdir.'/formslib.php');

/**
 * Defines the configuration form
 *
 */
class guardian_scheduler_booking_onbehalf_form extends moodleform {

    var $rawdata;
    /**
     * Defines the form elements
     */
    public function definition() {
        global $DB,$USER;
        $mform    =& $this->_form;
        
        $mform->addElement('header',
                           'parentseveheader',
                           get_string('guardian_scheduler_book', 'local_zilink'));
        

        if(!empty($this->_customdata['slots']))
        {
            $mform->addElement('html', '<br><table name="zilink_view_reports" class="generaltable boxalignleft" width="100%">
                                        <thead>
                                        <tr>
                                            <th class="header c2" style="text-align:center;" scope="col">'.get_string('time', 'local_zilink').'</th>
                                            <th class="header c0" style="text-align:center;" scope="col">'.get_string('subject', 'local_zilink').'</th>
                                            <th class="header c1" style="text-align:center;" scope="col">'.get_string('student', 'local_zilink').'</th>
                                            <th class="header c1" style="text-align:center;" scope="col">'.get_string('guardian', 'local_zilink').'</th>
                                        </tr>
                                        </thead>
                                        <tbody>');
            //date_default_timezone_set('UTC');
            $dateTime = new DateTime(); 
            $dateTime->setTimezone(new DateTimeZone('UTC'));                     
            foreach( $this->_customdata['slots'] as $time => $booking)
            {
                
                
                if(isset($this->_customdata['bookings'][$time])) {
                    $dateTime->setTimestamp ($this->_customdata['bookings'][$time]->apptime);    
                    
                    $mform->addElement('html', '<tr class="r0">'); 
                    $mform->addElement('html', '<td class="cell c0" style="text-align:center;">'. $dateTime->format('H:i').'</td>');
                    $mform->addElement('html', '<td class="cell c0" style="text-align:center;"><p style="margin-left: 16%; margin-bottom:0px">'.$DB->get_record('course_categories',array('id' => $this->_customdata['bookings'][$time]->subjectid))->name.'</p></td>');
                    $mform->addElement('html', '<td class="cell c0" style="text-align:center;"><p style="margin-left: 16%; margin-bottom:0px">'. fullname($DB->get_record('user',array('id' => $this->_customdata['bookings'][$time]->studentid))).'</p></td>');
                    $mform->addElement('html', '<td class="cell c0" style="text-align:center;"><p style="margin-left: 16%; margin-bottom:0px">'. fullname($DB->get_record('user',array('id' => $this->_customdata['bookings'][$time]->guardianid))).'</p></td>');
                    $mform->addElement('html', '</tr>');  
                } else {
                    $mform->addElement('html', '<tr class="r0">');
                    $mform->addElement('html', '<td class="cell c0" style="text-align:center;">'. $booking.'</td>');
                    $mform->addElement('html', '<td class="cell c0" style="text-align:center;">');
                    $mform->addElement('select', 'subjects['.$time.']', '',array_merge(array(0 => get_string('guardian_scheduler_no_appointment_required','local_zilink')),$this->_customdata['subjects']),array('class' => 'test'));
                    $mform->addElement('html', '</td>');
                    $mform->addElement('html', '<td class="cell c0" style="text-align:center;">');
                    $mform->addElement('select', 'students['.$time.']', '', array_merge(array(0 => get_string('guardian_scheduler_select_student','local_zilink')),$this->_customdata['students']));
                    $mform->addElement('html', '</td>');
                    $mform->addElement('html', '<td class="cell c0" style="text-align:center;">');
                    $mform->addElement('select', 'guardians['.$time.']', '',array(0 => get_string('guardian_scheduler_select_student','local_zilink')));
                    $mform->addElement('html', '</td>');
                    $mform->addElement('html', '</tr>');  
                }
            }
            
            $mform->addElement('html', '</tbody>
                                        </table>');
        }
                           
        $mform->addElement('hidden', 'session', $this->_customdata['sid']);
        $mform->setType('session',PARAM_INT);
        $mform->addElement('hidden', 'offset','second');
        $mform->setType('offset',PARAM_RAW);
        /*
        if (get_config('local_progressreview', 'version')) {
            $sessions = $DB->get_records_menu('progressreview_session', array(), 'deadline_tutor DESC');
            $sessions = array(get_string('choosedots')) + $sessions;
            $mform->addElement('select',
                               'importteachers',
                               get_string('guardian_scheduler_importteachers', 'local_zilink'),
                               $sessions);
        }
        */
        /*
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('guardian_scheduler_book','local_zilink'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar'); 
        */
        $this->add_action_buttons(false);
    }

    public function definition_after_data() {
        $mform = $this->_form;
        //$mform->disabledIf('importteachers', 'parentseve', 'neq', '');
    }

    function get_data() {
        $mform =& $this->_form;
  
        if (!$this->is_cancelled() and $this->is_submitted() and $this->is_validated()) {
            return (object)$mform->_submitValues;
        } else {
            return null;
       }
   }

    function display()
    {
        return $this->_form->toHtml();
    }
}

class guardian_scheduler_select_child_form extends moodleform {

    /**
     * Defines the form elements
     */
    public function definition() {
        global $DB,$USER;
        $mform    =& $this->_form;
        
        $mform->addElement('header',
                           'parentseveheader',
                           get_string('guardian_scheduler_multiple_children', 'local_zilink'));

        
        $mform->addElement('select','child',get_string('guardian_scheduler_select_child', 'local_zilink'),$this->_customdata['children']);
        $mform->setType('child',PARAM_INT);         
        $mform->addElement('hidden', 'session');
        $mform->setType('session',PARAM_INT);
        /*
        if (get_config('local_progressreview', 'version')) {
            $sessions = $DB->get_records_menu('progressreview_session', array(), 'deadline_tutor DESC');
            $sessions = array(get_string('choosedots')) + $sessions;
            $mform->addElement('select',
                               'importteachers',
                               get_string('guardian_scheduler_importteachers', 'local_zilink'),
                               $sessions);
        }
        */
        //$this->add_action_buttons(false);
        
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('guardian_scheduler_select_child','local_zilink'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar'); 
    }

    public function definition_after_data() {
        $mform = $this->_form;
        //$mform->disabledIf('importteachers', 'parentseve', 'neq', '');
    }
    function display()
    {
        return $this->_form->toHtml();
    }
}