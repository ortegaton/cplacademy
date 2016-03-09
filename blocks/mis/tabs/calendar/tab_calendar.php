<?php

global $CFG;

require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/calendar/lib.php');	
	
class tab_calendar extends tab_base{

	var $calurl;

	function init($name){
		global $CFG;
		$this->setName($name);
		$this->calurl=$CFG->wwwroot.'/blocks/mis/index.php?tab=calendar&amp;userid='.$this->mdluser->id;
	}
	
	function getTitle(){
		$this->title = "Calendar";
		return $this->title;
	}
	
	/**
	 * Based on standard Moodle calendar with nasty hack to make it show another person's calendar
	 */
	function getContent(){
	
		global $CFG, $SESSION, $USER, $CALENDARDAYS, $DB, $PAGE, $OUTPUT, $origuser, $url;
	
		$content='';
	
		$courseid = optional_param('course', 1, PARAM_INT);
		$view = optional_param('view', 'upcoming', PARAM_ALPHA);
		$day  = optional_param('cal_d', 0, PARAM_INT);
		$mon  = optional_param('cal_m', 0, PARAM_INT);
		$yr   = optional_param('cal_y', 0, PARAM_INT);

		if(!$site = get_site()) {
			return ($content);
		}		
		
		// DIRTY HACK (The Moodle Devs will HATE this, and so do I!)
		// Get calendar for a specific user by overriding global $USER variable temporarilly
		$origuser=$USER;
		$USER=$this->mdluser;
		$USER->realuser=$USER; // let the calendar know that we are looking at someone elses calendar
		$uparams=array('tab'=>'calendar','userid'=>$this->mdluser->id);
		$url= new moodle_url('/blocks/mis/index.php',$uparams);
		$PAGE->set_pagelayout('standard');
		$PAGE->set_url($url);
		
		$now = usergetdate(time());
		$pagetitle = '';
		$strcalendar = get_string('calendar', 'calendar');
		$navlinks = array();
		$params=array('tab'=>'calendar','userid'=>$this->mdluser->id,'view'=>'upcoming','course'=>$courseid);
		$tcalurl=new moodle_url('/blocks/mis/index.php',$params);
		$navlinks[] = array('name' => $strcalendar,'link' =>calendar_get_link_href($tcalurl,$now['mday'], $now['mon'], $now['year']),'type' => 'misc');
		if(!checkdate($mon, $day, $yr)) {
			$day = intval($now['mday']);
			$mon = intval($now['mon']);
			$yr = intval($now['year']);
		}
		$time = make_timestamp($yr, $mon, $day);

		switch($view) {
			case 'day':
				$navlinks[] = array('name' => userdate($time, get_string('strftimedate')), 'link' => null, 'type' => 'misc');
				$pagetitle = get_string('dayview', 'calendar');
			break;
			case 'month':
				$navlinks[] = array('name' => userdate($time, get_string('strftimemonthyear')), 'link' => null, 'type' => 'misc');
				$pagetitle = get_string('detailedmonthview', 'calendar');
			break;
			case 'upcoming':
				$pagetitle = get_string('upcomingevents', 'calendar');
			break;
		}
		
		// If a course has been supplied in the URL, change the filters to show that one
		if (!empty($courseid)) {
			if ($course = $DB->get_record('course',array('id'=>$courseid))) {
				if ($course->id == SITEID) {
					// If coming from the home page, show all courses
					$SESSION->cal_courses_shown = calendar_get_default_courses(true);
				} else {
					// Otherwise show just this one
					$SESSION->cal_courses_shown = $course->id;
				}
			}
		} else {
			$course = null;
		}
	
		if (empty($USER->id) or (!isloggedin())) {
			$defaultcourses = calendar_get_default_courses();
		}
		if ($courseid != SITEID && !empty($courseid)) {
			$course = $DB->get_record('course', array('id' => $courseid));
			$courses = array($course->id => $course);
			$issite = false;
			navigation_node::override_active_url(new moodle_url('/course/view.php', array('id' => $course->id)));
		} else {
			$course = get_site();
			$courses = calendar_get_default_courses();
			$issite = true;
		}
		
		list($courses, $groups, $users) = calendar_set_filters($courses);

		$strcalendar = get_string('calendar', 'calendar');
		
		// Begin 960 wrapper
		$content .= html_writer::start_tag('div', array('class'=>'region-content','id'=>'block_mis_wrapper'));
		
		// START: Main column
		$content.= html_writer::start_tag('div', array('class'=>'path-calendar','style'=>'width:750px;float:left;'));
		$content.= html_writer::start_tag('div', array('class'=>'maincalendar'));
		
		switch($view) {
			case 'day':
				$content.=$this->calendar_show_day($day, $mon, $yr, $courses, $groups, $users, $courseid);
			break;
			case 'month':
				$content.=$this->calendar_show_month_detailed($mon, $yr, $courses, $groups, $users, $courseid);
			break;
			case 'upcoming':
				$content.=$this->calendar_show_upcoming_events($courses, $groups, $users, get_user_preferences('calendar_lookahead', CALENDAR_DEFAULT_UPCOMING_LOOKAHEAD), get_user_preferences('calendar_maxevents', CALENDAR_DEFAULT_UPCOMING_MAXEVENTS), $courseid);
			break;
		}
		
		$content .= html_writer::end_tag('div');
		$content .= html_writer::end_tag('div');
		// END: Main column
		
		// START: Last column (3-month display)
		list($prevmon, $prevyr) = calendar_sub_month($mon, $yr);
		list($nextmon, $nextyr) = calendar_add_month($mon, $yr);
		$getvars = 'id='.$courseid.'&amp;cal_d='.$day.'&amp;cal_m='.$mon.'&amp;cal_y='.$yr; // <-- for filtering
		$content .= html_writer::start_tag('div', array('class'=>'block-region','style'=>'float:left;width:180px;padding-left:20px;'));
		$content .= html_writer::start_tag('div', array('class'=>'block'));
		
		$content .= html_writer::start_tag('div', array('class'=>'header','style'=>'text-align:center;font-weight:bold;'));
		$content .= get_string('eventskey', 'calendar');
		$content .= html_writer::end_tag('div');
		$content .= html_writer::tag('div', $this->calendar_filter_controls($url), array('class'=>'calendar_filters filters'));
		$content .= html_writer::start_tag('div', array('class'=>'header','style'=>'text-align:center;font-weight:bold;'));
		$content .= get_string('monthlyview', 'calendar');
		$content .= html_writer::end_tag('div');
		$content .= html_writer::start_tag('div', array('class'=>'minicalendarblock'));
        $content .= $this->calendar_top_controls('display', array('id' => $courseid, 'm' => $prevmon, 'y' => $prevyr));
        $content .= calendar_get_mini($courses, $groups, $users, $prevmon, $prevyr);
		$content .= html_writer::end_tag('div');
        $content .= html_writer::start_tag('div', array('class'=>'minicalendarblock'));
        $content .= $this->calendar_top_controls('display', array('id' => $courseid, 'm' => $mon, 'y' => $yr));
        $content .= calendar_get_mini($courses, $groups, $users, $mon, $yr);
        $content .= html_writer::end_tag('div');
        $content .= html_writer::start_tag('div', array('class'=>'minicalendarblock'));
        $content .= $this->calendar_top_controls('display', array('id' => $courseid, 'm' => $nextmon, 'y' => $nextyr));
        $content .= calendar_get_mini($courses, $groups, $users, $nextmon, $nextyr);
        $content .= html_writer::end_tag('div');
		
		// Calendar Export...
		$content.= html_writer::start_tag('div', array('style'=>'text-align:center;clear:both;'));
		if ((!empty($USER->id))&&(!empty($USER->password))) {
			$authtoken = sha1($USER->username . $USER->password . $CFG->calendar_exportsalt);
			$usernameencoded = urlencode($USER->username);

			$content.= $OUTPUT->container_start('bottom');
			if (!empty($CFG->enablecalendarexport)) {
				$content.= $OUTPUT->single_button(new moodle_url('/calendar/export.php', array('course'=>$courseid)), get_string('exportcalendar', 'calendar'));
				if (isloggedin()) {
					$authtoken = sha1($USER->id . $USER->password . $CFG->calendar_exportsalt);
					$link = new moodle_url('/calendar/export_execute.php', array('preset_what'=>'all', 'preset_time'=>'recentupcoming', 'userid' => $USER->id, 'authtoken'=>$authtoken));
					$icon = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/ical'), 'height'=>'14', 'width'=>'36', 'alt'=>get_string('ical', 'calendar'), 'title'=>get_string('quickdownloadcalendar', 'calendar')));
					$content.= html_writer::tag('a', $icon, array('href'=>$link));
				}
			}	 
		}
		$content.= html_writer::end_tag('div');	
		$content.= $OUTPUT->container_end();
		$content.= html_writer::end_tag('div'); 
		$content.= html_writer::end_tag('div');
		$content.= html_writer::end_tag('div'); // end 960 wrapper
		
		// UNDO DIRTINESS - We've got the calendar html so now restore $USER
		$USER=$origuser;		
		return ($content);
	}
	
