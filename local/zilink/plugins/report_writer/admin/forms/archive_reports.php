<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');

class zilink_student_reporting_archive_reports_form extends moodleform {
    function definition() {

        global $CFG,$DB;
 
        $mform =& $this->_form;
        
        $display = FALSE;
        
        $mform->addElement('hidden', 'cid',$this->_customdata['cid']);
        $mform->setType('cid',PARAM_INT);
        $mform->addElement('hidden', 'tid',$this->_customdata['tid']);
        $mform->setType('tid',PARAM_INT);
        
        

         $mform->addElement('html', '<table class="generaltable boxaligncenter" width="80%">
                                            <thead>
                                            <tr>
                                            <th class="header c0" style="text-align:left;" scope="col">'.get_string('report_writer_edit_report_name', 'local_zilink').'</th>
                                            <th class="header c0" style="text-align:center;" scope="col">'.get_string('report_writer_number_reports_completed', 'local_zilink').'</th>
                                            <th class="header c0" style="text-align:center;"" scope="col">'.get_string('report_writer_report_archive', 'local_zilink').'</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr class="r0">');
                                            
        if (isset($this->_customdata['rows'])) {
            
                foreach ($this->_customdata['rows'] as $row) {
                
                    $mform->addElement('html', '<tr class="r1">');
                    $mform->addElement('html', '<td class="cell c0" style="text-align:left;">'. $row[0].'</td>');
                    $mform->addElement('html', '<td class="cell c0" style="text-align:center;">'. $row[3].'</td>');
                    if ($row[4] <> -1) {
                        $mform->addElement('html', '<td class="cell c0" style="text-align:center;">');
                        $attributes = array('width' => '30%', 'margin' => '3px');
                        $mform->addElement('advcheckbox', 'archive['.$row[5].']','','',array('group'=> 2), array(0,1));
                            if ( $row[4] == 1) {
                                $mform->setDefault('opened['.$row[5].']',1);
                            } else {
                                $mform->setDefault('opened['.$row[5].']',0);
                            }
                        
                        $mform->addElement('html', '</td>');
                    } else {
                        $mform->addElement('html', '<td class="cell c0" style=""></td>');
                    } 
                }         
                
                $mform->addElement('html', '</tr>
                                            </tbody>    
                                            </table>'); 
                
                $buttonarray=array();
                $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
                $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
                $mform->closeHeaderBefore('buttonar'); 

        } 
        
    }
    
     function display()
    {
        return $this->_form->toHtml();
    }
}