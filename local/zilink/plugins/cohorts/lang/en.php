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

/*
==============================================
    Moodle Required Plugin Text
==============================================
*/

/* 
=============================================
    Moodle Permission Text
=============================================
*/

/*
==============================================
    ZiLink Block Text
==============================================
*/

$string['cohorts'] = 'Cohorts';

$string['cohorts_view_title'] = 'Cohort View';
$string['cohorts_view_title_desc'] = 'This feature allows you to search for and select a ZiLink managed cohort to which users are currently members.';

$string['cohorts_page_title'] = $string['zilink'] .' ' . $string['cohorts'];

$string['cohorts_auto_create_title'] = 'ZiLink Cohort Creation';
$string['cohorts_auto_create_title_desc'] = 'Select Enable to configure ZiLink to automatically create Cohorts based on the information within your school management information system.';

$string['cohorts_cohort_creation'] = 'Cohort Creation';
$string['cohorts_cohort_delete']   = 'Cohorts Without Members';

$string['cohorts_cohort_auto_create'] = 'Manage Cohorts';
$string['cohorts_cohort_auto_create_help'] = 'TODO';

$string['cohorts_delete_title'] = 'ZiLink Cohort Delete';
$string['cohorts_delete_title_desc'] = 'TODO';

$string['cohorts_settings'] = $string['cohorts'];

$string['cohorts_create'] = 'Auto Create';
$string['cohorts_view'] = 'View';
$string['cohorts_delete'] = 'Delete';


$string['cohorts_support_desc'] = 'More information about ZiLink Cohorts is available on our ';

$string['cohorts_enrolment'] = 'Enrolment'; 
$string['cohorts_enrolment_title'] = 'Cohort Enrolment';  
$string['cohorts_enrolment_title_desc'] = 'This report lists all the courses that has a ZiLink cohort enroled.'; 