	/**
	 * Standard Moodle 'calendar_filter_controls' function, but with sesskey variable changed to $origuser's instead of $USER's
	 */
	function calendar_filter_controls(moodle_url $returnurl) {
      global $CFG, $USER, $OUTPUT, $origuser;
  
      $groupevents = true;
  
      $id = optional_param( 'id',0,PARAM_INT );
  
      $seturl = new moodle_url('/calendar/set.php', array('return' => base64_encode($returnurl->out(false)), 'sesskey'=>$origuser->sesskey));
  
      $content = '<table>';
      $content .= '<tr>';
  
      $seturl->param('var', 'showglobal');
      if (calendar_show_event_type(CALENDAR_EVENT_GLOBAL)) {
          $content .= '<td class="eventskey calendar_event_global" style="width: 11px;"><img src="'.$OUTPUT->pix_url('t/hide') . '" class="iconsmall" alt="'.get_string('hide').'" title="'.get_string('tt_hideglobal', 'calendar').'" style="cursor:pointer" onclick="location.href='."'".$seturl."'".'" /></td>';
          $content .= '<td><a href="'.$seturl.'" title="'.get_string('tt_hideglobal', 'calendar').'">'.get_string('global', 'calendar').'</a></td>'."\n";
      } else {
          $content .= '<td style="width: 11px;"><img src="'.$OUTPUT->pix_url('t/show') . '" class="iconsmall" alt="'.get_string('show').'" title="'.get_string('tt_showglobal', 'calendar').'" style="cursor:pointer" onclick="location.href='."'".$seturl."'".'" /></td>';
          $content .= '<td><a href="'.$seturl.'" title="'.get_string('tt_showglobal', 'calendar').'">'.get_string('global', 'calendar').'</a></td>'."\n";
      }
  
      $seturl->param('var', 'showcourses');
      if (calendar_show_event_type(CALENDAR_EVENT_COURSE)) {
          $content .= '<td class="eventskey calendar_event_course" style="width: 11px;"><img src="'.$OUTPUT->pix_url('t/hide') . '" class="iconsmall" alt="'.get_string('hide').'" title="'.get_string('tt_hidecourse', 'calendar').'" style="cursor:pointer" onclick="location.href='."'".$seturl."'".'" /></td>';
          $content .= '<td><a href="'.$seturl.'" title="'.get_string('tt_hidecourse', 'calendar').'">'.get_string('course', 'calendar').'</a></td>'."\n";
      } else {
          $content .= '<td style="width: 11px;"><img src="'.$OUTPUT->pix_url('t/show') . '" class="iconsmall" alt="'.get_string('hide').'" title="'.get_string('tt_showcourse', 'calendar').'" style="cursor:pointer" onclick="location.href='."'".$seturl."'".'" /></td>';
          $content .= '<td><a href="'.$seturl.'" title="'.get_string('tt_showcourse', 'calendar').'">'.get_string('course', 'calendar').'</a></td>'."\n";
      }
  
      if (isloggedin() && !isguestuser()) {
          $content .= "</tr>\n<tr>";
  
          if ($groupevents) {
              // This course MIGHT have group events defined, so show the filter
              $seturl->param('var', 'showgroups');
              if (calendar_show_event_type(CALENDAR_EVENT_GROUP)) {
                  $content .= '<td class="eventskey calendar_event_group" style="width: 11px;"><img src="'.$OUTPUT->pix_url('t/hide') . '" class="iconsmall" alt="'.get_string('hide').'" title="'.get_string('tt_hidegroups', 'calendar').'" style="cursor:pointer" onclick="location.href='."'".$seturl."'".'" /></td>';
                  $content .= '<td><a href="'.$seturl.'" title="'.get_string('tt_hidegroups', 'calendar').'">'.get_string('group', 'calendar').'</a></td>'."\n";
              } else {
                  $content .= '<td style="width: 11px;"><img src="'.$OUTPUT->pix_url('t/show') . '" class="iconsmall" alt="'.get_string('show').'" title="'.get_string('tt_showgroups', 'calendar').'" style="cursor:pointer" onclick="location.href='."'".$seturl."'".'" /></td>';
                  $content .= '<td><a href="'.$seturl.'" title="'.get_string('tt_showgroups', 'calendar').'">'.get_string('group', 'calendar').'</a></td>'."\n";
              }
          } else {
              // This course CANNOT have group events, so lose the filter
              $content .= '<td style="width: 11px;"></td><td>&nbsp;</td>'."\n";
          }
  
          $seturl->param('var', 'showuser');
          if (calendar_show_event_type(CALENDAR_EVENT_USER)) {
              $content .= '<td class="eventskey calendar_event_user" style="width: 11px;"><img src="'.$OUTPUT->pix_url('t/hide') . '" class="iconsmall" alt="'.get_string('hide').'" title="'.get_string('tt_hideuser', 'calendar').'" style="cursor:pointer" onclick="location.href='."'".$seturl."'".'" /></td>';
              $content .= '<td><a href="'.$seturl.'" title="'.get_string('tt_hideuser', 'calendar').'">'.get_string('user', 'calendar').'</a></td>'."\n";
          } else {
              $content .= '<td style="width: 11px;"><img src="'.$OUTPUT->pix_url('t/show') . '" class="iconsmall" alt="'.get_string('show').'" title="'.get_string('tt_showuser', 'calendar').'" style="cursor:pointer" onclick="location.href='."'".$seturl."'".'" /></td>';
              $content .= '<td><a href="'.$seturl.'" title="'.get_string('tt_showuser', 'calendar').'">'.get_string('user', 'calendar').'</a></td>'."\n";
          }
      }
      $content .= "</tr>\n</table>\n";
  
      return $content;
  }
 	
