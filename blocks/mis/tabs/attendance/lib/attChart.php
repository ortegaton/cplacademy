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
        

	require_once('../../../../../config.php');		
    require_once('../../../cfg/config.php');
	require_once('../../../lib/block.php');
    include_once('../../../lib/setvars.php'); // GT Mod set standard variables	    
	require_once('../../../lib/chart/FusionCharts.php');
    require_once('../../../lib/timelib.php');
	require_once('../../../lib/urllib.php');
	require_once("attLibs.php");

    $chart='';
    
    $fdata=new facilityData();
	
    // GT Mod 2008/09/10 - changed variables $passedMonth and $passedYear to $pmonth and $pyear (passed month could be interperated as previous month)
    
	$pmonth = optional_param('month', false, PARAM_INT);
	if (!$pmonth){
		$pmonth = date("n");
	}
    $pyear = optional_param('year', false, PARAM_INT);	
	if (!$pyear){
		$pyear = date("Y");
	}
	
    global $absAuthCount, $absNotAuthCount, $lateCount; // GT Note - we should not be using globals for tallying - lets create a class for this at some point and change these to class properties.
    
	$absAuthCount = 0;
	$absNotAuthCount = 0;
	$lateCount = 0;
	
	if ($CFG->mis->debug){
		echo "Passed Month: " . $pmonth . " - Current Month: " . date("n") ."<br>";
		echo "Passed Year: " . $pyear . " - Current Year: " . date("Y") ."<br>"; 
	}
	
    // GT MOD - get roll call times
    $rctime=getRCTime();    
    
    // GT MOD 2008/09/10 - bug fix, do not check month and year seperately - check them as a combined date (otherwise attendance will be retrieved for next year because the month will be less than the current month)	
	// if (!($pmonth > (date("n")) AND ($pyear >= date("Y")))){                                                 
           
    $ldm=mis_time::last_date_of_month($pmonth, $pyear);
    $cdm=mis_time::last_date_of_month(date("n"), date("Y"));
    
    if ($ldm<=$cdm){
    
		//connect to facility database and get school days and attendance details
		$fdata = new facilityData();
		$schoolDays = getSchoolDays($pmonth,$pyear);
		
		if ($CFG->mis->debug){
			print_r($schoolDays);
			echo "<br>";
		}
				
		if($schoolDays != ""){
			$attDetails = getAttDetails($USER->mis_mdlstuid,$pmonth,$pyear);
			
			if ($CFG->mis->debug){
				echo "<br>*****************Start of attDetails********************<br>";
				print_r($attDetails);
				echo "<br>";
			}
			
			if($attDetails !=""){
				//loop through all school days entries
				foreach ($schoolDays as $schoolDay){
					foreach	($attDetails as $attDetail){	

						//if an entry exists for the current iteration date then an absense occured
						if ($attDetail->attdate == $schoolDay->cdate){

							//cope with full days
                            //GT MOD - use roll call start and end times to determine am / pm values
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

			$icon = "pix/attWarning.gif";
			$error =  "<h2 align=\"center\"><br>Currently this system can only display attendance data for this academic year.</h2>";
			$chart=drawError($icon,$error,true);
		}
	}else{
		$icon = "pix/attWarning.gif";
		$error = "<h2 align=\"center\"><br>No data is available for this period.</h2><p align=\"center\">This is because the month requested is in the future </h2>";
		$chart=drawError($icon,$error,true);
	}
	$Block = new Block(get_string('attChart','block_mis') . getMonthName(),"attChart","",$chart);
	$table = $Block->draw();
	echo $table;
?>
