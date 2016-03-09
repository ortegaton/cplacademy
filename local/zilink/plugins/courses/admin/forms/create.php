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
 * Defines the capabilities for the ZiLink block
 *
 * @package     local_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');


class zilink_courses_settings_form extends moodleform {

    public function definition() {
        global $CFG, $DB;

        if (isset($this->_customdata['courses_course_sorting'])) {
            $course_sorting = $this->_customdata['courses_course_sorting'];
        } else {
            $course_sorting = 0;
        }    
        /*
        if (isset($this->_customdata['courses_course_auto_create_classes'])) {
            $cohort_auto_create_classes = $this->_customdata['courses_course_auto_create_classes'];
        } else {
            $cohort_auto_create_classes = 0;
        }  
        
        if (isset($this->_customdata['courses_course_auto_create_years'])) {
            $cohort_auto_create_years = $this->_customdata['courses_course_auto_create_years'];
        } else {
            $cohort_auto_create_years = 0;
        }  
        */
        if (isset($this->_customdata['courses_course_template'])) {
            $course_template = $this->_customdata['courses_course_template'];
        } else {
            $course_template = 0;
        }  


        $options = array();
        $options[0] = get_string('disable');
        $options[1] = get_string('enable');   

        $strrequired = get_string('required');
        $mform = & $this->_form;
        
        //COURSE

        $mform->addElement('header', 'moodle', get_string('courses_creation_title', 'local_zilink'));

        $where = $DB->sql_like('fullname', ':var', false, false, false);
        $params=array();
        $params['var'] = '%template%';
        $templates = $DB->get_records_sql("SELECT id, fullname FROM {course} WHERE $where", $params);
        
        $list = array();

        if($templates)
        {
            foreach ($templates as $template)
                $list[$template->id] = $template->fullname;
        }
        else
            $list[0] = get_string('courses_no_templates', 'local_zilink');
  
        $select = $mform->addElement('select', 'courses_course_template', get_string('courses_course_template', 'local_zilink'), $list);
        $select->setSelected($course_template);
        $mform->addHelpButton('courses_course_template', 'courses_course_template', 'local_zilink');

        $select = $mform->addElement('advcheckbox', 'courses_course_auto_create_years', get_string('courses_course_create_years', 'local_zilink'), get_string('courses_course_create_years_desc', 'local_zilink'), array('group' => 1), array(0, 1));
        $select = $mform->addElement('advcheckbox', 'courses_course_auto_create_classes', get_string('courses_course_create_classes', 'local_zilink'), get_string('courses_course_create_classes_desc', 'local_zilink'), array('group' => 1), array(0, 1));
        //$select->setSelected($cohort_auto_create_classes);
        
        //$select->setSelected($cohort_auto_create_years);
        //$this->add_checkbox_controller(1);
        
        $select = $mform->addElement('select', 'courses_course_sorting', get_string('courses_course_sorting', 'local_zilink'), $options);
        $select->setSelected($course_sorting);

        $mform->addHelpButton('courses_course_sorting', 'courses_course_sorting', 'local_zilink');

        $this->add_action_buttons(false, get_string('savechanges'));
    }

    function display()
    {
        return $this->_form->toHtml();
    }

}