	private function calendar_show_day($d, $m, $y, $courses, $groups, $users, $courseid) {
		global $CFG, $USER;
		
		$content = '';

		if (!checkdate($m, $d, $y)) {
			$now = usergetdate(time());
			list($d, $m, $y) = array(intval($now['mday']), intval($now['mon']), intval($now['year']));
		}

		$getvars = 'from=day&amp;cal_d='.$d.'&amp;cal_m='.$m.'&amp;cal_y='.$y; // For filtering
		$somevars = array('from'=>'day','cal_d'=>$d,'cal_m'=>$m,'cal_y'=>$y);

		$starttime = make_timestamp($y, $m, $d);
		$endtime   = make_timestamp($y, $m, $d + 1) - 1;
		$events = calendar_get_upcoming($courses, $groups, $users, 1, 100, $starttime);

		$text = '';
		$text .= '<div style="height:5px;padding:2px;float:left">'.get_string('dayview', 'calendar').': </div>'.$this->calendar_course_filter_selector($somevars);
		$content.= '<div class="header">'.$text.'</div>';
		$content.= '<div class="controls">'.$this->calendar_top_controls('day', array('id' => $courseid, 'd' => $d, 'm' => $m, 'y' => $y)).'</div>';

		if (empty($events)) {
			// There is nothing to display today.
			$content.= '<h3>'.get_string('daywithnoevents', 'calendar').'</h3>';

		} else {

			$content.= '<div class="eventlist">';

			$underway = array();

			// First, print details about events that start today
			foreach ($events as $event) {

				$event->calendarcourseid = $courseid;

				if ($event->timestart >= $starttime && $event->timestart <= $endtime) {  // Print it now


	/*
					$dayend = calendar_day_representation($event->timestart + $event->timeduration);
					$timeend = calendar_time_representation($event->timestart + $event->timeduration);
					$enddate = usergetdate($event->timestart + $event->timeduration);
					// Set printable representation
					$content.= calendar_get_link_tag($dayend, $this->calurl.'&amp;view=day'.$morehref.'&amp;', $enddate['mday'], $enddate['mon'], $enddate['year']).' ('.$timeend.')';
	*/
					//unset($event->time);

					$event->time = calendar_format_event_time($event, time(), '', false, $starttime);
					$content.=$this->calendar_event_html($event);

				} else {                                                                 // Save this for later
					$underway[] = $event;
				}
			}

			// Then, show a list of all events that just span this day
			if (!empty($underway)) {
				$content.= '<h3>'.get_string('spanningevents', 'calendar').':</h3>';
				foreach ($underway as $event) {
					$event->time = calendar_format_event_time($event, time(), '', false, $starttime);
					$content.=$this->calendar_event_html($event);
				}
			}

			$content.= '</div>';
			
		}

		// return content
		return ($content);
	}

