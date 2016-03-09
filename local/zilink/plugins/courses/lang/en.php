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

$string['courses'] = 'Courses';
$string['courses_page_title'] = $string['zilink']  .' '.$string['courses'];
$string['courses_creation_title'] = 'Course Auto Creation';
$string['courses_creation_title_desc'] = 'ZiLink for Moodle will use the configuration settings on this page to automatically create courses using the information from your school management information system.';

$string['courses_settings'] = $string['courses'];


$string['courses_create'] = 'Auto Create';
$string['courses_view'] = 'View';
$string['courses_delete'] = 'Delete';
$string['courses_reset'] = 'Reset';


$string['courses_course_creation'] = 'Course Creation';
$string['courses_course_template'] = 'Course Template';
$string['courses_course_template_help'] = 'Select a course template for ZiLink for Moodle to use to create courses. To create a template just create a new course with the features you need such as topics format with five topics, and save the course with a distinctive name e.g. ZiLink Course Template, in the Miscellaneous category of your Moodle.';
$string['courses_no_templates'] = 'No Templates Available';
//$string['courses_no_templates_help'] = 'TODO';
$string['courses_course_sorting'] = 'Sort Courses';
$string['courses_course_sorting_help'] = 'Set to Enable if you want ZiLink for Moodle to sort the ZiLink for Moodle created courses into alphabetical order.';

$string['courses_support_desc'] = 'More information about ZiLink Courses is avaliable on our ';

$string['courses_course_create_years'] = 'Create Year Courses';
$string['courses_course_create_years_desc'] = 'Create courses based on Year & Subject (eg Year 7 Art)';

$string['courses_course_create_classes'] = 'Create Class Courses';
$string['courses_course_create_classes_desc'] = 'Create courses based on Class Code (eg 7A/Ar1) in a classes category ';
