<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');

class zilink_student_reporting_edit_template_form extends moodleform {
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
        
        $mform->addElement('hidden', 'action','edit');
        $mform->setType('action',PARAM_RAW);
        
        $template = $DB->get_record('zilink_report_writer_tmplts',array('id' => $this->_customdata['rid']));

        $mform->addElement('html', '<table class="generaltable boxaligncenter" width="600px">
                                            <thead>
                                            <tr>
                                            <th class="header c0" style="" scope="col" colspan=2>Edit Template</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr class="r0">
                                            <td class="cell c0" style="">'.get_string('report_writer_edit_template_name','local_zilink').'</td>
                                            <td class="cell c1 lastcol" style="">');
                
                $attributes = array('style' => 'width: 100%');
                $mform->addElement('static', 'name','',$DB->get_record('course_categories',array('id' => $template->subjectid))->name);
                
                $mform->addElement('html', '</td>
                                            </tr>
                                            <tr class="r1">
                                            <td class="cell c0" style="">'.get_string('report_writer_edit_template_year','local_zilink').'</td><td class="cell c1 lastcol" style="">');

                $mform->addElement('static', 'year','',$DB->get_record('course_categories',array('id' => $template->yearid))->name);
                
                $mform->addElement('html', '</td>
                                            </tr>
                                            <tr class="r1">
                                            <td class="cell c0" style="">'.get_string('report_writer_edit_template_cohort','local_zilink').'</td><td class="cell c1 lastcol" style="">');
                
                $mform->addElement('static', 'cohort','',$DB->get_record('cohort',array('id' => $template->cohortid))->name);
                
                $mform->addElement('html', '</td>
                                            </tr>
                                            <tr class="r1">
                                            <td class="cell c0" style="">'.get_string('report_writer_edit_aspect_mapping','local_zilink').'</td><td class="cell c1 lastcol" style="">');
                
                
                $components = array_merge(array( 'NR'=>'Not Required'),$this->_customdata['components']);
                
                $mform->addElement('select', 'component1', '1', $components);
                $mform->addElement('select', 'component2', '2', $components);
                $mform->addElement('select', 'component3', '3', $components);
                $mform->addElement('select', 'component4', '4', $components);
                $mform->addElement('select', 'component5', '5', $components);
                $mform->addElement('select', 'component6', '6', $components);
                
                (!empty($template->component1)) ?  $mform->setDefault('component1',$template->component1) : $mform->setDefault('component1','NR');
                (!empty($template->component2)) ?  $mform->setDefault('component2',$template->component2) : $mform->setDefault('component2','NR');
                (!empty($template->component3)) ?  $mform->setDefault('component3',$template->component3) : $mform->setDefault('component3','NR');
                (!empty($template->component4)) ?  $mform->setDefault('component4',$template->component4) : $mform->setDefault('component4','NR');
                (!empty($template->component5)) ?  $mform->setDefault('component5',$template->component5) : $mform->setDefault('component5','NR');
                (!empty($template->component6)) ?  $mform->setDefault('component6',$template->component6) : $mform->setDefault('component6','NR');
                
                                
                $mform->addElement('html', '</td>
                                            </tr>');
                
                $mform->addElement('html', '</tbody>    
                                            </table>'); 
                
                $buttonarray=array();
                $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
                $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
                $mform->closeHeaderBefore('buttonar'); 
                
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