	private function calendar_show_month_detailed($m, $y, $courses, $groups, $users, $courseid) {
		global $CFG, $SESSION, $USER, $CALENDARDAYS;
		global $day, $mon, $yr, $url, $origuser;

		$content='';
		
		$getvars = array('from'=>'month','cal_d'=>$day,'cal_m'=>$mon,'cal_y'=>$yr); // For filtering
//		$getvars = 'from=month&amp;cal_d='.$day.'&amp;cal_m='.$mon.'&amp;cal_y='.$yr; // For filtering

		$display = &New stdClass;
		$display->minwday = get_user_preferences('calendar_startwday', calendar_get_starting_weekday());
		$display->maxwday = $display->minwday + 6;

		if(!empty($m) && !empty($y)) {
			$thisdate = usergetdate(time()); // Time and day at the user's location
			if($m == $thisdate['mon'] && $y == $thisdate['year']) {
				// Navigated to this month
				$date = $thisdate;
				$display->thismonth = true;
			}
			else {
				// Navigated to other month, let's do a nice trick and save us a lot of work...
				if(!checkdate($m, 1, $y)) {
					$date = array('mday' => 1, 'mon' => $thisdate['mon'], 'year' => $thisdate['year']);
					$display->thismonth = true;
				}
				else {
					$date = array('mday' => 1, 'mon' => $m, 'year' => $y);
					$display->thismonth = false;
				}
			}
		}
		else {
			$date = usergetdate(time());
			$display->thismonth = true;
		}

		// Fill in the variables we 're going to use, nice and tidy
		list($d, $m, $y) = array($date['mday'], $date['mon'], $date['year']); // This is what we want to display
		$display->maxdays = calendar_days_in_month($m, $y);

		$startwday = 0;
		if (get_user_timezone_offset() < 99) {
			// We 'll keep these values as GMT here, and offset them when the time comes to query the db
			$display->tstart = gmmktime(0, 0, 0, $m, 1, $y); // This is GMT
			$display->tend = gmmktime(23, 59, 59, $m, $display->maxdays, $y); // GMT
			$startwday = gmdate('w', $display->tstart); // $display->tstart is already GMT, so don't use date(): messes with server's TZ
		} else {
			// no timezone info specified
			$display->tstart = mktime(0, 0, 0, $m, 1, $y);
			$display->tend = mktime(23, 59, 59, $m, $display->maxdays, $y);
			$startwday = date('w', $display->tstart); // $display->tstart not necessarily GMT, so use date()
		}

		// Align the starting weekday to fall in our display range
		if($startwday < $display->minwday) {
			$startwday += 7;
		}

		// Get events from database
		$events = calendar_get_events(usertime($display->tstart), usertime($display->tend), $users, $groups, $courses);
		if (!empty($events)) {
			foreach($events as $eventid => $event) {
				if (!empty($event->modulename)) {
					$cm = get_coursemodule_from_instance($event->modulename, $event->instance);
					if (!groups_course_module_visible($cm)) {
						unset($events[$eventid]);
					}
				}
			}
		}
		
		// Extract information: events vs. time
		calendar_events_by_day($events, $m, $y, $eventsbyday, $durationbyday, $typesbyday, $courses);

		$text = '';
		/* Removed code below - parents shouldn't be able to create events in their childrens calendars!
		/*
		if(!isguest() && !empty($USER->id) && calendar_user_can_add_event()) {
			$text.= '<div class="buttons"><form action="'.CALENDAR_URL.'event.php" method="get">';
			$text.= '<div>';
			$text.= '<input type="hidden" name="action" value="new" />';
			$text.= '<input type="hidden" name="course" value="'.$courseid.'" />';
			$text.= '<input type="hidden" name="cal_m" value="'.$m.'" />';
			$text.= '<input type="hidden" name="cal_y" value="'.$y.'" />';
			$text.= '<input type="submit" value="'.get_string('newevent', 'calendar').'" />';
			$text.= '</div></form></div>';
		}
		*/

		$text .= '<div style="float:left;height:5px;padding:2px;">'.get_string('detailedmonthview', 'calendar').': </div> '.$this->calendar_course_filter_selector($getvars);

		$content.= '<div class="header">'.$text.'</div>';

		$content.= '<div class="controls">';
		$content.= $this->calendar_top_controls('month', array('id' => $courseid, 'm' => $m, 'y' => $y));
		$content.= '</div>';

		// Start calendar display
		$content.= '<table class="calendarmonth calendartable"><thead><tr class="weekdays">'; // Begin table. First row: day names
		$days = calendar_get_days();
		// Print out the names of the weekdays
		for($i = $display->minwday; $i <= $display->maxwday; ++$i) {
			// This uses the % operator to get the correct weekday no matter what shift we have
			// applied to the $display->minwday : $display->maxwday range from the default 0 : 6
			$content.= '<th class="header" scope="col">'.get_string($days[$i % 7], 'calendar').'</th>';
		}

		$content.= '</tr></thead><tbody><tr>'; // End of day names; prepare for day numbers

		// For the table display. $week is the row; $dayweek is the column.
		$week = 1;
		$dayweek = $startwday;

		// Paddding (the first week may have blank days in the beginning)
		for($i = $display->minwday; $i < $startwday; ++$i) {
			$content.= '<td class="nottoday">&nbsp;</td>'."\n";
		}

		// Now display all the calendar
		for($day = 1; $day <= $display->maxdays; ++$day, ++$dayweek) {
			if($dayweek > $display->maxwday) {
				// We need to change week (table row)
				$content.= "</tr>\n<tr>";
				$dayweek = $display->minwday;
				++$week;
			}

			// Reset vars
			$cell = '';
			$params=array('tab'=>'calendar','userid'=>$this->mdluser->id,'view'=>'day','course'=>$courseid);
			$tcalurl=new moodle_url('/blocks/mis/index.php',$params);
			$dayhref = calendar_get_link_href($tcalurl, $day, $m, $y);

			if(CALENDAR_DEFAULT_WEEKEND & (1 << ($dayweek % 7))) {
				// Weekend. This is true no matter what the exact range is.
				$class = 'weekend';
			}
			else {
				// Normal working day.
				$class = '';
			}

			// Special visual fx if an event is defined
			if(isset($eventsbyday[$day])) {
				if(count($eventsbyday[$day]) == 1) {
					$title = get_string('oneevent', 'calendar');
				}
				else {
					$title = get_string('manyevents', 'calendar', count($eventsbyday[$day]));
				}
				$cell = '<div class="day"><a href="'.$dayhref.'" title="'.$title.'">'.$day.'</a></div>';
			}
			else {
				$cell = '<div class="day">'.$day.'</div>';
			}

			// Special visual fx if an event spans many days
			if(isset($typesbyday[$day]['durationglobal'])) {
				$class .= ' duration_global';
			}
			else if(isset($typesbyday[$day]['durationcourse'])) {
				$class .= ' duration_course';
			}
			else if(isset($typesbyday[$day]['durationgroup'])) {
				$class .= ' duration_group';
			}
			else if(isset($typesbyday[$day]['durationuser'])) {
				$class .= ' duration_user';
			}

			// Special visual fx for today
			if($display->thismonth && $day == $d) {
				$class .= ' today';
			} else {
				$class .= ' nottoday';
			}

			// Just display it
			if(!empty($class)) {
				$class = ' class="'.trim($class).'"';
			}
			$content.= '<td'.$class.'>'.$cell;

			if(isset($eventsbyday[$day])) {
				$content.= '<ul class="events-new">';
				foreach($eventsbyday[$day] as $eventindex) {

					// If event has a class set then add it to the event <li> tag
					$eventclass = '';
					if (!empty($events[$eventindex]->class)) {
						$eventclass = ' class="'.$events[$eventindex]->class.'"';
					}

					$content.= '<li'.$eventclass.'><a href="'.$dayhref.'#event_'.$events[$eventindex]->id.'">'.format_string($events[$eventindex]->name, true).'</a></li>';
				}
				$content.= '</ul>';
			}
			if(isset($durationbyday[$day])) {
				$content.= '<ul class="events-underway">';
				foreach($durationbyday[$day] as $eventindex) {
					$content.= '<li>['.format_string($events[$eventindex]->name,true).']</li>';
				}
				$content.= '</ul>';
			}
			$content.= "</td>\n";
		}

		// Padding (the last week may have blank days at the end)
		for($i = $dayweek; $i <= $display->maxwday; ++$i) {
			$content.= '<td class="nottoday">&nbsp;</td>';
		}
		$content.= "</tr></tbody>\n"; // Last row ends

		$content.= "</table>\n"; // Tabular display of days ends

		// OK, now for the filtering display 	 
		$content.= '<div class="filters"><table><tr>';     
		
		// Global events
        $link = new moodle_url('/calendar/set.php', array('var' => 'showglobal', 'return' => base64_encode($url->out(false)), 'sesskey'=>$origuser->sesskey));
        if (calendar_show_event_type(CALENDAR_EVENT_GLOBAL)) {
            $content .= html_writer::tag('td', '', array('class'=>'calendar_event_global', 'style'=>'width:8px;'));
            $content .= html_writer::tag('td', html_writer::tag('strong', get_string('globalevents', 'calendar')).' '.get_string('shown', 'calendar').' ('.html_writer::link($link, get_string('clickhide', 'calendar')).')');
        } else {
            $content .= html_writer::tag('td', '', array('style'=>'width:8px;'));
            $content .= html_writer::tag('td', html_writer::tag('strong', get_string('globalevents', 'calendar')).' '.get_string('hidden', 'calendar').' ('.html_writer::link($link, get_string('clickshow', 'calendar')).')');
        }

        // Course events
        $link = new moodle_url('/calendar/set.php', array('var'=>'showcourses', 'return' => base64_encode($url->out(false)), 'sesskey'=>$origuser->sesskey));
        if (calendar_show_event_type(CALENDAR_EVENT_COURSE)) {
            $content .= html_writer::tag('td', '', array('class'=>'calendar_event_course', 'style'=>'width:8px;'));
            $content .= html_writer::tag('td', html_writer::tag('strong', get_string('courseevents', 'calendar')).' '.get_string('shown', 'calendar').' ('.html_writer::link($link, get_string('clickhide', 'calendar')).')');
        } else {
            $content .= html_writer::tag('td', '', array('style'=>'width:8px;'));
            $content .= html_writer::tag('td', html_writer::tag('strong', get_string('courseevents', 'calendar')).' '.get_string('hidden', 'calendar').' ('.html_writer::link($link, get_string('clickshow', 'calendar')).')');
        }
        $content .= html_writer::end_tag('tr');

        if(isloggedin() && !isguestuser()) {
            $content .= html_writer::start_tag('tr');
            // Group events
            $link = new moodle_url('/calendar/set.php', array('var'=>'showgroups', 'return' => base64_encode($url->out(false)), 'sesskey'=>$origuser->sesskey));
            if (calendar_show_event_type(CALENDAR_EVENT_GROUP)) {
                $content .= html_writer::tag('td', '', array('class'=>'calendar_event_group', 'style'=>'width:8px;'));
                $content .= html_writer::tag('td', html_writer::tag('strong', get_string('groupevents', 'calendar')).' '.get_string('shown', 'calendar').' ('.html_writer::link($link, get_string('clickhide', 'calendar')).')');
            } else {
                $content .= html_writer::tag('td', '', array('style'=>'width:8px;'));
                $content .= html_writer::tag('td', html_writer::tag('strong', get_string('groupevents', 'calendar')).' '.get_string('hidden', 'calendar').' ('.html_writer::link($link, get_string('clickshow', 'calendar')).')');
            }
            // User events
            $link = new moodle_url('/calendar/set.php', array('var'=>'showuser', 'return' => base64_encode($url->out(false)), 'sesskey'=>$origuser->sesskey));
            if (calendar_show_event_type(CALENDAR_EVENT_USER)) {
                $content .= html_writer::tag('td', '', array('class'=>'calendar_event_user', 'style'=>'width:8px;'));
                $content .= html_writer::tag('td', html_writer::tag('strong', get_string('userevents', 'calendar')).' '.get_string('shown', 'calendar').' ('.html_writer::link($link, get_string('clickhide', 'calendar')).')');
            } else {
                $content .= html_writer::tag('td', '', array('style'=>'width:8px;'));
                $content .= html_writer::tag('td', html_writer::tag('strong', get_string('userevents', 'calendar')).' '.get_string('hidden', 'calendar').' ('.html_writer::link($link, get_string('clickshow', 'calendar')).')');
            }
            $content .= html_writer::end_tag('tr');
        }
        $content .= html_writer::end_tag('table');
        $content .= html_writer::end_tag('div');
		return ($content);
	}

