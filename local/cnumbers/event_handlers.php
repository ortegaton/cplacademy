<?php
// This file is part of the Local cnumber plugin
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
 * This plugin generates a new number for each course based on type of the course (online or F2F). 
 * for each month it generates a new number
 *
 * @package    local
 * @subpackage cnumbers
 * @copyright  2015 Jack Bradley jack.bradley@cartelsystems.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function save_number($course) {
    global $CFG, $DB;
	
	// check if number already added
	$exists = $DB->get_record('course_numbers', array('courseid'=>$course->id, 'month'=>date('n'), 'year'=>date('Y')));
	
	$latestnumber = get_latest_number($course->coursetype);
	
	if(empty($exists)){
		$record = new stdClass();
		$record->courseid = $course->id;
		$record->coursetype = $course->coursetype;
		$record->number = $latestnumber + 1; // increment by one
		$record->month = date('n');
		$record->year = date('Y');
		$lastinsertid = $DB->insert_record('course_numbers', $record, false);
	}

    //email_to_user($user, $sender, $message_user_subject, html_to_text($message_user), $message_user);
}

function get_latest_number($coursetype) {
    global $CFG, $DB;
	
	$record = $DB->get_record_sql('SELECT max(number) as number FROM {course_numbers} WHERE coursetype = :coursetype LIMIT 0,1', array('coursetype'=>$coursetype));
	if(empty($record->number)){
		if ($coursetype == 'online')
		    $latestnumber = 5000;
		elseif($coursetype == 'offline')
		    $latestnumber = 10000;
		else
		    $latestnumber = 0;
	} else {
		$latestnumber = $record->number;
	}
	return $latestnumber;
}