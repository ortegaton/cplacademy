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
 * Defines the settings for the ZiLink block
 *
 * @package     local_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//$ADMIN->add('zilink_reports_settings', new admin_category('zilink_reports_settings', get_string('reports_settings', 'local_zilink')));

//$ADMIN->add('zilink_reports_settings_cohort_enrolment', new admin_externalpage('zilink_reports_settings_cohort_enrolment', get_string('reports_cohort_enrolment', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/reports/admin/cohort_enrolment.php",'moodle/site:config'));
//$ADMIN->add('zilink_cohort_settings', new admin_externalpage('zilink_reports_settings_cohort_enrolment', get_string('reports_cohort_enrolment', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/reports/admin/cohort_enrolment.php",'moodle/site:config'));
$ADMIN->add('zilink_reports_settings', new admin_externalpage('zilink_reports_settings_account_matching', get_string('reports_account_matching', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/reports/admin/account_matching.php",'moodle/site:config'));
$ADMIN->add('zilink_reports_settings', new admin_externalpage('zilink_reports_settings_timetable_weeks', get_string('reports_timetable_weeks', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/reports/admin/timetable_weeks.php",'moodle/site:config'));