	private function calendar_show_upcoming_events($courses, $groups, $users, $futuredays, $maxevents, $courseid) {
		global $USER, $DB, $OUTPUT;

		$events = calendar_get_upcoming($courses, $groups, $users, $futuredays, $maxevents);

		$content = '';
		$text = '';

		
		$somevars = array('from'=>'upcoming');
		
		$content.= html_writer::start_tag('div', array('class'=>'header'));
		$content.= html_writer::tag('label', get_string('upcomingevents', 'calendar'), array('for'=>'cal_course_flt_jump'));
		$content.= $this->calendar_course_filter_selector($somevars);
		$content.= html_writer::end_tag('div');
		

		if ($events) {

			$content.= html_writer::start_tag('div', array('class'=>'eventlist'));
			foreach ($events as $event) {
				$event = new calendar_event($event);
				$event->calendarcourseid = $courseid;
				$content .= $this->event($event);
				//$content.=$this->calendar_event_html($event);
			}
			$content.= html_writer::end_tag('div');
		} else {
			$OUTPUT->heading(get_string('noupcomingevents', 'calendar'));
		}
		return ($content);
	}
	
	private function calendar_course_filter_selector($getvars = '') {
		global $USER, $SESSION, $DB, $OUTPUT, $origuser, $url;
		if (empty($USER->id) or isguestuser()) {
			return '';
		}

		if (has_capability('moodle/calendar:manageentries', get_context_instance(CONTEXT_SYSTEM)) && !empty($CFG->calendar_adminseesall)) {
			$courses = $DB->get_records('course',array('visible'=>1));
		} else {
			$courses = enrol_get_my_courses();
		}
		unset($courses[SITEID]);
		$courseoptions[SITEID] = get_string('fulllistofcourses');
		foreach ($courses as $course) {
			$courseoptions[$course->id] = format_string($course->shortname);
		}
		if (is_numeric($SESSION->cal_courses_shown)) {
			$selected = $SESSION->cal_courses_shown;
		} else {
			$selected = '';
		}
		$nothing='';
		$cal_vars=array('return' => base64_encode($url->out(false)),'var'=>'setcourse','sesskey'=>$origuser->sesskey);
		$merged_cal_vars=array_merge((array)$cal_vars,(array)$getvars);
		$calpath=new moodle_url('/calendar/set.php',$merged_cal_vars);
		return $OUTPUT->single_select($calpath,'id',$courseoptions,$selected,$nothing,'cal_course_flt'); 
	}
	
