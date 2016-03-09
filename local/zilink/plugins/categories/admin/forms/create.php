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


class zilink_categories_settings_form extends moodleform {

    public function definition() {
        global $CFG, $DB;

        if (isset($this->_customdata['categories_sorting'])) {
            $category_sorting = $this->_customdata['categories_sorting'];
        } else {
            $category_sorting = 0;
        }
        
        if (isset($this->_customdata['categories_structure'])) {
            $categories_structure = $this->_customdata['categories_structure'];
        } else {
            $categories_structure = 0;
        } 

        
        if (isset($this->_customdata['categories_root_category'])) {
            $root_category = $this->_customdata['categories_root_category'];
        } else {
            $root_category = 0;
        }  

        $options = array();
        $options[0] = get_string('disable');
        $options[1] = get_string('enable');   

        $strrequired = get_string('required');
        $mform = & $this->_form;

        //CATEGORY
        
        $mform->addElement('header', 'moodle', get_string('categories_category_creation', 'local_zilink'));


        $sql = 'SELECT id, name FROM {course_categories} WHERE parent = 0 ORDER BY name ASC';
        $categories = $DB->get_records_sql($sql);
        
        $list = array();
        
        $list[0] = get_string('none');
        if($categories)
        {
            foreach ($categories as $category)
                $list[$category->id] = $category->name;
        }
        

        $select = $mform->addElement('select', 'categories_root_category', get_string('categories_root_category', 'local_zilink'), $list);
        $select->setSelected($root_category);
        $mform->addHelpButton('categories_root_category', 'categories_root_category', 'local_zilink');

        $list = array();
        
        $list = array( 
            '0' => get_string('categories_stucture_none','local_zilink'),
            '1' => get_string('categories_stucture_subject_only','local_zilink'),
            '2' => get_string('categories_stucture_subject_and_year','local_zilink'),
        );
        
        $select = $mform->addElement('select', 'categories_structure', get_string('categories_category_structure', 'local_zilink'), $list);
        $select->setSelected($categories_structure);
        
        $select = $mform->addElement('select', 'categories_category_sorting', get_string('categories_category_sorting', 'local_zilink'), $options);
        $select->setSelected($category_sorting);

        $mform->addHelpButton('categories_category_sorting', 'categories_category_sorting', 'local_zilink');

        $this->add_action_buttons(false, get_string('savechanges'));
    }

    function display()
    {
        return $this->_form->toHtml();
    }

}

