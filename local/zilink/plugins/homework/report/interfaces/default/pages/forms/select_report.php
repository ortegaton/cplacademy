<?php

require_once($CFG->libdir.'/formslib.php');

class zilink_report_writer_select_report_form extends moodleform {
    function definition() {

        global $CFG ,$DB,$OUTPUT;
 
        $mform =& $this->_form;
        
        $display = FALSE;
        
        $mform->addElement('hidden', 'cid',$this->_customdata['cid']);
        $mform->setType('cid',PARAM_INT);
        
    
            $mform->addElement('header', 'moodle', get_string('homework_report_subject_reports', 'local_zilink'));
            
            if(!empty($this->_customdata['reports']))
            {
                $mform->addElement('html', '<table name="zilink_homework_report_list" class="generaltable boxaligncenter" width="64%">
                                        <thead>
                                        <tr>
                                            <th class="header c0" style="text-align:left" scope="col">Subject</th>
                                            <th class="header c2" style="text-align:left" scope="col">Cohort</th>
                                            <th class="header c3" style="text-align:center" scope="col">Teachers</th>
                                            <th class="header c4" style="text-align:center" scope="col">Amount Set</th>
                                            <th class="header c5" style="text-align:center" scope="col">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>');
                                        
                                        
                foreach ($this->_customdata['reports'] as $category => $courses ) 
                {
                    
                    foreach ($courses as $cohort => $items) {
                        
                        $mform->addElement('html', '<tr class="r0">');
                        $mform->addElement('html', '<td class="cell c0" style="text-align:left">'.$DB->get_record('course_categories',array( 'id' => $category))->name.'</td>');
                        $mform->addElement('html', '<td class="cell c1" style="text-align:left">'.$DB->get_record('cohort',array( 'id' => $cohort))->name.'</td>');
                        //$mform->addElement('html', '<td class="cell c0" style="text-align:center">'.count($items['teachers']).'</td>');
                        $mform->addElement('html', '<td class="cell c2" style="">');
                        foreach ($items['teachers'] as $teacher) {
                            $mform->addElement('html',$teacher);
                            $mform->addElement('html','<br>');
                        }
                        //$mform->addElement('html', '</td>');
                        $mform->addElement('html', '<td class="cell c4" style="text-align:center">'.$items['homework'].'</td>');
                        $action = html_writer::link('#', $OUTPUT->pix_icon("t/preview", get_string('view')));
                    
                        $name = $cohort . '-'. $this->_customdata['homeworksetperiodstart']. '-' .$this->_customdata['homeworksetperiodend'];
                        $action = '<a href="#" name="'.$name.'" id="zilink_view_homework">'.$OUTPUT->pix_icon("t/preview", get_string('view')).'</a>';
                    
                        $mform->addElement('html', '<td class="cell c3" style="text-align:center">'.$action.'</td></tr>');
                    }
                    
                }


                
                $mform->addElement('html', '</tbody>
                                            </table>');
            }
            else
            {
                $mform->addElement('html', '<table class="generaltable boxaligncenter" width="600px">
                                    <thead>
                                    <tr>
                                    <th class="header c0" style="" scope="col"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="r0">
                                    <td class="cell c0" style="">'.get_string('homework_report_no_reports','local_zilink').'</td>
                                    </tr>
                                    </tbody>
                                    </table');
            } 
            
            
            $mform->addElement('header', 'moodle', get_string('homework_report_homework_set', 'local_zilink'));
            
            $mform->addElement('html',$this->_customdata['homeworklist']);
           
            $mform->addElement('html', '</td>
                                        </tr>
                                        </tbody>
                                        </table>');
            
            $mform->disable_form_change_checker();
           
    }
    
     function display()
    {
        return $this->_form->toHtml();
    }
}