	private function calendar_event_html($event) {
		global $CFG, $USER;

		static $strftimetime;
		
		$content = '';

		$event = calendar_add_event_metadata($event);
		$content.= '<a name="event_'.$event->id.'"></a><table class="event" cellspacing="0">';
		$content.= '<tr><td class="picture">';
		if (!empty($event->icon)) {
			$content.= $event->icon;
		} else {
			print_spacer(16,16);
		}
		$content.= '</td>';
		$content.= '<td class="topic">';

		if (!empty($event->referer)) {
			$content.= '<div class="referer">'.$event->referer.'</div>';
		} else {
			$content.= '<div class="name">'.$event->name."</div>";
		}
		if (!empty($event->courselink)) {
			$content.= '<div class="course">'.$event->courselink.' </div>';
		}
		if (!empty($event->time)) {
			$content.= '<span class="date">'.$event->time.'</span>';
		} else {
			$content.= '<span class="date">'.calendar_time_representation($event->timestart).'</span>';
		}

		$content.= '</td></tr>';
		$content.= '<tr><td class="side">&nbsp;</td>';
		if (isset($event->cssclass)) {
			$content.= '<td class="description '.$event->cssclass.'">';
		} else {
			$content.= '<td class="description">'; 
		}
		$content.= format_text($event->description, FORMAT_HTML);
		$content.= '</td></tr></table>';
		return ($content);
	}	
	
	
	function calendar_top_controls($type, $data) {
		global $CFG, $CALENDARDAYS, $THEME;
		$content = '';
		if(!isset($data['d'])) {
			$data['d'] = 1;
		}

		// Ensure course id passed if relevant
		// Required due to changes in view/lib.php mainly (calendar_session_vars())
		$courseid = '';
		if (!empty($data['id'])) {
			$courseid = '&amp;course='.$data['id'];
		}

		if(!checkdate($data['m'], $data['d'], $data['y'])) {
			$time = time();
		}
		else {
			$time = make_timestamp($data['y'], $data['m'], $data['d']);
		}
		$date = usergetdate($time);

		$data['m'] = $date['mon'];
		$data['y'] = $date['year'];

		//Accessibility: calendar block controls, replaced <table> with <div>.
		//$nexttext = link_arrow_right(get_string('monthnext', 'access'), $url='', $accesshide=true);
		//$prevtext = link_arrow_left(get_string('monthprev', 'access'), $url='', $accesshide=true);
		$params=array('tab'=>'calendar','userid'=>$this->mdluser->id,'view'=>'month','course'=>$data['id']);
		$tcalurl=new moodle_url('/blocks/mis/index.php',$params);
		switch($type) {
			case 'frontpage':
				list($prevmonth, $prevyear) = calendar_sub_month($data['m'], $data['y']);
				list($nextmonth, $nextyear) = calendar_add_month($data['m'], $data['y']);
				$nextlink = $this->calendar_get_link_next(get_string('monthnext', 'access'), 'index.php?', 0, $nextmonth, $nextyear, $accesshide=true);
				$prevlink = $this->calendar_get_link_previous(get_string('monthprev', 'access'), 'index.php?', 0, $prevmonth, $prevyear, true);
				$content .= "\n".'<div class="calendar-controls">'. $prevlink;
				$content .= '<span class="hide"> | </span><span class="current"><a href="'.$this->calendar_get_link_href($tcalurl, 1, $data['m'], $data['y']).'">'.userdate($time, get_string('strftimemonthyear')).'</a></span>';
				$content .= '<span class="hide"> | </span>'. $nextlink ."\n";
				$content .= "<span class=\"clearer\"><!-- --></span></div>\n";
			break;
			case 'course':
				list($prevmonth, $prevyear) = calendar_sub_month($data['m'], $data['y']);
				list($nextmonth, $nextyear) = calendar_add_month($data['m'], $data['y']);
				$nextlink = $this->calendar_get_link_next(get_string('monthnext', 'access'), $this->calurl.'&amp;id='.$data['id'].'&amp;', 0, $nextmonth, $nextyear, $accesshide=true);
				$prevlink = $this->calendar_get_link_previous(get_string('monthprev', 'access'), $this->calurl.'&amp;id='.$data['id'].'&amp;', 0, $prevmonth, $prevyear, true);
				$content .= "\n".'<div class="calendar-controls">'. $prevlink;
				$content .= '<span class="hide"> | </span><span class="current"><a href="'.$this->calendar_get_link_href($tcalurl, 1, $data['m'], $data['y']).'">'.userdate($time, get_string('strftimemonthyear')).'</a></span>';
				$content .= '<span class="hide"> | </span>'. $nextlink ."\n";
				$content .= "<span class=\"clearer\"><!-- --></span></div>\n";
			break;
			case 'upcoming':
				$content .= '<div style="text-align: center;"><a href="'.$this->calurl.'&amp;view=upcoming"'.$courseid.'>'.userdate($time, get_string('strftimemonthyear'))."</a></div>\n";
			break;
			case 'display':
				$content .= '<div style="text-align: center;"><a href="'.$this->calendar_get_link_href($tcalurl, 1, $data['m'], $data['y']).'">'.userdate($time, get_string('strftimemonthyear'))."</a></div>\n";
			break;
			case 'month':
				list($prevmonth, $prevyear) = calendar_sub_month($data['m'], $data['y']);
				list($nextmonth, $nextyear) = calendar_add_month($data['m'], $data['y']);
				$prevdate = make_timestamp($prevyear, $prevmonth, 1);
				$nextdate = make_timestamp($nextyear, $nextmonth, 1);
				$content .= "\n".'<div class="calendar-controls">';
				$content .= $this->calendar_get_link_previous(userdate($prevdate, get_string('strftimemonthyear')), $this->calurl.'&amp;view=month'.$courseid.'&amp;', 1, $prevmonth, $prevyear);
				$content .= '<span class="hide"> | </span><span class="current">'.userdate($time, get_string('strftimemonthyear'))."</span>\n";
				$content .= '<span class="hide"> | </span>'.$this->calendar_get_link_next(userdate($nextdate, get_string('strftimemonthyear')), $this->calurl.'&amp;view=month'.$courseid.'&amp;', 1, $nextmonth, $nextyear);
				$content .= "<span class=\"clearer\"><!-- --></span></div>\n";
			break;
			case 'day':
				$data['d'] = $date['mday']; // Just for convenience
				$days = calendar_get_days();
				$prevdate = usergetdate(make_timestamp($data['y'], $data['m'], $data['d'] - 1));
				$nextdate = usergetdate(make_timestamp($data['y'], $data['m'], $data['d'] + 1));
				$prevname = calendar_wday_name($days[$prevdate['wday']]);
				$nextname = calendar_wday_name($days[$nextdate['wday']]);
				$content .= "\n".'<div class="calendar-controls">';
				$content .= $this->calendar_get_link_previous($prevname, $tcalurl, $prevdate['mday'], $prevdate['mon'], $prevdate['year']);

				// Get the format string
				$text = get_string('strftimedaydate');
				/*
				// Regexp hackery to make a link out of the month/year part
				$text = ereg_replace('(%B.+%Y|%Y.+%B|%Y.+%m[^ ]+)', '<a href="'.$this->calendar_get_link_href($this->calurl.'&amp;view=month&amp;', 1, $data['m'], $data['y']).'">\\1</a>', $text);
				$text = ereg_replace('(F.+Y|Y.+F|Y.+m[^ ]+)', '<a href="'.$this->calendar_get_link_href($this->calurl.'&amp;view=month&amp;', 1, $data['m'], $data['y']).'">\\1</a>', $text);
				*/
				// Replace with actual values and lose any day leading zero
				$text = userdate($time, $text);
				// Print the actual thing
				$content .= '<span class="hide"> | </span><span class="current">'.$text.'</span>';

				$content .= '<span class="hide"> | </span>'. $this->calendar_get_link_next($nextname, $this->calurl.'&amp;view=day'.$courseid.'&amp;', $nextdate['mday'], $nextdate['mon'], $nextdate['year']);
				$content .= "<span class=\"clearer\"><!-- --></span></div>\n";
			break;
		}
		return $content;
	}
	
