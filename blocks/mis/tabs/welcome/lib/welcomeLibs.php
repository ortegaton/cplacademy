<?php
	

	/**
	* (c) Alan Hardy - Frederick Gent School 2008
	* 
	* Licence - GNU GENERAL PUBLIC LICENSE - Version 3, 29 June 2007
	*           Refer to http://www.gnu.org/licenses/gpl.html for full terms
	*
	* Version - Alpha 
	*
	* Date    - 03-03-2008
	*
	* Project - MIS - Facility to Moodle integration
	*
	**/

	/**
	* Get all the students details as an array of objects for a given user
	*
	* @uses $fdata, $CFG
	* @param Required $stuUnID - the students unique identifier  
	* @return Array of objects - stu details all student data for a give user
	*/

	function getStuDetails($stuUnID){
		global $CFG, $fdata;
		$fields = 'SELECT nst.Forename, ';
		$fields .= 'nst.CalledName, ';
		$fields .= 'nst.Surname, ';
		$fields .= 'nst.DateOfBirth, ';
		$fields .= 'nst.stuSex, ';
		$fields .= 'st.CourseId, ';
		$fields .= 'st.CourseYear, ';
		$fields .= 'st.classgroupid, ';
		$fields .= 'lct.Name AS Hoy ';
		$from = 'FROM '.$fdata->prefix.'ukstustats AS ukst INNER JOIN ('.$fdata->prefix.'lecturer AS lct INNER JOIN ('.$fdata->prefix.'classgroup AS cg INNER JOIN ('.$fdata->prefix.'students AS st INNER JOIN '.$fdata->prefix.'nstupersonal AS nst ON st.StudentId = nst.StudentId) ON cg.classgroupid = st.classgroupid) ON lct.lecturerid = cg.Lect1) ON ukst.StudentId = st.StudentId ';
		$where = ' WHERE (((ukst.UniqueNum)=\'' . $stuUnID .  '\') ';
		$where .='AND ((nst.SetId)=\'' .  $CFG->mis->cmisDataSet . '\') ';
		$where .='AND ((st.SetId)=\'' .  $CFG->mis->cmisDataSet . '\') ';
		$where .='AND ((cg.SetId)=\'' .  $CFG->mis->cmisDataSet . '\') ';
		$where .='AND ((lct.SetId)=\'' .  $CFG->mis->cmisDataSet . '\') ';
		$where .='AND ((ukst.SetId)=\'' .  $CFG->mis->cmisDataSet . '\'));';
		$sql = $fields . $from . $where;
		// get all but form tutor
		$stuDetails = $fdata->getRowValues($sql);
        
		// format student details date of birth
		if (isset($stuDetails['dateofbirth'])){
			if ($dobtm=mis_weltime::strtotime_yyyymmdd($stuDetails['dateofbirth'])){
				$stuDetails['dateofbirth']=mis_weltime::formatdate($dobtm);
			}
		}
        
		
        /* GT MOD - REMOVED SQL -  below will only work in schools where you actually have a timetabled event with a moduleid of 'REG'
		/*       
		$sql = "SELECT UKSTUSTATS.uniquenum as uniquenum, ";
		$sql .= "LECTURER.name AS name ";
		$sql .= "FROM UKSTUSTATS INNER JOIN (STUDENTS INNER JOIN (TIMETABLE INNER JOIN LECTURER ON TIMETABLE.LecturerId = LECTURER.LecturerId) ON STUDENTS.ClassGroupId = TIMETABLE.ClassGroupId) ON UKSTUSTATS.StudentId = STUDENTS.StudentId ";
		$sql .= "GROUP BY UKSTUSTATS.UniqueNum, LECTURER.Name, TIMETABLE.ModuleId, UKSTUSTATS.SetId, STUDENTS.SetId, TIMETABLE.SetId, LECTURER.SetId ";
		$sql .= "HAVING (((UKSTUSTATS.UniqueNum)='" . $stuUnID . "') AND ((TIMETABLE.ModuleId)='REG') AND ((UKSTUSTATS.SetId)='" .  $CFG->mis->cmisDataSet ."') AND ((STUDENTS.SetId)='" .  $CFG->mis->cmisDataSet ."') AND ((TIMETABLE.SetId)='" .  $CFG->mis->cmisDataSet . "') AND ((LECTURER.SetId)='"  .$CFG->mis->cmisDataSet ."'));";
        */
        /* SEE updated sql below (gets form tutor from students class group)*/
                
        $sql='SELECT * FROM (('.$fdata->prefix.'ukstustats AS us LEFT JOIN '.$fdata->prefix.'students AS st ON st.studentid=us.studentid)';
        $sql.=' LEFT JOIN '.$fdata->prefix.'classgroup AS cg ON st.classgroupid=cg.classgroupid)';
        $sql.=' LEFT JOIN '.$fdata->prefix.'lecturer AS lt ON lt.lecturerid=cg.lect1';
        $sql.=' WHERE us.uniquenum=\''.$stuUnID.'\' AND us.setid=\''.$CFG->mis->cmisDataSet.'\'  AND st.setid=\''.$CFG->mis->cmisDataSet.'\' AND cg.setid=\''.$CFG->mis->cmisDataSet.'\' AND lt.setid=\''.$CFG->mis->cmisDataSet.'\'';
        
        
		//get form teacher 
		$stuDetails2 = $fdata->getRowValues($sql);
		//Add form teacher to arry
        if ($stuDetails2 && $stuDetails){
            $stuDetails['ftutor'] = $stuDetails2['name'];
        }

		return $stuDetails;
	}
	
	
	/**
	* Print the stu details block 
	*
	* @param Required $stuUnID - the students unique identifier  
	* @return Array of objects - stu details all student data for a give user
	*/
	function printStuDetails($stuDetails){    
        if ($stuDetails){
    		$content = "<table class=\"stuDetails\" >\n";
    		$content .= "	<tr>\n";
    		$content .= "		<td class=\"label\">\n";
    		$content .= "			Name: ";
    		$content .= "		</td>\n";
    		$content .= "		<td class=\"value\">\n";
    		$content .= 			$stuDetails['forename'] . " " . $stuDetails['surname'];//calledname not showing - changed to forename EMC
    		$content .= "		</td>\n";
    		$content .= "	</tr>\n";
    		
    		$content .= "	<tr>\n";
    		$content .= "		<td class=\"label\">\n";
    		$content .= "			DOB: ";
    		$content .= "		</td>\n";
    		$content .= "		<td class=\"value\">\n";
    		$content .= 			$stuDetails['dateofbirth'];
    		$content .= "		</td>\n";
    		$content .= "	</tr>\n";
    		
    		$content .= "	<tr>\n";
    		$content .= "		<td class=\"label\">\n";
    		$content .= "			Sex: ";
    		$content .= "		</td>\n";
    		$content .= "		<td class=\"value\">\n";
    		$content .= 			$stuDetails['stusex'];
    		$content .= "		</td>\n";
    		$content .= "	</tr>\n";
    		
    		$content .= "	<tr>\n";
    		$content .= "		<td class=\"label\">\n";
    		$content .= "			Course Stage: ";
    		$content .= "		</td>\n";
    		$content .= "		<td class=\"value\">\n";
    		$content .= 			$stuDetails['courseid'];
    		$content .= "		</td>\n";
    		$content .= "	</tr>\n";
    		
    		$content .= "	<tr>\n";
    		$content .= "		<td class=\"label\">\n";
    		$content .= "			School Year: ";
    		$content .= "		</td>\n";
    		$content .= "		<td class=\"value\">\n";
    		$content .= 			$stuDetails['courseyear'];
    		$content .= "		</td>\n";
    		$content .= "	</tr>\n";
    		
    		$content .= "	<tr>\n";
    		$content .= "		<td class=\"label\">\n";
    		$content .= "			Form: ";
    		$content .= "		</td>\n";
    		$content .= "		<td class=\"value\">\n";
    		$content .= 			$stuDetails['classgroupid'];
    		$content .= "		</td>\n";
    		$content .= "	</tr>\n";

            /*
    		$content .= "	<tr>\n";
    		$content .= "		<td class=\"label\">\n";
    		$content .= "			Form Tutor: ";
    		$content .= "		</td>\n";
    		$content .= "		<td class=\"value\">\n";
    		$arrFTName = explode(",",$stuDetails['ftutor']);
    		$content .= 		$arrFTName[1] ." " . $arrFTName[0];
    		$content .= "		</td>\n";
    		$content .= "	</tr>\n";
            */
            
            /*
    		$content .= "	<tr>\n";
    		$content .= "		<td class=\"label\">\n";
    		$content .= "			Head of Year: ";
    		$content .= "		</td>\n";
    		$content .= "		<td class=\"value\">\n";
    		
    		$arrHoYName = explode(",",$stuDetails['hoy']);
    		$content .= 		$arrHoYName[1] ." " . $arrHoYName[0];
    		$content .= "		</td>\n";
    		$content .= "	</tr>\n";
            */
        } else {
            $content='<div class="errorbox">Error: Student Not Found</div>';
        }
		$content .= "</table>\n";
		$Block = new Block("Personal Details","personal","",$content);
		$content = $Block->draw();
		return $content;
	}
	
	
	/**
	* Perform Attendance checks 
	*
	* @param Required $arrNotifications - array to hold notifictions as they are built 
	* @return Array of notifications - stu details all student data for a give user
	*/
	function checkNotifyAtt($arrNotifications){
		$arrAtt = array("label" =>"info",
						"text"  =>"Your student is below the 93% attendance target.",
						"origin"=>"Attendance System");

		$arrNotifications[] = $arrAtt;

		$arrAtt1 = array("label" =>"warning",
						 "text"  =>"Your Student has 3 recorded lates in the last 30 days",
						 "origin"=>"Attendance System");

		$arrNotifications[] = $arrAtt1;

	return $arrNotifications;
	}
	
	
	/**
	* Perform Assessment checks 
	*
	* @param Required $arrNotifications - array to hold notifictions as they are built 
	* @return Array of notifications - stu details all student data for a give user
	*/
	function checkNotifyAss($arrNotifications){
		$arrAss = array("label" =>"info",
						"text"  =>"New Upcoming Assesment",
						"origin"=>"Assessment System");
		$arrNotifications[] = $arrAss;
	return $arrNotifications;
	}	

	/**
	* Perform Library System checks 
	*
	* @param Required $arrNotifications - array to hold notifictions as they are built 
	* @return Array of notifications - stu details all student data for a give user
	*/
	function checkNotifyLib($arrNotifications){
		$arrLibrary = array("label" =>"warning",
							"text"  =>"Your student has an overdue book loan.",
							"origin"=>"Library System");
		$arrNotifications[] = $arrLibrary;
	return $arrNotifications;
	}
	
	/**
	* Perform Detention checks 
	*
	* @param Required $arrNotifications - array to hold notifictions as they are built 
	* @return Array of notifications - stu details all student data for a give user
	*/
	function checkNotifyDet($arrNotifications){
		$arrLibrary = array("label" =>"info",
							"text"  =>"Your student has a detention on the 01/01/08.",
							"origin"=>"Detention System");
		$arrNotifications[] = $arrLibrary;	
	return $arrNotifications;
	}	

	/**
	* Perform Messaging system checks 
	*
	* @param Required $arrNotifications - array to hold notifictions as they are built 
	* @return Array of notifications - stu details all student data for a give user
	*/
	function checkNotifyMsg($arrNotifications){
		$arrLibrary = array("label" =>"message",
							"text"  =>"Please can you contact school as soon as possible.",
							"origin"=>"Mrs Teacher");
		$arrNotifications[] = $arrLibrary;	
	return $arrNotifications;
	}
	
	
		/**
		* Perform Assessment checks 
		*
		* @param Required $arrNotifications - array to hold notifictions as they are built 
		* @return Array of notifications - stu details all student data for a give user
	*/
	function printNotifications(){
		global $CFG;
		$arrNotifications = array();
		$arrNotifications = checkNotifyAtt($arrNotifications);
		$arrNotifications = checkNotifyAss($arrNotifications);
		$arrNotifications = checkNotifyLib($arrNotifications);
		$arrNotifications = checkNotifyDet($arrNotifications);
		$arrNotifications = checkNotifyMsg($arrNotifications);
		$notifications = "";
		
		$arrLinks = array("Attendance System"=>get_mis_blockwww().'attendance.php',
						  "Assessment System"=>get_mis_blockwww().'/blocks/mis/assessment.php',
						  "Library System"   =>get_mis_blockwww().'/blocks/mis/library.php',
						  "Detention System" =>get_mis_blockwww().'/blocks/mis/detention.php');
		
		foreach($arrNotifications as $arrNotification){
			if (array_key_exists($arrNotification['origin'],$arrLinks)){
				$notifications .= "<a href=\"". $arrLinks[$arrNotification['origin']] ."\"><div class=\"notification n" . $arrNotification['label'] . "\">" . $arrNotification['text']  . "</div></a>";
			}else{
				$notifications .= "<div class=\"notification n" . $arrNotification['label'] . "\">" . $arrNotification['text']  . "</div>";
			}
		}
		
		$Block = new Block("Notifications","notifications","",$notifications);
		$table = $Block->draw();
		return $table;
	}

	function printLetters(){
		$stuUnId = getStuUPN($_SESSION['userid']);
		$arrStuDetails = getStuDetails($stuUnId);
		$year = $arrStuDetails['courseyear'];
		$xml = simplexml_load_file('http://www.frederickgent.derbyshire.sch.uk/?q=lettersrss/feed');
		$data="<table class=\"letters\">";
		foreach($xml->channel->item as $item){
			if (($item->category == "Year " . $year) || ($item->category == "All Letters") || ($item->category == "Whole School") || ($item->category == "Trips") ||($item->category == "PE Department")){
				$data .= "<tr><td class=\"letter\"><a href=\"javascript:poptastic('". $item->enclosure->attributes()->url  ."');\">" . $item->title . "</a></td><tr>\n";
			}
		}
		$data .="</table>";
		$Block = new Block("Letters","letters","",$data);
		$table = $Block->draw();
		return $table;
	}
	
	function printAttOverview(){
			global $USER,  $CFG, $presentCount, $absAuthCount, $absNotAuthCount, $lateCount;
			
            $chart='';
            
			//************************* generate Graph ***************
            
			
            
            
            $table = "<div class=\"newlabel\">Last Months attendance data</div>";
            
			$table .="<div class=\"graphContainer\">";
            
            /* GT MOD 2009010500- removed following code (does not work for January, as would result in month 0!)
			$passedMonth = date('n') -1;
			$passedYear = date('Y');
            */

			/* DEPRECATED MOD - not compatible with old PHP 5 
            // GT MOD 2009010500 - create dat time object for current month and then subtract by 1 month                        
            $dtst=date('Y').'-'.date('m').'-01'; // date time string
            $dt = new DateTime($dtst); // date object 
            $dt->modify("-1 month"); // subtract date by 1 month
            $passedMonth=intval($dt->format('n')); // get month
            $passedYear=intval($dt->format('Y')); // get year
			*/
			
			// GT MOD 2009
			$dt=mis_weltime::adjust_time(mis_weltime::first_of_month(), 0, -1);
			$passedMonth=date('n',$dt);
			$passedYear=date('Y',$dt);
			
            
			$stuId = $USER->mis_mdlstuid;

			// GT MOD - get roll call times
			$rctime=getRCTime();    
			$fdata = new facilityData();
			$schoolDays = getSchoolDays($passedMonth,$passedYear);

			if ($CFG->mis->debug){
				print_r($schoolDays);
				echo "<br>";
			}

			if($schoolDays != ""){
				$attDetails = getAttDetails($stuId,$passedMonth,$passedYear);

				if ($CFG->mis->debug){
					echo "<br>*****************Start of attDetails********************<br>";
					print_r($attDetails);
					echo "<br>";
				}
      
				if($attDetails){
					//loop through all school days entries
					foreach ($schoolDays as $schoolDay){
						foreach	($attDetails as $attDetail){	
							//if an entry exists for the current iteration date then an absense occured
							if ($attDetail->attdate == $schoolDay->cdate){

								//cope with full days
								//GT MOD - use roll call start and end times to determine am on pm values
								if ($attDetail->starttime <= $rctime->am AND $attDetail->finishtime >= $rctime->pm){
									$multiplier =2;
									setRegStatus($attDetail,$multiplier);
								}

								//handle morning reg only
								if ($attDetail->finishtime < $rctime->pm){
									$multiplier =1;
									setRegStatus($attDetail,$multiplier);
								}

								//handle afternoon reg only
								if ($attDetail->starttime > $rctime->am){
									$multiplier =1;
									setRegStatus($attDetail,$multiplier);
								}				
							}
						}
					}
					//work out how many present periods 
					$presentCount = getPresentRegs($schoolDays);
					//draw the final chart
					$chart = drawAttChart();

				}else{
					//work out how many present periods 
					$presentCount = getPresentRegs($schoolDays);
					//draw the final chart
					$chart = drawAttChart();
				}
			}else{
			}


			$table .= $chart;
			$table .= "</div>\n";
                        
			//************************* generate data ***************
			$table .= "<div class=\"newlabel\">This months attendance data so far</div>";
			$table .= "<div class=\"dataContainer\">";
			
			$passedMonth = date('n');
			$passedYear = date('Y');
			
			$presentCount =0;
			$absAuthCount = 0;
			$absNotAuthCount = 0;
			$lateCount = 0;
			
			$schoolDays = getSchoolDays($passedMonth,$passedYear);
			
			if ($CFG->mis->debug){
				print_r($schoolDays);
				echo "<br />";
			}
			
			if($schoolDays != ""){
				$attDetails = getAttDetails($stuId,$passedMonth,$passedYear);

				if ($CFG->mis->debug){
					echo "<br />*****************Start of attDetails********************<br>";
					print_r($attDetails);
					echo "<br />";
				}

				if($attDetails){
					//loop through all school days entries
					foreach ($schoolDays as $schoolDay){
						foreach	($attDetails as $attDetail){	

							//if an entry exists for the current iteration date then an absense occured
							if ($attDetail->attdate == $schoolDay->cdate){

								//cope with full days
								//GT MOD - use roll call start and end times to determine am on pm values
								if ($attDetail->starttime <= $rctime->am AND $attDetail->finishtime >= $rctime->pm){
									$multiplier =2;
									setRegStatus($attDetail,$multiplier);
								}

								//handle morning reg only
								if ($attDetail->finishtime < $rctime->pm){
									$multiplier =1;
									setRegStatus($attDetail,$multiplier);
								}

								//handle afternoon reg only
								if ($attDetail->starttime > $rctime->am){
									$multiplier =1;
									setRegStatus($attDetail,$multiplier);
								}				
							}
						}
					}
                    
                }
                //work out how many present periods 
                $presentCount = getPresentRegs($schoolDays);
                //draw the final chart
                $table .= "	<table class=\"table\">\n";
                $table .= "		<tr>\n";
                $table .= "			<td>Absent Authorised (" . $absAuthCount /2 . " days)</td>\n";
                $table .= "		</tr>\n";					
                $table .= "		<tr>\n";
                $table .= "			<td>Absent Unauthorised (" . $absNotAuthCount/2 . " days) </td>\n";
                $table .= "		</tr>\n";
                $table .= "		<tr>\n";
                $table .= "			<td>Present(Late) (" . $lateCount/2 . " days)</td>\n";
                $table .= "		</tr>\n";
                $table .= "	</table>\n";
					
				
			}
			$table .= "</div>\n";
			$table .="<br />\n";
            
			$table .='<div style="text-align:center"><a href="' .get_mis_blockwww().'/index.php?tab=attendance">Click here to see more detail</a></div>';
             
			$Block = new Block("Attendance Overview","attendance","",$table);
			$content = $Block->draw();
			return $content;
	}
	
	function printAssOverview(){
		$Block = new Block("Assessment Overview","assessment","","Assessment will go here");
		$table = $Block->draw();
		return $table;
	}
	
	function printTTOverview(){
		global $USER, $CFG,$fdata,$userDetails;
		
		$studentid = $fdata->getStuAdminNo($userDetails->idnumber);            
		$timetable = drawDaysTimetable($studentid, time());
		
		$Block = new Block("Todays Timetable","timetable","",$timetable . '<div style="text-align:center"><a href="' .get_mis_blockwww().'/index.php?tab=timetable">Click here to see more detail</a></div>');
		$table = $Block->draw();
		return $table;
	}
	
	function printcalendarblock($forceuser=false){
		global $CFG, $USER;
		
		// DIRTY HACK!
		// Get calendar for a specific user by overriding global $USER variable temporarilly
		if ($forceuser){
			$origuser=$USER;
			$USER=$forceuser;
			$USER->realuser=$USER; // let the calendar know that we are looking at someone elses calendar
		}
		
		$bcm=new block_calendar_month();
		$content=$bcm->get_content();
		$calendar=$content->text;
				
		// Now replace event links with links to custom calendar viewer
		$calendar=str_replace($CFG->wwwroot.'/calendar/view.php?',
			$CFG->wwwroot.'/blocks/mis/index.php?tab=calendar&amp;userid='.$USER->id.'&amp;',
			$calendar);

		// DIRTY HACK CTD. !
		// We've got the calendar html so now restore $USER
		if ($forceuser){
			$USER=$origuser;
		}
		$calendar=str_replace('id="overDiv"', 'id="overDivDisabled"', $calendar); // disable overDiv (causes problems with pop ups positioned incorrectly)
		$block = new Block("Calendar","","",$calendar);
		$output = $block->draw();
		return ($output);
	}
?>