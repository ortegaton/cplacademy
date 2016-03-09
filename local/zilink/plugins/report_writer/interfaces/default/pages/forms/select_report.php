<?php

require_once($CFG->libdir.'/formslib.php');

class zilink_report_writer_select_report_form extends moodleform {
    function definition() {

        global $CFG ,$DB,$OUTPUT;
 
        $mform =& $this->_form;
        
        $display = FALSE;
        
        $mform->addElement('hidden', 'cid',$this->_customdata['cid']);
        $mform->setType('cid',PARAM_INT);
        
    
            $mform->addElement('header', 'moodle', get_string('report_writer_my_reports', 'local_zilink'));
            
            if(!empty($this->_customdata['reports']))
            {
                $mform->addElement('html', '<table name="zilink_view_reports" class="generaltable boxaligncenter" width="64%">
                                        <thead>
                                        <tr>
                                            <th class="header c0" style="text-align:left" scope="col">Subject</th>
                                            <th class="header c1" style="" scope="col">Year</th>
                                            <th class="header c2" style="" scope="col">Cohort</th>
                                            <th class="header c3" style="" scope="col">No. Students</th>
                                            <th class="header c4" style="" scope="col">No. Completed</th>
                                            <th class="header c5" style="" scope="col">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>');
                                        
                                        
                foreach ($this->_customdata['reports'] as $report ) 
                {
                    $mform->addElement('html', '<tr class="r0">');
                    $mform->addElement('html', '<td class="cell c0" style="">'.$report->subject.'</td>');
                    $mform->addElement('html', '<td class="cell c1" style="">'.$report->year.'</td>');
                    $mform->addElement('html', '<td class="cell c2" style="">'.$report->cohort.'</td>');
                    $mform->addElement('html', '<td class="cell c3" style="">'.$DB->count_records('cohort_members',array('cohortid' => $report->cohortid)).'</td>');
                    $mform->addElement('html', '<td class="cell c4" style=""></td>');
                    //$mform->addElement('html', '<td class="cell c3" style="">'.$DB->count_records('zilink_report_writer_data',array('reportid' => $report->reportid, 'userid' => $report->userid, 'setting' => 'teachersignoff')).'</td>');
                    
                    $action = html_writer::link('#', $OUTPUT->pix_icon("t/preview", get_string('view')));
                    
                    $action = '<a href="#" name="'.$report->reportid.'" id="zilink_view_reports">'.$OUTPUT->pix_icon("t/preview", get_string('view')).'</a>';
                    
                    $mform->addElement('html', '<td class="cell c3" style="">'.$action.'</td></tr>', array('name' => $report->reportid));
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
                                    <td class="cell c0" style="">'.get_string('noreports','block_zilink').'</td>
                                    </tr>
                                    </tbody>
                                    </table');
            } 
            
            
            $mform->addElement('header', 'moodle', get_string('report_writer_pupil_reports', 'local_zilink'));
            
            $mform->addElement('html',$this->_customdata['pupillist']);
           
             
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