	/**
	 * Build and return a previous month HTML link, with an arrow.
	 * @param string $text The text label.
	 * @param string $linkbase The URL stub.
	 * @param int $d $m $y Day of month, month and year numbers.
	 * @param bool $accesshide Default visible, or hide from all except screenreaders.
	 * @return string HTML string.
	 */
	function calendar_get_link_previous($text, $linkbase, $d, $m, $y, $accesshide=false) {
		$href = $this->calendar_get_link_href($linkbase, $d, $m, $y);
		if(empty($href)) return $text;
		return link_arrow_left($text, $href, $accesshide, 'previous'); 
	}

	/**
	 * Build and return a next month HTML link, with an arrow.
	 * @param string $text The text label.
	 * @param string $linkbase The URL stub.
	 * @param int $d $m $y Day of month, month and year numbers.
	 * @param bool $accesshide Default visible, or hide from all except screenreaders.
	 * @return string HTML string.
	 */
	function calendar_get_link_next($text, $linkbase, $d, $m, $y, $accesshide=false) {
		$href = $this->calendar_get_link_href($linkbase, $d, $m, $y);
		if(empty($href)) return $text;
		return link_arrow_right($text, $href, $accesshide, 'next');
	}
	
	/**
	 * TODO document
	 */
	function calendar_get_link_href($linkbase, $d, $m, $y) {
		if(empty($linkbase)) return '';
		$paramstr = '';
		if(!empty($d)) $paramstr .= '&amp;cal_d='.$d;
		if(!empty($m)) $paramstr .= '&amp;cal_m='.$m;
		if(!empty($y)) $paramstr .= '&amp;cal_y='.$y;
		if(!empty($paramstr)) $paramstr = substr($paramstr, 5);
		return $linkbase.$paramstr;
	}

	/**
	 * TODO document
	 */
	function calendar_get_link_tag($text, $linkbase, $d, $m, $y) {
		$href = calendar_get_link_href($linkbase, $d, $m, $y);
		if(empty($href)) return $text;
		return '<a href="'.$href.'">'.$text.'</a>';
	}	
	
	
        

