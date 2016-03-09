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


class zilink_cohorts_settings_form extends moodleform {

    public function definition() {
        global $CFG, $DB;  

        if (isset($this->_customdata['cohorts_cohort_auto_create'])) {
            $cohort_auto_create = $this->_customdata['cohorts_cohort_auto_create'];
        } else {
            $cohort_auto_create = 1;
        }  


        $options = array();
        $options[0] = get_string('disable');
        $options[1] = get_string('enable');   

        $strrequired = get_string('required');
        $mform = & $this->_form;


        //COHORT

        $mform->addElement('header', 'moodle', get_string('cohorts_cohort_creation', 'local_zilink'));
        $select = $mform->addElement('select', 'cohorts_cohort_auto_create', get_string('cohorts_cohort_auto_create', 'local_zilink'), $options);
        $select->setSelected($cohort_auto_create);
        
        $mform->addHelpButton('cohorts_cohort_auto_create', 'cohorts_cohort_auto_create', 'local_zilink');

        $this->add_action_buttons(false, get_string('savechanges'));
    }

    function display()
    {
        return $this->_form->toHtml();
    }

}

