<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');

class zilink_student_reporting_edit_report_form extends moodleform {
    function definition() {

        global $CFG,$DB;
 
        $mform =& $this->_form;
        
        $display = FALSE;
        
        $mform->addElement('hidden', 'cid',$this->_customdata['cid']);
        $mform->setType('cid',PARAM_INT);
        $mform->addElement('hidden', 'tid',$this->_customdata['tid']);
        $mform->setType('tid',PARAM_INT);
        $mform->addElement('hidden', 'rid',$this->_customdata['rid']);
        $mform->setType('rid',PARAM_INT);
        
        $report = $DB->get_record('zilink_report_writer_reports',array('id' => $this->_customdata['rid']));
        
                $mform->addElement('html', '<table class="generaltable boxaligncenter" width="600px">
                                            <thead>
                                            <tr>
                                            <th class="header c0" style="" scope="col" colspan=2>Edit Report</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr class="r0">
                                            <td class="cell c0" style="">'.get_string('report_writer_edit_report_name','local_zilink').'</td>
                                            <td class="cell c1 lastcol" style="">');                     
       
                
                $attributes = array('style' => 'width: 100%');
                $mform->addElement('static', 'name','',$DB->get_record('course_categories',array('id' => $report->subjectid))->name);
                
                $mform->addElement('html', '</td>
                                            </tr>
                                            <tr class="r1">
                                            <td class="cell c0" style="">'.get_string('report_writer_edit_template_year','local_zilink').'</td><td class="cell c1 lastcol" style="">');

                $mform->addElement('static', 'year','',$DB->get_record('course_categories',array('id' => $report->yearid))->name);
                
                $mform->addElement('html', '</td>
                                            </tr>
                                            <tr class="r1">
                                            <td class="cell c0" style="">'.get_string('report_writer_edit_template_cohort','local_zilink').'</td><td class="cell c1 lastcol" style="">');
                
                $mform->addElement('static', 'cohort','',$DB->get_record('cohort',array('id' => $report->cohortid))->name);
                
                $mform->addElement('html', '</td>
                                            </tr>
                                            <tr class="r1">
                                            <td class="cell c0" style="">'.get_string('report_writer_edit_aspect_mapping','local_zilink').'</td><td class="cell c1 lastcol" style="">');
                
                
                

                $components = array_merge(array( 'NR'=> get_string('report_writer_not_required','local_zilink')),$this->_customdata['components']);
                
                $mform->addElement('select', 'component1', '1', $components);
                $mform->addElement('select', 'component2', '2', $components);
                $mform->addElement('select', 'component3', '3', $components);
                $mform->addElement('select', 'component4', '4', $components);
                $mform->addElement('select', 'component5', '5', $components);
                $mform->addElement('select', 'component6', '6', $components);
                
                (!empty($report->component1)) ?  $mform->setDefault('component1',$report->component1) : $mform->setDefault('component1','NR');
                (!empty($report->component2)) ?  $mform->setDefault('component2',$report->component2) : $mform->setDefault('component2','NR');
                (!empty($report->component3)) ?  $mform->setDefault('component3',$report->component3) : $mform->setDefault('component3','NR');
                (!empty($report->component4)) ?  $mform->setDefault('component4',$report->component4) : $mform->setDefault('component4','NR');
                (!empty($report->component5)) ?  $mform->setDefault('component5',$report->component5) : $mform->setDefault('component5','NR');
                (!empty($report->component6)) ?  $mform->setDefault('component6',$report->component6) : $mform->setDefault('component6','NR');
                
                
                                $mform->addElement('html', '</td>
                                            </tr>
                                            <tr class="r1">
                                            <td class="cell c0" style="">'.get_string('report_writer_edit_report_session','local_zilink').'</td><td class="cell c1 lastcol" style="">');
                $mform->addElement('select', 'assessmentsessionrefid', null, $this->_customdata['sessions']);
                $mform->setDefault('assessmentsessionrefid',$report->assessmentsessionrefid );
                $mform->addHelpButton('assessmentsessionrefid', 'report_writer_no_sessions', 'local_zilink');
                
                
                $mform->addElement('html', '</tr>
                                            <tr class="r0 lastrow">
                                            <td class="cell c0" style="">Status</td>
                                            <td class="cell c1 lastcol" style="">');
                $attributes = array('width' => '30%', 'margin' => '3px');
                $mform->addElement('advcheckbox', 'open', get_string('report_writer_report_opened','local_zilink'), '', array('group' => 1), array(0, 1));
               
                if ( $report->open == 1) {
                    $mform->setDefault('open',1);
                } else {
                    $mform->setDefault('open',0);
                }
                $mform->addElement('html', '</td>
                                            </tr>');
                
                
                $mform->addElement('html', '</tr>
                                            <tr class="r0 lastrow">
                                            <td class="cell c0" style="">&nbsp;</td>
                                            <td class="cell c1 lastcol" style="">');
                $attributes = array('width' => '30%', 'margin' => '3px');
                $mform->addElement('advcheckbox', 'published', get_string('report_writer_report_published','local_zilink'),'',array('group'=> 2), array(0,1));
                if ( $report->published == 1) {
                    $mform->setDefault('published',1);
                } else {
                    $mform->setDefault('published',0);
                }
                                            
                $mform->addElement('html', '</td>
                                            </tr>');
                
                $mform->addElement('html', '</tr>
                                            <tr  class="r0 lastrow">
                                            <td class="cell c0" style="">&nbsp;</td>
                                            <td class="cell c1 lastcol" style="">');
                
                
                
                
                $mform->addElement('html', '</td>
                                            </tr>
                                            </tbody>    
                                            </table>'); 
                                            
                $buttonarray=array();
                $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
                $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
                $mform->closeHeaderBefore('buttonar'); 
        
    }
    
     function display()
    {
        return $this->_form->toHtml();
    }
}