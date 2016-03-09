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
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

    'zilink_get_users'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_get_users',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Get Users',
        'capabilities' => 'moodle/user:viewalldetails',
        'type'         => 'write',
    ),
    'zilink_enrol_course_teacher'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_enrol_course_teacher',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Enrol Course Teacher',
        'capabilities' => 'enrol/zilink:enrol',
        'type'         => 'write',
    ),
    'zilink_unenrol_course_teacher'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_unenrol_course_teacher',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Unenrol Course Teacher',
        'capabilities' => 'enrol/zilink:enrol',
        'type'         => 'write',
    ),
    'zilink_get_courses'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_get_courses',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Get Courses',
        'capabilities' => 'moodle/course:view,moodle/course:update,moodle/course:viewhiddencourses',
        'type'         => 'write',
    ),
    'zilink_create_courses'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_create_courses',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Create Courses',
        'capabilities' => 'moodle/course:create,'.
                            'moodle/course:view,'.
                            'moodle/course:update,'.
                            'moodle/course:viewhiddencourses',
        'type'         => 'write',
    ),
    'zilink_sync_course_cohorts'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_sync_course_cohorts',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Sync Cohorts to Courses',
        'capabilities' => 'moodle/course:create,'.
                            'moodle/course:view,'.
                            'moodle/course:update,'.
                            'moodle/course:viewhiddencourses,'.
                            'moodle/course:enrolconfig',
        'type'         => 'write',
    ),
    'zilink_get_categories'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_get_categories',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Get Course Categories',
        'capabilities' => 'moodle/course:view,moodle/course:update,moodle/course:viewhiddencourses',
        'type'         => 'write',
    ),
    'zilink_create_categories'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_create_categories',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Create Categories',
        'capabilities' => 'moodle/category:manage,moodle/category:viewhiddencategories',
        'type'         => 'write',
    ),
    'zilink_get_cohorts'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_get_cohorts',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Get Cohorts',
        'capabilities' => 'moodle/cohort:manage',
        'type'         => 'write',
    ),
    'zilink_create_cohorts'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_create_cohorts',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Create Cohorts',
        'capabilities' => 'moodle/cohort:manage,moodle/cohort:assign',
        'type'         => 'write',
    ),
    'zilink_delete_cohorts'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_delete_cohorts',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Delete Cohorts',
        'capabilities' => 'moodle/cohort:manage',
        'type'         => 'write',
    ),
    'zilink_sort_categories'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_sort_categories',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Sort Categories',
        'capabilities' => 'moodle/category:manage',
        'type'         => 'write',
    ),
    'zilink_enrol_cohort_student'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_enrol_cohort_student',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Enrol Cohort Student',
        'capabilities' => 'moodle/cohort:manage',
        'type'         => 'write',
    ),
    'zilink_unenrol_cohort_student'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_unenrol_cohort_student',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Unenrol Cohort Student',
        'capabilities' => 'moodle/cohort:manage',
        'type'         => 'write',
    ),
    'zilink_get_cohort_enrolments'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_get_cohort_enrolments',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Get Cohort Enrolments',
        'capabilities' => 'moodle/cohort:manage',
        'type'         => 'write',
    ),
    'zilink_get_staff_cohort_enrolments'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_get_staff_cohort_enrolments',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Get Staff Cohort Enrolments',
        'capabilities' => 'moodle/cohort:manage',
        'type'         => 'write',
    ),
    'zilink_get_teacher_enrolments'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_get_teacher_enrolments',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Get Teacher Course Enrolments',
        'capabilities' => '',
        'type'         => 'write',
    ),
    'zilink_enrol_cohort_staff'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_enrol_cohort_staff',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Enrol Cohort Satff',
        'capabilities' => 'moodle/cohort:manage',
        'type'         => 'write',
    ),
    'zilink_unenrol_cohort_staff'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_unenrol_cohort_staff',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Unenrol Cohort Staff',
        'capabilities' => 'moodle/cohort:manage',
        'type'         => 'write',
    ),
    'zilink_set_user_data'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_set_user_data',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Set User Data',
        'capabilities' => '',
        'type'         => 'write',
    ),
    'zilink_set_global_data'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_set_global_data',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Set Global Data',
        'capabilities' => '',
        'type'         => 'write',
    ),
    'zilink_get_versions'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_get_versions',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Get Versions',
        'capabilities' => '',
        'type'         => 'write',
    ),
    'zilink_get_student_guardian_links'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_get_student_guardian_links',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Get Student Guardian Links',
        'capabilities' => '',
        'type'         => 'write',
    ),
    'zilink_remove_student_guardian_links'  => array(
        'classname'    => 'local_zilink_external',
        'methodname'   => 'zilink_remove_student_guardian_links',
        'classpath'    => 'local/zilink/external.php',
        'description'  => 'ZiLink Remove Student Guardian Links',
        'capabilities' => '',
        'type'         => 'write',
    ),
);