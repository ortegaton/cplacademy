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
	require_once("../../../lib/block.php");
	require_once("../../../lib/timelib.php");
    include_once('../../../lib/setvars.php'); // GT Mod set standard variables	
	require_once("attLibs.php");
    
	

	
	$amClass = "";
	$pmClass = "";
	$output = '';
	$month = required_param('month', PARAM_INT);
	$year = required_param('year', PARAM_INT);
	
	if($month == '' && $year == '') { 
        $time = time();
        $month = date('n',$time);
        $year = date('Y',$time);
    }	
	
    $cal=new calendar_month(mktime(0,0,0,$month,1,$year));	

    $date = getdate($cal->startdt);
	$today = getdate();
	$toduts=mktime(0,0,0,$today['mon'],$today['mday'], $today['year']); // uts time for today (no hours, minutes or seconds)
	$hours = $today['hours'];	
	$mins = $today['minutes'];
	$secs = $today['seconds'];

	if(strlen($hours)<2) $hours="0".$hours;
	if(strlen($mins)<2) $mins="0".$mins;
	if(strlen($secs)<2) $secs="0".$secs;

	$days=date("t",mktime(0,0,0,$month,1,$year));
	$start = $date['wday'];
	$name = $date['month'];

	$offset = $days + $start - 1;

	if($month==12) { 
		$next=1; 
		$nexty=$year + 1; 
	} else { 
		$next=$month + 1; 
		$nexty=$year; 
	}

	if($month==1) { 
		$prev=12; 
		$prevy=$year - 1; 
	} else { 
		$prev=$month - 1; 
		$prevy=$year; 
	}
    

	$output .= '    
    <table class="cal" cellspacing="1">
	<tr>
		<td colspan="7">
			<table class="calhead">
			<tr>
				<td>
					<a href="javascript:monthDraw('.$prev.','.$prevy.')"><img src="pix/calLeft.gif"></a> <a href="javascript:monthDraw(\'\',\'\')"><img src="pix/calCenter.gif"></a> <a href="javascript:monthDraw('.$next.','.$nexty.')"><img src="pix/calRight.gif"></a>
				</td>
				<td align="right">
					<div>'.$name.' '.$year.'</div>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr class="dayhead">
		<td>Mon</td>
		<td>Tue</td>
		<td>Wed</td>
		<td>Thu</td>
		<td>Fri</td>
		<td>Sat</td>
		<td>Sun</td>
	</tr>';

	$col=1;
	$cur=1;
	$next=0;
	$fdata = new facilityData();    
    
    // GT MOD - array of day codes
    $daycodes=getConfDayCodes();    
    
    // GT MOD - get roll call times
    $rctime=getRCTime();
    	
    // get roll call data hashed by uts
	$rollcall = rollcall_hash_by_uts($USER->mis_mdlstuid,$month,$year);
	
	// get all calendar days hashed by uts
	$cdays=all_days_hash_by_uts($month, $year);
    
	$grid=$cal->gridmonsun;
	foreach ($grid as $week){
	    $output.='<tr class="dayrow">';
	    $d=0;
	    foreach ($week as $dayobj){
	        $d++;
	        $day=$dayobj ? $dayobj->day : false;
	        $output.='<td>';
            if (!$day){
                $output.='<div width="100%" class="noday"></div>';
            } else {   

                if (isset($cdays[$dayobj->uts])){
                    $cday=$cdays[$dayobj->uts]; // get calendar day object from facility
                } else {
                    $cday=false;
                }
                
                // get am pm details
                $attam=false;
                $attpm=false;
                $amclass='';
                $pmclass='';
                if (isset($rollcall[$dayobj->uts])){
                    $att=$rollcall[$dayobj->uts];
                    $attam=isset($att['am']) ? $att['am'] : false;
                    $attpm=isset($att['pm']) ? $att['pm'] : false;                  
                }
                
                // make sure amclass is populated if mark exists or am period is less than current time           
                if ($attam){
                    $amclass=$attam->attribtype;
                } else {                    
                    if ($dayobj->uts<$toduts || $dayobj->uts==$toduts && $rctime->todayam<time()){
                        $amclass='Present';        
                    }
                }
                                
                // make sure pmclass is populated if mark exists or pm period is less than current time           
                if ($attpm){
                    $pmclass=$attpm->attribtype;
                } else {
                    if ($dayobj->uts<$toduts || $dayobj->uts==$toduts && $rctime->todaypm<time()){
                        $pmclass='Present';        
                    }
                }              

                $todayclass=($day==$today['mday']) && ($name==$today['month']) ? ' day_today' : '';
                if ($cday){
                    $daytype=isset($daycodes[$cday->cattr]) ? $daycodes[$cday->cattr] : 'school';
                } else {
                    // if no calendar day in facility, set default to school unless weekend
                    if ($today['wday']=0 || $today['wday']==6){
                        $daytype='weekend';
                    } else {
                        $daytype='school';
                    }
                }
                
                // make sure weekend actually is weekend (we've had cases where the calendar has been entered wrong!)
                if ($daytype=='weekend'){
                    if (!$dayobj->weekend){
                        $daytype='school'; // if wrong weekend day type then just set to school
                    }
                }
                
                switch($daytype){ 
                    case 'school':
                        // GT Mod - do not apply classes to dates in the future.
                        $checktime=$cday ? strtotime($cday->cdate) : $cal->startdt; 
                        if ($checktime<=time()){
                            $output.='<div width="100%" class="' . $amclass . $todayclass. '">'.$day.'</div>'."\n";
                            $output.='<div width="100%" class="' . $pmclass . '">&nbsp;</div>'."\n";
                        } else {
                            $output.='<div width="100%" class="futuredate">'.$day.'</div>'."\n";
                        }
                        break;
                        
                    case 'weekend':
                        $output.='<div width="100%" class="weekend'.$todayclass.'" height="100%">'.$day.'<br></div>'."\n";
                        break;
                    case 'inset':
                        $output.='<div width="100%" class="holidays'.$todayclass.'" height="100%">'.$day.'<br>Inset</div>'."\n";
                        break;
                    case 'closed':
                        $output.='<div width="100%" class="holidays'.$todayclass.'" height="100%">'.$day.'<br>Closed</div>'."\n";
                        break;
                    case 'holiday':
                        $output.='<div width="100%" class="holidays'.$todayclass.'" height="100%">'.$day.'<br>Holiday</div>'."\n";
                        break;
                }                 
            }
	        $output.='</td>';	         
	    }
	    // fill in day gaps
	    if ($d<7){
	        for ($dg=$d; $dg<7; $dg++){
            $output.='<td>';
            $output.='<div width="100%" class="noday"></div>';
            $output.='</td>';	            
	        }
	    }
	    
	    $output.='</tr>';
	}
	
	$output.="</table>";
	$Block = new Block(get_string('attCalendar','block_mis') ,"attCalendar","",$output);
	$output = $Block->draw();
	echo $output;
?>
