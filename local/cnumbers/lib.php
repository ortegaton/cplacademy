<?php 
function local_cnumbers_cron(){
	mtrace('Generating course numbers ..........................');
	global $CFG, $DB;
	
	$courses = $DB->get_records_sql('SELECT DISTINCT courseid, coursetype, number, month, year FROM {course_numbers}');
	if(!empty($courses)){
		foreach($courses as $course){
			$record = $DB->get_record_sql('SELECT max(number) as number FROM {course_numbers} WHERE coursetype = :coursetype LIMIT 0,1', array('coursetype'=>$course->coursetype));
			if(empty($record->number)){
				if ($course->coursetype == 'online')
					$latestnumber = 5000;
				else
					$latestnumber = 10000;
			} else {
				$latestnumber = $record->number;
			}
			// check if number already added
			$exists = $DB->get_record('course_numbers', array('courseid'=>$course->courseid, 'month'=>date('n'), 'year'=>date('Y')));

			if(empty($exists)){
				$record = new stdClass();
				$record->courseid = $course->courseid;
				$record->coursetype = $course->coursetype;
				$record->number = $latestnumber + 1; // increment by one
				$record->month = date('n');
				$record->year = date('Y');
				$lastinsertid = $DB->insert_record('course_numbers', $record, false);
				mtrace("Inserted new course number for course: ".$course->courseid." and number: ". $latestnumber + 1 ." ..........................");
			}
		}
	} else {
		mtrace('No existing course numbers exists ..........................');
		$courses = $DB->get_records_sql('SELECT * FROM {course} WHERE id<>1');
		
		$onlinenumber = 5000;
		$offlinenumber = 10000;
		
		foreach($courses as $course){
			if($course->coursetype == 'online'){
				$number = $onlinenumber;
				++$onlinenumber;
			}
			elseif($course->coursetype == 'offline'){
				$number = $offlinenumber;
				++$offlinenumber;
			}
			else
				$number = '0';
				
			$exists = $DB->get_record('course_numbers', array('courseid'=>$course->id, 'month'=>date('n'), 'year'=>date('Y')));
			if(empty($exists)){
				$record = new stdClass();
				$record->courseid = $course->id;
				$record->coursetype = $course->coursetype;
				$record->number = $number;
				$record->month = date('n');
				$record->year = date('Y');
				$lastinsertid = $DB->insert_record('course_numbers', $record, false);
				mtrace("Inserted new course number for course: ".$course->id." and number: ". $number ." ..........................");
			}
		}
	}
}
?>