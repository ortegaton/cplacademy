<?php

require_once($CFG->libdir.'/formslib.php');

class zilink_student_reporting_create_report_from_template_form extends moodleform {
    function definition() {

        global $CFG,$DB;
 
        $mform =& $this->_form;
        
        $mform->addElement('hidden', 'cid',$this->_customdata['cid']);
        $mform->setType('cid',PARAM_INT);
        $mform->addElement('hidden', 'tid',$this->_customdata['tid']);
        $mform->setType('tid',PARAM_INT); 

        $mform->addElement('html', '<table class="generaltable boxaligncenter" width="auto">
                                    <thead>
                                    <tr>
                                    <th class="header c0" style="" scope="col">Select Assessment Session</th>
                                    </tr>
                                    </thead>
                                    <tbody><tr><td style="text-align: center">');
                                    
        $mform->addElement('select', 'assessmentsessionrefid','',$this->_customdata['sessions']);
        $mform->addHelpButton('assessmentsessionrefid', 'report_writer_no_sessions', 'local_zilink');

        $mform->addElement('html', '</td></tr></tbody></table>');

        $mform->addElement('html', '<table class="generaltable boxaligncenter" width="60%">
                                    <thead>
                                    <tr>
                                    <th class="header c0" style="text-align:left" scope="col">Subject</th>
                                    <th class="header c1" style="" scope="col">Year</th>
                                    <th class="header c2" style="" scope="col">Cohort</th>
                                    <th class="header c3" style="" scope="col">Create Report for Session?</th>
                                    </tr>
                                    </thead>
                                    <tbody>');
        
        $templates = $DB->get_records('zilink_report_writer_tmplts');
        
        $previousYear = '';
        $count = 1;
        foreach($templates as $template)
        {
            
            if(empty($previousYear))
            {
                $previousYear = $template->yearid;
            }
            
            if($previousYear <> $template->yearid)
            {
                $mform->addElement('html', '<tr class="r0">');
                $mform->addElement('html', '<td class="cell c0"></td>');
                $mform->addElement('html', '<td class="cell c1" style=""></td>');
                $mform->addElement('html', '<td class="cell c2" style=""></td>');
                
                $mform->addElement('html', '<td class="cell c3" style="">');
                $this->add_checkbox_controller($count, get_string("selectall"), array('style' => 'font-weight: bold;'),0);
                $count++;
                $mform->addElement('html', '</td></tr>');
                $previousYear = $template->yearid;
            }
            $mform->addElement('html', '<tr class="r0">');
            $mform->addElement('html', '<td class="cell c0" style="">'.$DB->get_record('course_categories',array('id' => $template->subjectid))->name.'</td>');
            $mform->addElement('html', '<td class="cell c1" style="">'.$DB->get_record('course_categories',array('id' => $template->yearid))->name.'</td>');
            $mform->addElement('html', '<td class="cell c2" style="">'.$DB->get_record('cohort',array('id' => $template->cohortid))->name.'</td>');
            
            $mform->addElement('html', '<td class="cell c3" style="">');
            $mform->addElement('advcheckbox','template['.$template->id.']', null, null, array('group' => $count));
            //$mform->disabledIf('session', 'nosubmit_checkbox_controller'.$count, 'clicked');
            //$mform->disabledIf('assessmentsessionrefid', 'template['.$template->id.']', 'eq',1);
            //$mform->addElement('select', 'sessions['.$template->id.']','',$this->_customdata['sessions']);
            $mform->addElement('html', '</td></tr>');
        }
        
        if($templates)
        {
            $mform->addElement('html', '<tr class="r0">');
            $mform->addElement('html', '<td class="cell c0" style=""></td>');
            $mform->addElement('html', '<td class="cell c1" style=""></td>');
            $mform->addElement('html', '<td class="cell c2" style=""></td>');
            
            $mform->addElement('html', '<td class="cell c3" style="">');
            $this->add_checkbox_controller($count, get_string("selectall"), array('style' => 'font-weight: bold;'),0);
            $count++;
            $mform->addElement('html', '</td></tr>');
        }
    
        $mform->addElement('html', '</tbody></table>');
        
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('report_writer_button_create_reports','local_zilink'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
        
        $mform->disable_form_change_checker();
        
        
    }
    
    function display()
    {
        return html_writer::tag('div', $this->_form->toHtml(), array('id' => 'zilinkreportlist', 'name' => 'zilinkreportlist'));
    }
}