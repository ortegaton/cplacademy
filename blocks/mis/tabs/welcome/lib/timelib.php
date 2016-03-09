<?php

class mis_weltime {
    
    /**
     * 
     * @param integer $time
     * @param integer $years
     * @param integer $months
     * @param integer $days
     * @param integer $hours
     * @param integer $minutes
     * @param integer $seconds
     * @return integer UTS
     */
    static function adjust_time($time, $years=0, $months=0, $days=0, $hours=0, $minutes=0, $seconds=0){
        $y=date('Y', $time);
        $m=date('n',$time);
        $d=date('j',$time);
        $th=date('G', $time);
        $tm=intval(date('i', $time));
        $ts=intval(date('s', $time));
        
        // adjust years
        $y+=$years;
        
        // adjust months - deal with month adjustment affecting years
        if (($m+$months)>12){
            $y+=intval($months/12);
            $m=(($months/12)-intval($months/12))*12;
        } else if (($m+$months>0)){
            $m+=$months;
        } else if (($m+$months<=0)){                  
            $pmonths=$months*-1; // positive representation of negative months
            $y-=ceil($pmonths/12);
            $m=12-($pmonths-1);
        }

        // get days in month for adjusted year and month
        $nds=date('t', mktime(0,0,0,$m,1,$y));
        
        // if day is greater than number of days for adjusted year and month then set day to 1 and increment month by 1
        if ($d>$nds){
            $d=1;
            $m++;
            // if month is greater than 12 then reset to 1 and increment year
            if ($m>12){
                $m=1;
                $y++;   
            }
        }
        
        // create time based on years and months
        $time=mktime($th,$tm,$ts,$m,$d,$y);
        
        // adjust days
        $time+=$days*(24*60*60);
        
        // adjust hours
        $time+=$hours*(60*60);
        
        // adjust minutes
        $time+=$minutes*60;
        
        // adjust seconds
        $time+=$seconds;
        
        return ($time);
    }
    
    /**
     * get first day of month
     * @param integer $time - UTS
     * @return integer UTS
     */
    static function first_of_month($time=false){
        $time=$time ? $time : time();
        return (mktime(0,0,0,date('n',$time),1,date('Y',$time)));        
    }
    
    /**
     * get last date of month
     * @param integer $time - UTS
     * @return integer UTS
     */
    function last_of_month($time=false){
        $time=$time ? $time : time();
        return (mktime(0,0,0,date('n',$time),date('t',$time),date('Y',$time)));         
    }
    
    /**
     * Get last valid date of month
     * @param boolean|integer $month;
     * @param boolean|integer $year;
     * @return integer uts
     */	
    function last_date_of_month($month=false, $year=false){
        $month=$month===false ? date('m') : $month;
        $year=$year===false ? date('Y') : $year;        
        $time=mktime(0,0,0,$month,1,$year);
        $lastday=intval(date('t', $time));
        $time=mktime(0,0,0,$month,$lastday,$year);
        return ($time);
    }
    
    /**
     * Get monday of week as unix time value
     * @param boolean|integer $tm
     * @return boolean|integer - uts or false on fail
     */
    function mon_of_week($tm=false){
        $tm=$tm===false ? time() : $tm;
        $day=strtolower(date('D', $tm));
        if ($day=='mon'){
            return ($tm);
        }        
        while ($day!='mon'){
            $tm-=60*60*24; // decrement time by 1 day
            $day=strtolower(date('D', $tm));
            if ($day=='mon'){
                return ($tm); // first monday returned
            }
        }
        return (false);
    }
    
    /**
     * Get first monday of current month (as time)
     * @param boolean|integer $month;
     * @param boolean|integer $year;
     * @return integer uts 
     */
    function first_mon_of_month($month=false, $year=false){
        $month=$month===false ? date('m') : $month;
        $year=$year===false ? date('Y') : $year;        
        $em=mis_time::last_date_of_month($month, $year); // end of month
        $tm=$em; // set time to end of month
                
        $fmon=0; // first monday in month (unix time stamp)        
        // get first monday of this month        
        while (date('m',$tm)==$month){
            $cday=strtolower(date('D', $tm));
            if ($cday=='mon'){
                $fmon=$tm;
            }
            $tm -=60*60*24; // decrement time by 1 day
        }
        if ($fmon==0){
            return (false);
        }
        return ($fmon);
    }
	
	/**
	 * Convert yyyy-mm-dd date string to time
	 * @param string $str - string to be converted
	 * @param string $sep - optional string to use as separator (default is -)
	 * @return integer unix time
	 */
	static function strtotime_yyyymmdd($str, $sep='-'){
		$darr=explode('-', $str);
		return (mktime (0, 0, 0, $darr[1], $darr[2], $darr[0]));
	}
	
	/**
	 * Convert unix time stamp to date as specified in config preferences
	 */
	static function formatdate($time){
		global $CFG;
		$dateformat=isset($CFG->mis->dateformat) ? $CFG->mis->dateformat : 'd/m/Y';
		return (date($dateformat, $time));
	}
	
	/**
	 * convert facility cmis decimal time to object
	 * @param integer $dectime
	 * @return object
	 */
    function dectime_to_hoursmins($dectime){
        $hours = (int)($dectime/60);
        $mins = ($dectime%60);
        $mins = str_pad($mins, 2, "0", STR_PAD_RIGHT); //make sure integers are converted to double figure strings
        return ((object) array('hours'=>$hours, 'mins'=>$mins, 'minutes'=>$mins));
    }	
	
}

?>