<?php

require_once($CFG->libdir.'/formslib.php');

class zilink_student_reporting_create_template_form extends moodleform {
    function definition() {

        global $CFG,$DB;
 
        $mform =& $this->_form;
        
        $mform->addElement('hidden', 'cid',$this->_customdata['cid']);
        $mform->setType('cid',PARAM_INT);
        $mform->addElement('hidden', 'tid',$this->_customdata['tid']);
        $mform->setType('tid',PARAM_INT); 

        $mform->addElement('html', '<table class="generaltable boxaligncenter" width="60%">
                                        <thead>
                                            <tr>
                                                <th class="header c0" style="" scope="col">Subject</th>
                                                <th class="header c0" style="" scope="col">7</th>
                                                <th class="header c0" style="" scope="col">8</th>
                                                <th class="header c0" style="" scope="col">9</th>
                                                <th class="header c0" style="" scope="col">10</th>
                                                <th class="header c0" style="" scope="col">11</th>
                                                <th class="header c0" style="" scope="col">12</th>
                                                <th class="header c0" style="" scope="col">13</th>
                                            </tr>
                                        </thead>
                                    <tbody>');
        
        
        $existingTemplates = $DB->get_records('zilink_report_writer_tmplts');

        $cat = coursecat::get($CFG->zilink_category_root);
        $categories = $cat->get_children(array( 'sort' => array('name' => 1)));
        
        $count = 0;
        foreach($categories as $category)
        {
            
            if(in_array($category->name, $this->_customdata['allowed_subjects']))
            {
                $row = array();
                $row[] = $category->name; 
                
                $years = $DB->get_records('course_categories', array('parent' => $category->id));
                
                if(count($years) == 0)
                {
                    continue;
                }
                
                $mform->addElement('html', '<tr class="r0">');
                $mform->addElement('html', '<td class="cell c0" style="">'.$category->name.'</td>');
                
                $subjectYears = array();
                foreach($years as $year)
                {
                    $subjectYears[] = $year;
                }
                
                $years = array('7','8','9','10','11','12','13');
                $prevYear = '';
                
                foreach($years as $year)
                {
                    
                    $found = false;
                    
                    if(empty($year))
                    {
                       $prevYear = $year;
                    }
                    
                    if($prevYear <> $year)
                    {
                        
                    }
                    
                    foreach($subjectYears as $subjectYear)
                    {
                        if(strstr($subjectYear->name, $year) !== false)
                        {
                            $found = true;
                            break;
                        }
                    }
                    
                    if(!$found)
                    {
                        $mform->addElement('html', '<td class="cell c0" style="text-align: center; background-color: #DCDCDC;"></td>');
                        continue;
                    }
                    $found = false;
                    
                    if(count($existingTemplates > 0))
                    {
                        foreach($existingTemplates as $existingTemplate)
                        {
                            if($existingTemplate->subjectid == $category->id && $existingTemplate->yearid == $subjectYear->id)
                            {
                                $found = true;
                                break;
                            }
                        }
                    }
                    if($found)
                    {
                        $mform->addElement('html', '<td class="cell c0" style="text-align: center; background-color: lightgreen;"></td>');
                    }
                    else 
                    {
                        $mform->addElement('html', '<td class="cell c0" style="text-align: center;">');
                        $select = $mform->addElement('checkbox', 'report_writer_template['.$category->id.']['.$subjectYear->id.']');
                        $mform->addElement('html', '</td>');
                    }
                    
                      
                }
                $mform->addElement('html', '</tr>');
            }
            
        }
        $mform->addElement('html', '</tbody></table>');
        
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('report_writer_button_create_templates','local_zilink'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
        
        $mform->disable_form_change_checker();
        
    }
    
    function display()
    {
        return html_writer::tag('div', $this->_form->toHtml(), array('id' => 'zilinkreportlist', 'name' => 'zilinkreportlist'));
       
        return $this->_form->toHtml();
    }
}