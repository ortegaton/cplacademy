<?php

// include timelib
// require_once(dirname(__FILE__).'../../../../lib/timelib.php');
global $CFG;
require_once($CFG->dirroot.'/blocks/mis/lib/timelib.php');

    /**
    * Purpose: Work out how many timetable week definitions there are
    * Author: Guy Thomas
    * Date: 2008/07/07
    */
    function num_week_defs(){
        global $CFG, $fdata;
        $year=date('Y'); // current year
        $month=date('m'); // current month
        $fday=mis_time::first_mon_of_month(); // first monday of current month
        
        // build sql to get first 4 mondays in current month
        $daysql='';
        $dt=$fday; // date of monday
        for ($n=1; $n<=4; $n++){
            $daysql.=$daysql!='' ? ' OR ' : '';
            $daysql.='day(MapDate)='.intval(date("d", $dt));
            $dt += 60*60*24*7; // increase by 1 week
        }
        $daysql='AND ('.$daysql.')';
                
        $sql='SELECT dayposn FROM '.$fdata->prefix.'ccalmaps WHERE setid=\''.$CFG->mis->cmisDataSet.'\'  AND year(MapDate)='.$year.' AND month(MapDate)='.intval($month).' '.$daysql.' ORDER BY weeknum';
        $rs=$fdata->doQuery($sql, true); // get as associative array to provide unique number of week definitions (collapsed on day position)
        if (!$rs){
            return (false);
        }
        return (count($rs)); // return unique number of day positions that are mondays
    }
    
    /**
    * Purpose: Convert time minutes to percentage of hour
    */
    function timepercmin($time){
        $ta=explode(':',$time);
        $mt=$ta[0].'.'.strval(round(($ta[1]/60)*100));        
        return (doubleval($mt));
    }    
    
	function getWeekNo(){
		global $CFG, $fdata;
		$sql= 'SELECT dayposn ';
		$sql.= 'FROM '.$fdata->prefix.'CCALMAPS ';
		$sql.= 'WHERE (((CCALMAPS.MapDate)=#' . date('d/m/y') .' #) ';
		$sql.= 'AND ((CCALMAPS.SetId)=\'' . $CFG->mis->cmisDataSet .'\'));';
		$fieldname = 'dayposn';
		$dayNo = $fdata->getFieldValue($sql,$fieldname);
		if($dayNo >= 6){
			$weekNo = 1;
		}else{
			$weekNo = 2;
		}
		return $weekNo;		
	}
	

    /**
    * GT MOD - Deprecated by getTimetableRange
    */
    /*
	function getTimetable($studentid){
		global $CFG, $fdata;
		$sql  = "SELECT TIMETABLE.weekday, TIMETABLE.starttime, TIMETABLE.finishtime, TIMETABLE.duration, TIMETABLE.moduleid, MODULE.name, LECTURER.name, TIMETABLE.weekid, TIMETABLE.classgroupid, TIMETABLE.courseid, TIMETABLE.courseyear, TIMETABLE.clsgrpcode, TIMETABLE.roomid, STUDENTS.studentid";
		$sql .= " FROM STUDENTS INNER JOIN (MODULE INNER JOIN (TIMETABLE INNER JOIN LECTURER ON TIMETABLE.LecturerId = LECTURER.LecturerId) ON MODULE.ModuleId = TIMETABLE.ModuleId) ON STUDENTS.ClassGroupId = TIMETABLE.ClassGroupId";
		$sql .= " WHERE (((STUDENTS.StudentId)='" . $studentid . "') AND ((TIMETABLE.SetId)='" . $CFG->mis->cmisDataSet ."') AND ((LECTURER.SetId)='" . $CFG->mis->cmisDataSet ."') AND ((MODULE.SetId)='" . $CFG->mis->cmisDataSet ."') AND ((STUDENTS.SetId)='" . $CFG->mis->cmisDataSet ."'))";
		$sql .= " ORDER BY TIMETABLE.WeekDay, TIMETABLE.StartTime;";
        
		$arrTimetable = $fdata->doQuery($sql);
		return $arrTimetable;
	}
    */
    
    
	
    /**
    * Get a timetable for a specific week
    **/
	function getWeeksTimetable($studentid, $tm=false){
        $tm=$tm===false ? time() : $tm;
        // get first monday of specified time
        $tm=mis_time::mon_of_week($tm);
        // subtract 1 day (facility starts with Sunday as day 1!)
        // $tm-=(60*60*24);
        // return weeks time table
        return (getTimetableRange($studentid, $tm, 7));          
	}
    
    /**
    * Get a timetable for a specific date and day range
    **/
    function getTimetableRange($studentid, $tm, $days){
        global $CFG, $fdata;
        
        // strip hours, minutes and seconds from time
        $tm=mktime(00,00,00,date('m',$tm),date('d',$tm), date('Y',$tm));
        
        // adjusted time
        $tmadj=$tm+(60*60*24*($days-1))+(60*60*23)+(60*59)+59; // days -1 + 23 hours, 59 minutes and 59 seconds
        
        $dfrom=date('Y-m-d H:i:s.000', $tm);
        $dto=date('Y-m-d H:i:s.000', $tmadj);
        
        if ($CFG->mis->cmisDBType!='access'){        
        
            // SQL for MSSQL - works perfectly
            
            $sql='
            SELECT ws.startdate, ws.weeknumber, l.displectid, l.name AS lectname, t.*, tg.groupcode, md.name AS modulename, cm.mapdate, cm.weeknum FROM
            (((((('.$fdata->prefix.'stugroups AS sg
            LEFT JOIN '.$fdata->prefix.'timetable AS t ON t.groupid=sg.groupid)
            LEFT JOIN '.$fdata->prefix.'lecturer AS l ON l.lecturerid=t.lecturerid)
            LEFT JOIN '.$fdata->prefix.'weekmapnumeric AS wn ON wn.weekid=t.weekid)
            LEFT JOIN '.$fdata->prefix.'weekstructure AS ws ON wn.weeknumber=ws.weeknumber)
            LEFT JOIN '.$fdata->prefix.'ccalmaps AS cm ON wn.weeknumber=cm.weeknum AND t.weekday=cm.dayposn)
            LEFT JOIN '.$fdata->prefix.'teachinggroups AS tg ON tg.groupid=t.groupid)
            LEFT JOIN '.$fdata->prefix.'module AS md ON md.moduleid=t.moduleid
            WHERE
                sg.setid=\''.$CFG->mis->cmisDataSet.'\'
                AND sg.studentid=\''.$studentid.'\'
                AND t.setid=\''.$CFG->mis->cmisDataSet.'\'
                AND t.duration>0
                AND t.moduleid IS NOT NULL
                AND l.setid=\''.$CFG->mis->cmisDataSet.'\'
                AND wn.setid=\''.$CFG->mis->cmisDataSet.'\'
                AND ws.setid=\''.$CFG->mis->cmisDataSet.'\'
                AND cm.setid=\''.$CFG->mis->cmisDataSet.'\'
                AND tg.setid=\''.$CFG->mis->cmisDataSet.'\'
                AND md.setid=\''.$CFG->mis->cmisDataSet.'\'                
                AND cast (cm.mapdate as datetime) >= cast (\''.$dfrom.'\' as datetime)
                AND cast (cm.mapdate as datetime) <= cast (\''.$dto.'\' as datetime)
            ORDER BY t.starttime, t.finishtime, t.weekday
            ';  
        } else {        
            // CRAPPY SQL FOR ACCESS - does not work 100% perfect. You can end up with duplicate subjects for split groups.
            
			$dfrom=date('Y-m-d', $tm);
			$dto=date('Y-m-d', $tmadj);            
            $sql='

            SELECT DISTINCT l.displectid, l.name AS lectname, t.*, tg.groupcode, md.name AS modulename, cm.mapdate, cm.weeknum FROM
            ((((stugroups AS sg
            LEFT JOIN '.$fdata->prefix.'timetable AS t ON t.groupid=sg.groupid)
            LEFT JOIN '.$fdata->prefix.'lecturer AS l ON l.lecturerid=t.lecturerid)
            LEFT JOIN '.$fdata->prefix.'ccalmaps AS cm ON cm.dayposn=t.weekday)
            LEFT JOIN '.$fdata->prefix.'teachinggroups AS tg ON tg.groupid=t.groupid)
            LEFT JOIN '.$fdata->prefix.'module AS md ON md.moduleid=t.moduleid
            WHERE
                sg.setid=\''.$CFG->mis->cmisDataSet.'\'
                AND sg.studentid=\''.$studentid.'\'
                AND t.setid=\''.$CFG->mis->cmisDataSet.'\'
                AND t.duration>0
                AND t.moduleid IS NOT NULL
                AND l.setid=\''.$CFG->mis->cmisDataSet.'\'
                AND cm.setid=\''.$CFG->mis->cmisDataSet.'\'
                AND tg.setid=\''.$CFG->mis->cmisDataSet.'\'
                AND md.setid=\''.$CFG->mis->cmisDataSet.'\'    
                AND cDate(cm.mapdate) >= cDate(\''.$dfrom.'\')
                AND cDate(cm.mapdate) <= cDate(\''.$dto.'\')
            ORDER BY t.starttime, t.finishtime, t.weekday
            ';
        }
		

       
        $rs=$fdata->doQuery($sql);
        return ($rs);          
    }

    function regdatabyslotid($slotid){
    /*
        select * from TTATTSTUDENTS where setid='2008/2009'
select * from TTATTRSTU where setid='2008/2009'

SELECT * FROM ttattstudents AS ts LEFT JOIN timetable AS tt ON ts.slotid=tt.slotid WHERE ts.setid='2008/2009' AND ts.slotid=1467 AND ts. studentid='005883' AND tt.setid='2008/2009'
*/
    }
    
    function drawDaysTimetable($studentid,$tm){
        global $CFG;
        
        $tt=getTimetableRange($studentid, $tm, 1);
                        
        $table=new html_table();
        $table->head = array (get_string('start', 'block_mis'), get_string('finish', 'block_mis'), get_string('module', 'block_mis'), get_string('classroom', 'block_mis'), get_string('lecturer', 'block_mis')); // EMC / GT Mod 2009021200 - use language strings for day's timetable columns
        $table->align = array ('left', 'left', 'left', 'left', 'left');
        $table->width = "95%";        
        
        $output='<br />';
        
        if ($tt){
            foreach ($tt as $ev){
                $lectname=substr($ev->lectname, strpos($ev->lectname, ',')+2, 1).' '.substr($ev->lectname, 0, strpos($ev->lectname, ','));
                if (isset($CFG->mis->tt_lecturercode) && $CFG->mis->tt_lecturercode){
                    $lecturer=$ev->displectid;
                } else {
                    $lecturer=$lectname;
                }
                
                $table->data[]=array($ev->starttime, $ev->finishtime, $ev->modulename, $ev->roomid, $lecturer);
            }
            $output.=html_writer::table($table, true);
        } else {
            $output='<div class="error" style="margin:8px 2px 8px 2px">There is no timetable data for the specified day - '.date('d/m/Y', $tm).'.</div>';
        }
        return ($output);        
    }
    
    
    /**
    * @param required studentid - studentid in facilitt
    * @param required weeknum - week number starting from this week (so 1 would mean this week, 2 next week, 3 two weeks from now, etc)
    **/
	function drawWeeksTimetable($studentid,$weeknum,$shortName){
		global $CFG;
        
        $weektm=mis_time::mon_of_week(); // first monday of weeknum
        if ($weeknum>1){
            $weektm+=(60*60*24*7)*($weeknum-1);
        }
        
        $tt=getWeeksTimetable($studentid, $weektm);
        
        // Create times array and days array               
        $times=array();
        $dayrows=array();
        $trng_start=false; // time range start
        $trng_finish=false; // time range finish
        foreach ($tt as $ev){
            $trng_start=!$trng_start ? $ev->starttime : $trng_start;
            $times[$ev->starttime]=array('start'=>$ev->starttime, 'finish'=>$ev->finishtime);
            $evdate=strtotime($ev->mapdate);      
            $evday=date('N', $evdate); // 1 = monday - 7 =sunday
            $dayrows[$evday][]=$ev;      
            $trng_finish=$ev->finishtime;
        }
        $timeitems=count($times);
        
        
        
        // setup timeline
        $tlinestart=intval(timepercmin($trng_start));
        $tlinefinish=ceil(timepercmin(($trng_finish)));        
        $tlunits=($tlinefinish-$tlinestart);
        $tlunitperc=(100/$tlunits); // unit width percentage
                       
        $stclass=isset($CFG->mis->tt_eventsstagger) &&  $CFG->mis->tt_eventsstagger ? ' staggered' : '';
                       
        $timetable="";        
        $timetable.="\n".'<!--start time table--><div class="timetable'.$stclass.'">';
        $timetable.="\n\t".'<div class="daycell daycellhead"><div class="daycellinner"><div class="day">Day</div></div></div>';
        
        // Write timeline
        $timetable.="\n\t".'<div class="timeline">';

        
        
        for ($t=0; $t<$tlunits; $t++){        
            $timetable.="\n\t".'<div class="unit" style="width:'. $tlunitperc.'%"><div class="inner">'.($t+$tlinestart).':00</div></div>';
        }        
        $timetable.="\n\t".'</div><div class="clearer"></div>';
        
        // Write day rows
        $fm=mis_time::mon_of_week();        
        $ralt=1;
        $tm=$weektm;
        
        // Get last populated day from timetable
        for ($ld=1; $ld<=7; $ld++){
            if (isset($dayrows[$ld])){
                $lastday=$ld;
            }
        }
        
        for ($d=1; $d<=$lastday; $d++){
            $dtxt=date('D', $tm);
            $daydate=date('d-m-Y', $tm);
            $ralt=$ralt==1 ? 0 : 1;
            $timetable.="\n\t".'<div class="dayrow r'.$ralt.'">';
            $timetable.="\n\t\t".'<div class="daycell"><div class="daycellinner"><div class="day">'.$dtxt.'</div><div class="date">'.$daydate.'</div></div></div>';
            $timetable.="\n\t\t".'<div class="dayrowevents">';
            
            
            // Open first event lane (if staggering used)
            if (isset($CFG->mis->tt_eventsstagger) &&  $CFG->mis->tt_eventsstagger){
                $timetable.="\n\t\t".'<div class="eventlane0">';                
            }            
            
            // write events
            writeevents($timetable, $d, $dayrows, $tlinestart, $tlunits);

            // Close first event lane (if staggering used)
            if (isset($CFG->mis->tt_eventsstagger) && $CFG->mis->tt_eventsstagger){
                $timetable.="\n\t\t".'</div>';
            }
            

            if (isset($CFG->mis->tt_eventsstagger) && $CFG->mis->tt_eventsstagger){
                $timetable.="\n\t\t".'<div class="eventlane1">';  
                    // write events for 2nd event lane
                    writeevents($timetable, $d, $dayrows, $tlinestart, $tlunits, 1);                
                    $timetable.="\n\t\t".'</div>';
            } 

           
            $timetable.="\n\t\t".'</div>';
            
            $timetable.="\n\t".'</div>';
            $tm+=(60*60*24);         
        }
        
        
        
        $timetable.="\n".'</div><!--end time table-->';
                       
        return $timetable;
	}
    
    function writeevents(&$timetable, &$d, &$dayrows, &$tlinestart, &$tlunits, $evalt=0){
        global $CFG;
        
        $timespan=false; // current events time span
        $prevspan=false; // previous events time span
        $prevstart=''; // previous events start time (string)
        $block_start='';
        $block_finish='';
        
        // exit if the specified day is unpopulated
        if (!isset($dayrows[$d])){
            return;
        }
                
        foreach ($dayrows[$d] as $ev){
            // GT Mod - stagger timetable events on to two lanes per day row (for overlap reasons)
            if (!isset($CFG->mis->tt_eventsstagger) || $CFG->mis->tt_eventsstagger===false || $evalt==0){
                $block_start=$ev->starttime;
                $block_finish=$ev->finishtime;
                $timespan=timepercmin($block_finish)-timepercmin($block_start);
                $block_w=($timespan/$tlunits)*100;
                $block_l=((timepercmin($block_start)-$tlinestart)/$tlunits)*100;
                $ts_l=$block_l; // time span left position
                $ts_w=$block_w; // time span width
                                
                // if not in stagger mode and event is not greater than 30 minutes, increase block width and move left (if possible)
                if (!isset($CFG->mis->tt_eventsstagger) || $CFG->mis->tt_eventsstagger===false){
                    if ($timespan<=0.5){
                        $adjust_l=(((timepercmin($block_start)-$tlinestart)/$tlunits)-($timespan/$tlunits))*100; // block moved left by original width
                        // if first block in row or block adjusted left point is less than the end of previous block
                        $lastblockendpos=$prevspan ? (($prevspan+timepercmin($prevstart))/$tlunits)*100 : false;
                        if (!$prevspan || $lastblockendpos<$adjust_l){
                            $block_l=$adjust_l; // set block left position to adjusted version                          
                            $block_w=(($timespan/$tlunits)*2)*100; // double block width
                        }
                    }
                }
                
                $lectname=substr($ev->lectname, strpos($ev->lectname, ',')+2, 1).' '.substr($ev->lectname, 0, strpos($ev->lectname, ','));
                                
                if (isset($CFG->mis->tt_lecturercode) && $CFG->mis->tt_lecturercode){
                    $lecturer=$ev->displectid;
                } else {
                    $lecturer=$lectname;
                }                
            
                $timetable.="\n\t\t\t".'<div class="timespan" style="left:'.$ts_l.'%; width:'.$ts_w.'%"></div>';
                
                // All browsers except IE6 and Opera get correct css min-width property   
                if (stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6')===false && stripos($_SERVER['HTTP_USER_AGENT'], 'Opera')===false){
                    $block_wtype='min-width';
                } else {
                    $block_wtype='width';
                }
                
                $timetable.="\n\t\t\t".'<div id="sid_'.$ev->slotid.'" class="event unit unitabs" style="left:'.$block_l.'%; '.$block_wtype.':'.$block_w.'%">
                    <div class="inner">
                        <div class="evtime">'.$ev->starttime.' - '.$ev->finishtime.'</div>
                        <div class="module">'.$ev->modulename.'</div>
                        <div class="room">'.$ev->roomid.'</div>
                        <div class="lecturer">'.$lecturer.'</div>
                        </div>
                    </div>';
            }
            $evalt=$evalt==1 ? 0 : 1;
            $prevspan=$timespan;
            $prevstart=$block_start;
        }    
    }
	
?> 