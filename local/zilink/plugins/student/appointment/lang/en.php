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
 * Defines the class for the ZiLink block sub plugin student_appointment
 *
 * @package     student_appointment
 * @subpackage    timetable_week
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Plugin has been derived from the following plugin
 /**
 * English Language Strings for course appointments block
 *
 * @package block_course_appointments
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2011 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 
 
 defined('MOODLE_INTERNAL') || die();


$string['student_appointment'] = 'Student Appointments';
$string['zilink:student_appointment_addinstance'] = 'Student Appointments - Add Instance';

$string['student_appointment_book'] = 'Book';
$string['zilink:student_appointment_book'] = 'Can book course appointments with other users';
$string['zilink:student_appointment_bookable'] = 'User can have a course appointment booked with them';
$string['student_appointment_datetime'] = 'Date/Time';
$string['student_appointment_entrydescription'] = 'Meeting between {$a->student} and {$a->teacher}';
$string['student_appointment_entryname'] = 'Meeting with {$a}';
$string['student_appointment_invaliddate'] = 'The date and time selected was invalid';
$string['student_appointment_nostudent'] = 'No Student was selected';
$string['student_appointment_nodate'] = 'No Date was selected';
$string['student_appointment_notnotified'] = 'The student could not be notified';
$string['student_appointment_notifystudent'] = 'Notify Student?';
$string['student_appointment_notifysubject'] = 'Appointment to see {$a}';
$string['student_appointment_notifytext'] = 'Hi {$a->student}
{$a->teacher} has booked an appointment to see you on {$a->date} at {$a->time}.
Please let {$a->teacher} know if you are unable to attend.';
$string['notifysms'] = '{$a->teacher} has booked an appointment to see you on {$a->date} at
{$a->time}. Please let them know if you are unable to attend.';
$string['student_appointment_remindsms'] = 'Don\'t forget your appointment with {$a->name} at {$a->time} today.';
$string['student_appointment_pastdate'] = 'The time and date selected is in the past';
$string['student_appointment_studentdoesntexist'] = 'The selected student doesn\'t exist';
$string['student_appointment_student'] = 'Student';

$string['student_appointment_booking_form'] = 'Booking Form';
$string['student_appointment_booking_saved'] = 'Your booking has been saved';
