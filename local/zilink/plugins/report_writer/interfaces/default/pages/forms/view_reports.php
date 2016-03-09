<?php

require_once($CFG->libdir.'/formslib.php');

class zilink_report_writer_view_report_form extends moodleform {
    function definition() {

        global $CFG ,$DB,$OUTPUT;
 
        $mform =& $this->_form;
        
        $display = FALSE;
        
        $mform->addElement('hidden', 'cid',$this->_customdata['cid']);
        $mform->setType('cid',PARAM_INT);


            //$mform->addElement('header', 'moodle', get_string('report_writer_view_student_reports', 'local_zilink'));
            
            $mode = ($this->_customdata['args']['mode'] == 'full') ? '100%' : '68%';
            
            if(!empty($this->_customdata['reports']))
            {
                $mform->addElement('html', '<table name="zilink_view_reports" class="generaltable boxaligncenter" width="'.$mode.'">
                                        <thead>
                                        <tr>
                                            <th class="header c0" style="text-align:left" scope="col">Subject</th>
                                            <th class="header c1" style="text-align:center;" scope="col">Year</th>
                                            <th class="header c2" style="text-align:center;" scope="col">Cohort</th>
                                            <th class="header c3" style="text-align:center;" scope="col">Session</th>
                                            <th class="header c4" style="text-align:center;" scope="col">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>');
                                        
                                        
                foreach ($this->_customdata['reports'] as $report ) 
                {
                    $mform->addElement('html', '<tr class="r0">');
                    $mform->addElement('html', '<td class="cell c0" style="">'.$report->subject.'</td>');
                    $mform->addElement('html', '<td class="cell c1" style="text-align:center;">'.$report->year.'</td>');
                    $mform->addElement('html', '<td class="cell c2" style="text-align:center;">'.$report->cohort.'</td>');
                    $mform->addElement('html', '<td class="cell c3" style="text-align:center;">'.$this->_customdata['sessions'][$report->assessmentsessionrefid].'</td>');
                    
                    $action = html_writer::link('#', $OUTPUT->pix_icon("t/preview", get_string('view')));
                    
                    $action = '<a href="#" name="'.$report->reportid.'" id="zilink_view_reports">'.$OUTPUT->pix_icon("t/preview", get_string('view')).'</a>';
                    
                    $mform->addElement('html', '<td class="cell c3" style="text-align:center;">'.$action.'</td></tr>', array('name' => $report->reportid));
                }


                
                $mform->addElement('html', '</tbody>
                                            </table>');
            }
            else
            {
                $mform->addElement('html', '<table class="generaltable boxaligncenter" width="'.$mode.'">
                                    <thead>
                                    <tr>
                                    <tr>
                                        <th class="header c0" style="text-align:left" scope="col">Subject</th>
                                        <th class="header c1" style="" scope="col">Year</th>
                                        <th class="header c2" style="" scope="col">Cohort</th>
                                        <th class="header c3" style="" scope="col">Session</th>
                                        <th class="header c4" style="" scope="col">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="r0">
                                    <td class="cell c0" style="text-align:left" colspan="5">'.get_string('report_writer_no_published_reports','local_zilink').'</td>
                                    </tr>
                                    </tbody>
                                    </table');
            } 
           
             
              /*
            $buttonarray=array();
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('selectreport','block_zilink'));
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            $mform->closeHeaderBefore('buttonar');
               */
            $mform->addElement('html', '</td>
                                        </tr>
                                        </tbody>
                                        </table>');
              
           
    }
    
     function display()
    {
        return $this->_form->toHtml();
    }
}