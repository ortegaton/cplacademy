<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class zilink_report_writer_view_report_form extends moodleform {
    function definition() {

        global $CFG,$USER, $DB;
 
        $mform =& $this->_form;
        
        $display = FALSE;
        
        $mform->addElement('hidden', 'rid',$this->_customdata['rid']);
        $mform->addElement('hidden', 'cid',$this->_customdata['cid']);
        $mform->addElement('hidden', 'uid',$this->_customdata['uid']);
        $mform->addElement('hidden', 'cohortid',$this->_customdata['cohortid']);
        $mform->addElement('hidden', 'action','writereport');
        $mform->setType('rid',PARAM_INT);
        $mform->setType('uid',PARAM_INT);
        $mform->setType('cid',PARAM_INT);
        $mform->setType('cohortid',PARAM_INT);
        $mform->setType('action',PARAM_RAW);
        
        $reportingsessions=array();
        $gradesets=array();
        $pupildata=array();

        $report = $DB->get_record('zilink_report_writer_reports',array('id' => $this->_customdata['rid']));
        $pupil = $DB->get_record('user',array('id' => $this->_customdata['uid']));

        require_once($CFG->dirroot.'/blocks/zilink/plugins/picture/class.php');
        
        $student = $DB->get_record('user',array('id' => $this->_customdata['uid']));
        
        $school_picture = new picture();
        $school_picture->Display($pupil->idnumber);
        
        
        $coloums = 2;
        for ( $i=1; $i <= 6; $i++) 
        {
            if($this->_customdata['components'][$i]['type'] == 'list')
            {
                $coloums = 4;
            }
        }
        $rows = 6;
        
        for ( $i=1; $i <= 6; $i++) 
        {
            //if($this->_customdata['components'][$i]['type'] == 'editor')
            //{
            //    $rows++;
            //}
        }
        
        $mform->addElement('html', '<table class="generaltable boxaligncenter" width="900px">
                            <thead>
                            <tr>
                            <th class="header c0" style="" scope="col" colspan=7>Report for '.$pupil->firstname.' '.$pupil->lastname.'</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="r0">
                            <td class="cell c0" rowspan="'.($rows).'" style=" text-align:center; width:175px;">
                            '.$school_picture->display($pupil->idnumber).'</td>');
                
        $mform->addElement('html','<td class="cell c0 " rowspan="'.($rows).'" style="vertical-align: middle; text-align:center; width:175px;">'.
                            get_string('guardian_view_default_student_dob','local_zilink').': '.date('d/m/Y',strtotime($this->_customdata['details']->extended_details->PersonalInformation->Demographics->BirthDate)).'<br />'.'<br />'.
                            get_string('guardian_view_default_student_gender','local_zilink').': '.$this->_customdata['details']->extended_details->PersonalInformation->Demographics->Gender.'<br />'.'<br />'.
                            get_string('guardian_view_default_student_house','local_zilink').': '. (($this->_customdata['details']->details->person->schoolregistration->attribute('house') == "UNKNOWN") ? '-' : $this->_customdata['details']->details->person->schoolregistration->attribute('house')).'<br />'.'<br />'.
                            get_string('guardian_view_default_student_year_group','local_zilink') .': '.(($this->_customdata['details']->details->person->schoolregistration->attribute('year')  == "UNKNOWN") ? '-' : $this->_customdata['details']->details->person->schoolregistration->attribute('year')).'<br />'.'<br />'.
                            get_string('guardian_view_default_student_registration_group','local_zilink').': '.(($this->_customdata['details']->details->person->schoolregistration->attribute('registration')  == "UNKNOWN") ? '-' : $this->_customdata['details']->details->person->schoolregistration->attribute('registration')).'</td>');                          
                   
            
        //$mform->addElement('html','<tr>');   
        
        $count = 0;                 
        for ( $i=1; $i <= 6; $i++) 
        {
            
            if($this->_customdata['components'][$i]['type'] == 'list')
            {
                if($i > 2)
                {
                    $mform->addElement('html', '<tr>');
                }
                
                
                $mform->addElement('html','<td class="cell c1 " style="max-width:auto;">');
                $mform->addElement('html',$this->_customdata['components'][$i]['label']);
                $mform->addElement('html', '</td><td class="cell c1 lastcol">');
                $mform->addElement('html', $this->_customdata['reportdata']['component'.$i]);
                $mform->addElement('html', '</td>');
                $mform->addElement('html', '</tr>');
                $count++;
            }
        }

        if($count > 0){
            for ( $i=$count; $i < 6; $i++) 
            {
                $mform->addElement('html', '<tr>');
                $mform->addElement('html','<td class="cell c1 " style="max-width:auto;"></td>');
                $mform->addElement('html','<td class="cell c1 " style="max-width:auto;"></td>');
                $mform->addElement('html', '</tr>');
            }
        }
        
        $mform->addElement('html', '</tr>');
        

        for ( $i=1; $i <= 6; $i++) 
        {
            if($this->_customdata['components'][$i]['type'] == 'editor')
            {
        
                $mform->addElement('html', '
                                    <tr class="r0 lastrow">
                                    <td class="cell c1 lastcol"  style="">');
                $mform->addElement('html',$this->_customdata['components'][$i]['label']);
                $mform->addElement('html', '</td><td class="cell c1 lastcol" colspan="3">');
                
                $mform->addElement('html', $this->_customdata['reportdata']['component'.$i]);
                $mform->setType('components'.$i,PARAM_RAW);
                $mform->addElement('html', '</td></tr>');                
            }
        }    
        
        /*        
        $mform->addElement('html', '<tr class="r0 lastrow">');
        $mform->addElement('html', '<td class="cell c0" rowspan="1" style="text-align:center; vertical-align: middle;">'.get_string('report_writer_signoff','local_zilink').'</td>');
                
               
        
        //$mform->addElement('header', $signoff.'title',get_string($signoff,'block_zilink'));
        foreach ( $this->_customdata['signoffs'] as $signoff => $settings) 
        {
                $mform->addElement('html','<td class="cell c1 " style="">');
                $mform->addElement('html',get_string($signoff,'local_zilink'));
                $mform->addElement('advcheckbox',$signoff,'', '', array('group' => 1));
                $mform->setDefault($signoff,$settings['value']);
                $mform->disabledIf($signoff,$signoff.'permission','eq',0);
                $mform->addElement('html', '</td>');
            }    
        
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