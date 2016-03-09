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
 * Defines the settings for the ZiLink local
 *
 * @package     enrol_zilink_cohort
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require('../../config.php');

$filter = required_param('filter', PARAM_TEXT);
$type = required_param('type', PARAM_TEXT);
$courseid = required_param('courseid', PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);

if (isloggedin() && has_capability('moodle/site:config', $context) && confirm_sesskey()) {

    $output = array();

    if($type == 'cohort')
    {
        $existing = array();
        $enrolinstances = enrol_get_instances($courseid, false);
        foreach ($enrolinstances as $courseenrolinstance) {
            if ($courseenrolinstance->enrol == "zilink_cohort") {
                $existing[] = $courseenrolinstance->customint1;
            }
        }

        if (!empty($filter)) {
    
            $params = array("%$filter%");
            $select = 'SELECT id, idnumber, name ';
            $from = 'FROM {cohort}  ';
            $where = "WHERE name LIKE ? and component = 'enrol_zilink' ";
            $order = 'ORDER BY name ';

            if ($cohorts = $DB->get_records_sql($select.$from.$where.$order, $params)) {
                foreach($cohorts as $cohort)
                {
                    if(strlen($cohort->idnumber) == 32 && !in_array($cohort->id,$existing))
                    {
                        $output[] = $cohort;
                    }
                }
            }
        }
        else {
            $params = array(null);
            $select = 'SELECT id, idnumber, name ';
            $from = 'FROM {cohort}  ';
            $where = "WHERE component = 'enrol_zilink' ";
            $order = 'ORDER BY name ';

            if ($cohorts = $DB->get_records_sql($select.$from.$where.$order, $params)) {
                foreach($cohorts as $cohort)
                {
                    if(strlen($cohort->idnumber) == 32 && !in_array($cohort->id,$existing))
                    {
                        $output[] = $cohort;
                    }
                    
                }
            }
        }
        echo json_encode($output);
    }
    else
    {
        header('HTTP/1.1 404 Not Found');
    }

} else {
    header('HTTP/1.1 401 Not Authorised');
}