	function event(calendar_event $event, $showactions=true) {
		global $origuser, $OUTPUT;
        $event = calendar_add_event_metadata($event);

        $anchor  = html_writer::tag('a', '', array('name'=>'event_'.$event->id));

        $table = new html_table();
        $table->attributes = array('class'=>'event', 'cellspacing'=>'0');
        $table->data = array(
            0 => new html_table_row(),
            1 => new html_table_row(),
        );

        if (!empty($event->icon)) {
            $table->data[0]->cells[0] = new html_table_cell($anchor.$event->icon);
        } else {
            $table->data[0]->cells[0] = new html_table_cell($anchor.$this->output->spacer(array('height'=>16, 'width'=>16, 'br'=>true)));
        }
        $table->data[0]->cells[0]->attributes['class'] .= ' picture';

        $table->data[0]->cells[1] = new html_table_cell();
        $table->data[0]->cells[1]->attributes['class'] .= ' topic';
        if (!empty($event->referer)) {
            $table->data[0]->cells[1]->text .= html_writer::tag('div', $event->referer, array('class'=>'referer'));
        } else {
            $table->data[0]->cells[1]->text .= html_writer::tag('div', $event->name, array('class'=>'name'));
        }
        if (!empty($event->courselink)) {
            $table->data[0]->cells[1]->text .= html_writer::tag('div', $event->courselink, array('class'=>'course'));
        }
        if (!empty($event->time)) {
            $table->data[0]->cells[1]->text .= html_writer::tag('span', $event->time, array('class'=>'date'));
        } else {
            $table->data[0]->cells[1]->text .= html_writer::tag('span', calendar_time_representation($event->timestart), array('class'=>'date'));
        }

        $table->data[1]->cells[0] = new html_table_cell('&nbsp;');
        $table->data[1]->cells[0]->attributes['class'] .= 'side';

        $table->data[1]->cells[1] = new html_table_cell($event->description);
        $table->data[1]->cells[1]->attributes['class'] .= ' description';
        if (isset($event->cssclass)) {
            $table->data[1]->cells[1]->attributes['class'] .= ' '.$event->cssclass;
        }

        if (calendar_edit_event_allowed($event) && $showactions) {
            if (empty($event->cmid)) {
                $editlink = new moodle_url(CALENDAR_URL.'event.php', array('action'=>'edit', 'id'=>$event->id));
                $deletelink = new moodle_url(CALENDAR_URL.'delete.php', array('id'=>$event->id));
                if (!empty($event->calendarcourseid)) {
                    $editlink->param('course', $event->calendarcourseid);
                    $deletelink->param('course', $event->calendarcourseid);
                }
            } else {
                $editlink = new moodle_url('/course/mod.php', array('update'=>$event->cmid, 'return'=>true, 'sesskey'=>$origuser->sesskey));
                $deletelink = null;
            }
			$this->output=$OUTPUT;
            $commands  = html_writer::start_tag('div', array('class'=>'commands'));
            $commands .= html_writer::start_tag('a', array('href'=>$editlink));
            $commands .= html_writer::empty_tag('img', array('src'=>$this->output->pix_url('t/edit'), 'alt'=>get_string('tt_editevent', 'calendar'), 'title'=>get_string('tt_editevent', 'calendar')));
            $commands .= html_writer::end_tag('a');
            if ($deletelink != null) {
                $commands .= html_writer::start_tag('a', array('href'=>$deletelink));
                $commands .= html_writer::empty_tag('img', array('src'=>$this->output->pix_url('t/delete'), 'alt'=>get_string('tt_deleteevent', 'calendar'), 'title'=>get_string('tt_deleteevent', 'calendar')));
                $commands .= html_writer::end_tag('a');
            }
            $commands .= html_writer::end_tag('div');
            $table->data[1]->cells[1]->text .= $commands;
        }
        return html_writer::table($table);
    }
	function basic_export_form($allowthisweek, $allownextweek, $allownextmonth, $userid, $authtoken) {

        $output  = html_writer::tag('div', get_string('export', 'calendar'), array('class'=>'header'));
        $output .= html_writer::start_tag('fieldset');
        $output .= html_writer::tag('legend', get_string('commontasks', 'calendar'));
        $output .= html_writer::start_tag('form', array('action'=>new moodle_url('/calendar/export_execute.php'), 'method'=>'get'));

        $output .= html_writer::tag('div', get_string('iwanttoexport', 'calendar'));

        $output .= html_writer::start_tag('div', array('class'=>'indent'));
        $output .= html_writer::empty_tag('input', array('type'=>'radio', 'name'=>'preset_what', 'id'=>'pw_all', 'value'=>'all', 'checked'=>'checked'));
        $output .= html_writer::tag('label', get_string('eventsall', 'calendar'), array('for'=>'pw_all'));
        $output .= html_writer::empty_tag('br');
        $output .= html_writer::empty_tag('input', array('type'=>'radio', 'name'=>'preset_what', 'id'=>'pw_course', 'value'=>'courses'));
        $output .= html_writer::tag('label', get_string('eventsrelatedtocourses', 'calendar'), array('for'=>'pw_course'));
        $output .= html_writer::empty_tag('br');
        $output .= html_writer::end_tag('div');

        $output .= html_writer::tag('div', get_string('for', 'calendar').':');

        $output .= html_writer::start_tag('div', array('class'=>'indent'));
        if ($allowthisweek) {
            $output .= html_writer::empty_tag('input', array('type'=>'radio', 'name'=>'preset_time', 'id'=>'pt_wknow', 'value'=>'weeknow', 'checked'=>'checked'));
            $output .= html_writer::tag('label', get_string('weekthis', 'calendar'), array('for'=>'pt_wknow'));
            $output .= html_writer::empty_tag('br');
        }
        if ($allownextweek) {
            $output .= html_writer::empty_tag('input', array('type'=>'radio', 'name'=>'preset_time', 'id'=>'pt_wknext', 'value'=>'weeknext'));
            $output .= html_writer::tag('label', get_string('weeknext', 'calendar'), array('for'=>'pt_wknext'));
            $output .= html_writer::empty_tag('br');
        }
        $output .= html_writer::empty_tag('input', array('type'=>'radio', 'name'=>'preset_time', 'id'=>'pt_monnow', 'value'=>'monthnow'));
        $output .= html_writer::tag('label', get_string('monththis', 'calendar'), array('for'=>'pt_monnow'));
        $output .= html_writer::empty_tag('br');
        if ($allownextmonth) {
            $output .= html_writer::empty_tag('input', array('type'=>'radio', 'name'=>'preset_time', 'id'=>'pt_monnext', 'value'=>'monthnext'));
            $output .= html_writer::tag('label', get_string('monthnext', 'calendar'), array('for'=>'pt_monnext'));
            $output .= html_writer::empty_tag('br');
        }
        $output .= html_writer::empty_tag('input', array('type'=>'radio', 'name'=>'preset_time', 'id'=>'pt_recupc', 'value'=>'recentupcoming'));
        $output .= html_writer::tag('label', get_string('recentupcoming', 'calendar'), array('for'=>'pt_recupc'));
        $output .= html_writer::empty_tag('br');
        $output .= html_writer::end_tag('div');

        $output .= html_writer::start_tag('div', array('class'=>'rightalign'));
        $output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'cal_d', 'value'=>''));
        $output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'cal_m', 'value'=>''));
        $output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'cal_y', 'value'=>''));
        $output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'userid', 'value'=>$userid));
        $output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'authtoken', 'value'=>$authtoken));

        $output .= html_writer::empty_tag('input', array('type'=>'submit', 'name' => 'generateurl', 'id'=>'generateurl', 'value'=>get_string('generateurlbutton', 'calendar')));
        $output .= html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('exportbutton', 'calendar')));

        $output .= html_writer::end_tag('div');

        $output .= html_writer::end_tag('form');
        $output .= html_writer::end_tag('fieldset');

        $output .= html_writer::start_tag('div', array('id'=>'urlbox', 'style'=>'display:none;'));
        $output .= html_writer::tag('p', get_string('urlforical', 'calendar'));
        $output .= html_writer::tag('div', '', array('id'=>'url', 'style'=>'overflow:scroll;width:650px;'));
        $output .= html_writer::end_tag('div');

        return $output;
    }
	
}
?>