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
     */


    /**
     * Calculates whether supplied number is odd or even
     *
     * @param int $num required - number to check
     * @return boolean 
     */
    function isOdd($num){
        if ($num & 1) {
            $isOdd = true;
        } else {
            $isOdd = false;
        }
        return $isOdd;
    }


    /**
     * Converts a moodle user id to a students UPN to be used with facility
     *
     * @uses $CFG
     * @param int $mUserID required - moodle userid  
     * @return string - students UPN (found in UKSTUSATS)
     */
    function getStuUPN($mUserId){ 
        global $CFG, $fdata, $DB;
        // GT MOD - if student id type is UPN then simply return idnumber from moodle, else get the upn from facility
        $idnumber = $DB->get_field_sql('SELECT idnumber FROM {user} WHERE id= ? ',array($mUserId));
        if ($idnumber){
            if ($CFG->mis->stu_unidtype==STU_UNID_UPN){
                return ($idnumber);            
            } else {
                // stuId is an admin number (studentid), so get the students upn from Facility
                return ($fdata->getStuUpn($idnumber));
            }
        }else{
            echo "No UPN Found";
        }
    }

    /**
     * Checks to see if supplied date is a school day  - IE exists in Facility ATTCALENDAR table.
     *
     * @uses $fdata - data object allowing facility db connection
     * @uses $ftm_cfg - block config vars
     * @param string $dayDate required - date to query 
     * @return object - status array of objects 
     */
    function isSchoolDay($dayDate){
        global $CFG, $fdata;
        $chkSdSQL  = 'SELECT CAttr FROM '.$fdata->prefix.'attcalendar WHERE CDate = \'' . $dayDate . '\'';
        $chkSdSQL .= ' AND setid = \'' . $CFG->mis->cmisDataSet .'\';';
        $status = $fdata->getFieldValue($chkSdSQL,"CAttr");
        return $status; 
    }


    /**
     * Checks to see if a role call was taken on a specified date for a specific role call and for a sepcified user.
     *
     * @uses $fdata - data object allowing facility db connection
     * @uses $ftm_cfg - block config vars
     * @param string $rcDate required - date to check for role call 
     * @param string $RC required - role call to check for eg "AM" or "PM"
     * @param string $StuUPN required - student UPN to calculate Tutor group
     * @return int $recNum
     */
    function rcTaken($rcDate,$RC,$stuUPN){
        global $CFG, $fdata;
        if ($RC == 'AM'){
            $chkRCSQL ='(agr.RollCall1)=\'' .  $RC .'\')'; 
        }else{
            $chkRCSQL ='(agr.RollCall2)=\'' .  $RC .'\')'; 
        }
        //horrible way of checking for AM/PM
        $sql = 'SELECT agr.RecordNum';
        $sql .=' FROM '.$fdata->prefix.'attgrouprolls AS agr INNER JOIN ('.$fdata->prefix.'ukstustats AS uks INNER JOIN '.$fdata->prefix.'students AS st ON uks.StudentId = st.StudentId) ON agr.ClassGroupId = st.ClassGroupId';
        $sql .=' WHERE (((agr.AttDate)=\'' . $rcDate . '\') AND (' . $chkRCSQL . ' AND ((uks.UniqueNum)=\'' . $stuUPN . '\') AND ((uks.SetId)=\'' . $CFG->mis->cmisDataSet .'\') AND ((agr.SetId)=\'' . $CFG->mis->cmisDataSet .'\') AND ((st.SetId)=\'' . $CFG->mis->cmisDataSet .'\'));';
        $recNum = $fdata->doQuery($sql);
        return $recNum;
    }
    
    
    /**
     * Get the attendance status for a given date
     *
     * @uses $fdata - data object allowing facility db connection
     * @uses $ftm_cfg - block config vars
     * @param string $rcDate required - date to check for role call       
     * @param string $StuUPN required - student UPN to calculate Tutor group
     * @return object
     */
    function getAttStatus($rcDate, $stuUPN){
        global $CFG, $fdata;
        $sql = 'SELECT act.Code, act.Name, act.Descrip, ad.StartTime, ad.FinishTime, ag.AttDate';
        $sql .= ' FROM '.$fdata->prefix.'attgrouprolls AS ag INNER JOIN ('.$fdata->prefix.'ukstustats as us INNER JOIN ('.$fdata->prefix.'attdetail AS ad INNER JOIN '.$fdata->prefix.'attcatagory AS act ON ad.DAttr = act.Code) ON us.StudentId = ad.StuId) ON ag.AttDate = ad.CDate';
        $sql .= ' GROUP BY us.StudentId, act.Code, act.Name, act.Descrip, ad.StartTime, ad.FinishTime, ag.AttDate, us.UniqueNum, ad.SetId, act.SetId, us.SetId, ag.SetId';
        $sql .= ' HAVING (((ag.AttDate)=\'' . $rcDate . '\') AND ((us.UniqueNum)=\'' . $stuUPN . '\') AND ((ad.SetId)=\'' . $CFG->mis->cmisDataSet .'\') AND ((act.SetId)=\'' . $CFG->mis->cmisDataSet .'\') AND ((us.SetId)=\'' . $CFG->mis->cmisDataSet .'\') AND ((ag.SetId)=\'' . $CFG->mis->cmisDataSet .'\'));';
        $attDetails = $fdata->getRowValues($sql);
        return $attDetails;
    }


    /**
     * Gets an array of dates that are school days for the supplied month
     *
     * @uses $fdata - data object allowing facility db connection
     * @uses $ftm_cfg - block config vars
     * @param int $month required - number of month without preceeding '0' 
     * @param int $year required - full year '2008' 
     * @return object - $schoolDays array of school day objects
     */
    function getSchoolDays($month,$year){
        global $CFG, $fdata;
        // GT MOD - get school day codes and apply to sql
        $daycodes=getConfDayCodes();  
        $schdayssql='';
        foreach ($daycodes as $dcode=>$val){
            if ($val=='school'){
                $schdayssql.=$schdayssql=='' ? '' : ' OR ';
                $schdayssql.='aca.CAttr=\''.$dcode.'\'';
            }
        }
        $sql ='SELECT aca.CDate, aca.CAttr FROM '.$fdata->prefix.'attcalendar AS aca';
        $sql .=' GROUP BY aca.CDate, aca.CAttr, Month([CDate]), aca.SetId';
        $sql .=' HAVING (('.$schdayssql.') AND ((Month([CDate]))=' . $month . ') AND ((Year([CDate]))=' . $year . ') AND ((aca.SetId)=\'' . $CFG->mis->cmisDataSet .'\'));';
        $schoolDays = $fdata->doQuery($sql);
        return $schoolDays;    
    }
    
    /*
     * @param int $month required - number of month without preceeding '0' 
     * @param int $year required - full year '2008' 
     * @return object - array of school day records hashed by uts
     */    
    function school_days_hash_by_uts($month,$year){
        $sdays=getSchoolDays($month,$year);
        $sdaysuts=array();
        foreach ($sdays as $day){
            $utsdate=strtotime(date("Y-m-d",strtotime($day->cdate)));
            $sdaysuts[$utsdate]=$day;           
        }
        return ($sdaysuts);
    }
    
    /**
     * Gets an array of dates  for the supplied month
     *
     * @uses $fdata - data object allowing facility db connection
     * @uses $ftm_cfg - block config vars
     * @param int $month required - number of month without preceeding '0' 
     * @param int $year required - full year '2008' 
     * @return object - $schoolDays array of school day objects
     */
    function getAllDays($month,$year){
        global $CFG, $fdata;
        $sql ='SELECT CDate, CAttr FROM '.$fdata->prefix.'attcalendar';
        $sql .=' GROUP BY CDate, CAttr, Month([CDate]), SetId';
        $sql .=' HAVING (((Month([CDate]))=' . $month . ') AND ((Year([CDate]))=' . $year . ') AND ((SetId)=\'' . $CFG->mis->cmisDataSet .'\'));';
        $schoolDays = $fdata->doQuery($sql);
        return $schoolDays;    
    }
    
    /*
     * @param int $month required - number of month without preceeding '0' 
     * @param int $year required - full year '2008' 
     * @return object - array of day records hashed by uts
     */    
    function all_days_hash_by_uts($month,$year){
        $days=getAllDays($month,$year);
        $daysuts=array();
        if ($days && !empty($days)){
            foreach ($days as $day){
                $utsdate=strtotime(date("Y-m-d",strtotime($day->cdate)));
                $daysuts[$utsdate]=$day;         
            }
        }
        
        return ($daysuts);
    }    

    /**
     * Gets all the attendance details for a given month and student.
     *
     * @uses $fdata - data object allowing facility db connection
     * @uses $ftm_cfg - block config vars
     * @param int $userid required - moodle users id to be converted to stuUPN
     * @param int $month required - number of month without preceeding '0' 
     * @param int $year required - full year '2008'
     * @param boolean $incnonstatabs - GT MOD 2009040200 - if false, prevent non-statistical absences from being shown 
     * @return object - $attDetails array of absent details
     */    
    function getAttDetails($userid,$month,$year,$incnonstatabs=false){
        global $CFG, $fdata, $DB;
    
        /*
        // GT MOD 2009011600
        // THIS CODE REMOVED - SLOW IN MSSQL SERVER 2005 FOR SOME REASON
        $stuUPN = getStuUPN($userid);        
        $sql ='SELECT act.Code,act.Descrip, ad.StartTime, ad.FinishTime, ag.AttDate,ad.Attribtype, ad.Excused, act.Expl';
        $sql .=' FROM '.$fdata->prefix.'attgrouprolls AS ag INNER JOIN ('.$fdata->prefix.'ukstustats AS uks INNER JOIN ('.$fdata->prefix.'attdetail AS ad INNER JOIN '.$fdata->prefix.'attcatagory AS act ON ad.DAttr = act.Code) ON uks.StudentId = ad.StuId) ON ag.AttDate = ad.CDate';
        $sql .=' GROUP BY act.Code,act.Descrip, ad.StartTime, ad.FinishTime, ag.AttDate,ad.Attribtype, Month([AttDate]), Year([AttDate]), ad.Excused, act.Expl, uks.UniqueNum, ad.SetId, act.SetId, uks.SetId, ag.SetId';
        $sql .=' HAVING (((Month([AttDate]))=' . $month . ') AND ((Year([AttDate]))='. $year .') AND ((uks.UniqueNum)=\'' . $stuUPN . '\') AND ((ad.SetId)=\'' . $CFG->mis->cmisDataSet .'\') AND ((act.SetId)=\'' . $CFG->mis->cmisDataSet .'\') AND ((uks.SetId)=\'' . $CFG->mis->cmisDataSet .'\') AND ((ag.SetId)=\'' . $CFG->mis->cmisDataSet .'\')) ORDER BY ag.AttDate;';
        $attDetails = $fdata->doQuery($sql);
        return $attDetails;
        */
        
        $mdluser=$DB->get_record('user',array('id'=>$userid));
        $idnumber=$mdluser->idnumber;
        $stuid=$fdata->getStuAdminNo($idnumber);
		// bug fix by Martin Griffiths July 2009 - added $fdata->prefix to both attdetail and attcatagory tables
        $sql='SELECT act.code, act.statabs, act.isauth, act.descrip, ad.starttime, ad.finishtime, ad.cdate as attdate, ad.attribtype, ad.excused, act.expl FROM '.$fdata->prefix.'attdetail AS ad LEFT JOIN '.$fdata->prefix.'attcatagory AS act ON ad.dattr = act.Code WHERE ad.stuid=\''.$stuid.'\' AND ad.setid=\''.$CFG->mis->cmisDataSet.'\' AND MONTH(ad.cdate)='.$month.' AND YEAR(ad.cdate)='.$year.' AND act.setid=\''.$CFG->mis->cmisDataSet.'\'';
        
        // GT MOD 2009040200 - prevent non-statistical absences from being shown
        $sql.=$incnonstatabs ? '' : 'AND ((act.statabs=\'Y\' OR act.isauth=\'N\') OR ad.attribtype=\'L\')';
                
        $attDetails = $fdata->doQuery($sql);
        return $attDetails;        
    }

    function rollcall_hash_by_uts($userid,$month,$year){
        $rctime=getRCTime();
        $atts=getAttDetails($userid, $month, $year);
        $attdets=array();
        if ($atts){
            foreach ($atts as $att){
                $utsdate=strtotime(date("Y-m-d",strtotime($att->attdate)));
                $am=false;
                $pm=false;
                // use roll call start and end times to determine am / pm
                if ($att->starttime <= $rctime->am AND $att->finishtime >= $rctime->pm){                
                    $am = true;
                    $pm = true;
                }
                
                //handle morning reg only
                if ($att->finishtime < $rctime->pm){
                    $am = true;
                }
    
                //handle afternoon reg only
                if ($att->starttime > $rctime->am){
                    $pm = true;
                }            
                
                // add attendance detail to attendance details hashed by uts / am pm
                if ($am){
                    $attdets[$utsdate]['am']=$att;
                }
                if ($pm){
                    $attdets[$utsdate]['pm']=$att;
                }            
            }
        }
        return ($attdets);
    }
    
    /**
     * From the number of absences and lates work out the number of presents
     *
     * @uses $absAuthCount - Global var containing authorised absences
     * @uses $absNotAuthCount - Global var containing Unauthorised absences
     * @uses $lateCount - Global var containing lates
     * @param int $schoolDays required - total number of regs for month 
     * @return int - present registrations
     */
    function getPresentRegs($schoolDays){
        global $absAuthCount,$absNotAuthCount,$lateCount;
        $numSchoolDays = 2*(count($schoolDays));
        $nonPresentRegs = $absAuthCount + $absNotAuthCount + $lateCount;
        $presentRegs = $numSchoolDays - $nonPresentRegs;
        return $presentRegs;
    }


    /**
     * Modifies appropriate count based on sttDetails attribute ie Late
     *
     * @uses $absAuthCount - Global var containing authorised absences
     * @uses $absNotAuthCount - Global var containing Unauthorised absences
     * @uses $lateCount - Global var containing lates
     * @param object $attDetail required - an absence instance 
     * @param float $multiplier required - dependant on all whether event is all day or half day
     */
    function setRegStatus($attDetail,$multiplier){
        Global $absAuthCount,$absNotAuthCount,$lateCount;
            
        //Absence 
        if($attDetail->attribtype == "A"){
            if ($attDetail->excused == "Y"){
                //auth
                $absAuthCount = $absAuthCount + (1 * $multiplier);
            }else{
                //not authed
                $absNotAuthCount = $absNotAuthCount + (1 * $multiplier);
            }
        }
        //Lateness
        if($attDetail->attribtype == "L"){
            $lateCount = $lateCount + (1 * $multiplier);
        }
    }


    /**
     * GT Mod
     * Call draw functions for flashchart or imgchart
     */
    function drawAttChart(){
        global $CFG;
        $imgchart=isset($CFG->mis->imgcharts) && $CFG->mis->imgcharts;
        if ($imgchart){
            $chart = drawImgChart();
        } else {
            $chart = drawFusionChart();
        }
       return $chart;
    }

    /**
     * Draw the FusionChart based on global att vars
     *
     * @uses $presentCount - Global var containing present days
     * @uses $absAuthCount - Global var containing authorised absences
     * @uses $absNotAuthCount - Global var containing Unauthorised absences
     * @uses $lateCount - Global var containing lates
     * @param object $attDetail required - an absence instance 
     * @param float $multiplier required - dependant on all whether event is all day or half day
     */
    function drawFusionChart(){
        Global $presentCount,$absAuthCount,$absNotAuthCount,$lateCount;
        //echo "Present: " . $presentCount . "<br>Absent(authorized): " . $absAuthCount . "<br>Absent(Unauthorized): " . $absNotAuthCount . "<br>Late: " . $lateCount;
        $fcColours = array("00FF00","FF0000","0000FF","CEEF00");
        $arrData = array("Present"=>$presentCount,"Absent(Authorized)"=>$absAuthCount,"Absent(Unauthorized)" =>$absNotAuthCount,"Present(Late)" => $lateCount);
        
        //gen XML for chart 
        $strXML = "<graph caption='Attendance data for " . getMonthName() . "' numberSuffix='' formatNumberScale='0' decimalPrecision='0' showNames='1' showLegend='1'>";
        $i = 0;
        foreach ($arrData as $label=>$val){
            $strXML .= "<set name='" . $label . "'   value='" . $val . "' color='" .$fcColours[$i] . "'/>";
            $i++;
        }
        $strXML .= "</graph>\n";
        
        //render the chart
        $graphData =  renderChartHTML("lib/chart/FCF_Pie3D.swf", "", $strXML, "productSales", 400, 400); 
        return $graphData;
    }
    
    /**
     * GT Mod
     * Draw image chart
     * @uses $presentCount - Global var containing present days
     * @uses $absAuthCount - Global var containing authorised absences
     * @uses $absNotAuthCount - Global var containing Unauthorised absences
     * @uses $lateCount - Global var containing lates    
     */
    function drawImgChart(){
        global $CFG, $presentCount,$absAuthCount,$absNotAuthCount,$lateCount;
		
		$blockwww=get_mis_blockwww(); // function in urllib.php
		
        $params='data='.$presentCount.'~'.$absAuthCount.'~'.$absNotAuthCount.'~'.$lateCount.'&label=Present ('.($presentCount/2).' days)~Absent Authorised ('.($absAuthCount/2).' days)~Absent Not Authorised ('.($absNotAuthCount/2).' days)~Present(Late)';
        $graphData = '<div class="imggraph"><img src=\''.$blockwww.'/lib/3dpie/attendancebygetpost.php?'.$params.'\' alt=\'attendance_graph\'/></div>';
        return $graphData;
    }
    
    /**
     * Draw errors with passed vars
     *
     * @param string $icon required - warning images 
     * @param string $error required - error text
     * @param boolean $return required - return error html rather than output
     */
    function drawError($icon,$error,$return=false){
        //handle future months
        $output='';
        $output.="\n". '<br><br><table border="0" width="100%">';
        $output.="\n\t". '<tr>';
        $output.="\n\t\t". '<td width="25%">&nbsp;<img border="0" src="' . $icon . '"></td>';
        $output.="\n\t". '</tr>';
        $output.="\n\t". '<tr>';
        $output.="\n\t\t". '<td width="75%"><p align="center">';
        $output.="\n\t". $error;
        $output.="\n\t\t". '</td>';
        $output.="\n\t". '</tr>';
        $output.="\n". '</table>';
        if ($return){
            return ($output);
        } else {
            echo ($output);
        }
    }


    /**
     * get a month name from a number
     *
     * @return int - present registrations
     */
    function getMonthName(){
        if ($_GET['month']){
            $monthNum = $_GET['month'];
            $timestamp = mktime(0, 0, 0, $monthNum, 1, 2005);
            $month =  date("F", $timestamp);
        }else{
            $month =  date("F");
        }
        return $month;
    }

    /**
     * Format time - convert minutes from midnight to 24 hour time
     *
     * @param int $inMins required - minutes from midnight     
     * @return string - time
     */
    function formatTime($inMins){
        $hours = (int)($inMins/60);
        $mins = ($inMins%60);
        $readableMins = sprintf("%02d", $hours) . ":" . str_pad($mins, 2, "0", STR_PAD_RIGHT); // GT Mod - make sure integers are converted to double figure strings
        return $readableMins;
    }
    
    /**
     * @author: GThomas
     * Get config daycodes array and add school days to it
     * @param boolean|string $setid optional - string or false to use config setid
     * @return array (array of daycodes)
     */
    function getConfDayCodes($setid=false){
        global $CFG, $fdata;
        $setid=$setid ? $setid : $CFG->mis->cmisDataSet;
        // Get calendar types that should be attributed as school days
        $sql='SELECT * FROM '.$fdata->prefix.'attcatagory WHERE setid=\''.$setid.'\' AND dtype=\'cal\'  AND statabs=\'y\' AND expl=\'y\'';
        $rs=$fdata->doQuery($sql);        
        $dcodes=isset($CFG->mis->daycodes) ? $CFG->mis->daycodes : array();
        foreach ($rs as $row){
            $dcodes[$row->code]='school';
        }
        return ($dcodes);
    }

    /**
     * @author: GThomas
     * Get the roll call times for the specific or current dataset
     * @param boolean|string $setid optional string or false to set from config
     * @return object (object with am/pm time vals)
     */
    function getRCTime($setid=false){
        global $CFG, $fdata;
        $setid=$setid ? $setid : $CFG->mis->cmisDataSet;
        $sql='SELECT rtime FROM '.$fdata->prefix.'attrollcall WHERE setid=\''.$setid.'\' AND rcode=\'AM\'';
        $amval=$fdata->getFieldValue($sql,'rtime');
        $sql='SELECT rtime FROM '.$fdata->prefix.'attrollcall WHERE setid=\''.$setid.'\' AND rcode=\'PM\'';
        $pmval=$fdata->getFieldValue($sql,'rtime');
        $amobj=mis_time::dectime_to_hoursmins($amval);
        $pmobj=mis_time::dectime_to_hoursmins($pmval);
                
        $todayam=mktime($amobj->hours, $amobj->mins, 0, date('n'), date('j'), date('Y'));
        $todaypm=mktime($pmobj->hours, $pmobj->mins, 0, date('n'), date('j'), date('Y'));
        
        $retobj=new stdclass;
        $retobj->am=$amval; // am decimal time value
        $retobj->todayam=$todayam; // unix time stamp for todays am period
        $retobj->pm=$pmval; // pm decimal time value
        $retobj->todaypm=$todaypm; // unix time stamp for todays pm period
        return ($retobj);
    }
    
    
/**
 * builds a calendar month object for a specific time
 * @author gthomas     
 */
class calendar_month{    
    
    /**
     * UTS date used to construct object
     * @var integer
     */
    var $srctime;
    
    /**
     * first UTS date of month
     * @var integer
     */    
    var $startdt;
    
    /**
     * last UTS date of month
     * @var integer
     */
    var $enddt;
    
    /**
     * days in month
     * @var integer
     */
    var $days;
    
    /**
     * first monday (UTS date) in month
     * @var integer - UTS
     */
    var $firstmonday;
    
    /**
     * first day of month, day pos 1-7 (mon - sun) 
     * @var integer
     */
    var $firstday;
   
    /**
     * monday to sunday grid array
     * if month doesn't start on monday then first grid days will be false
     * @var array 
     */
    var $gridmonsun;
    
    /**
     * constructor
     * @param boolean|integer|string $time - unix time stamp
     * @return void
     */
    function __construct($time=false){
        $time=$time!==false ? $time : time();
        // if time was passed in as a string then convert it to UTS
        if (is_string($time)){
            $time=strtotime($time);
        }
        $this->srctime=$time;
        $this->_set_dt_props($time);
    }
    
    /**
     * set date/time properties from source time
     * @return void
     */
    private function _set_dt_props(){
        $time=$this->srctime;
        // create date array for $time
        $darr=date_parse(date('Y-m-d 00:00:00',$time));
        // set start date/time to first of month
        $this->startdt=mktime(0,0,0, intval($darr['month']),1,intval($darr['year']));
        // create date array for last day of $time
        $darr=date_parse(date('Y-m-t 00:00:00',$time));
        // set end date/time to last day of month
        $this->enddt=mktime(0,0,0, intval($darr['month']),intval($darr['day']),intval($darr['year']));
        $this->days=intval($darr['day']);
        // set first day position for month
        $this->firstday=intval(date('N',$this->startdt));
        // set first monday of month
        $this->_set_firstmonday();     
        $this->_set_gridmonsun();   
    }
    
    /**
     * set first monday of month UTS date
     * @return void
     */
    private function _set_firstmonday(){
        $sdt=$this->startdt; // start of month UTS            
        for ($d=0; $d<7; $d++){
            $chkdt=$sdt+((60*60*24)*$d); // UTS date to check to see if its a monday
            $day=strtolower(date('D',$chkdt));
            if ($day=='mon'){
                $this->firstmonday=$chkdt;
            } 
        }
    }
    
    /**
     * set monday to sunday array
     * @return void
     */
    private function _set_gridmonsun(){
        $grid=array();
        $week=1; // 1 - 6 (week grid position)
        $day=0; // 1 - 7 (day grid position 1=monday, 7=sunday)
        $offset=$this->firstday-1; // set offset to 0 based
        
        for ($d=0; $d<($this->days+$offset); $d++){
            $day++;
            if ($day==8){
                $day=1;
                $week++;
            }
            if ($d>=$offset){
                
                // idea below to increment uts date is not accurate enough!
                //$uts=strtotime(date('Y-m-d', $this->startdt+(60*60*$dayhrs*($d-$offset))));
                
                $stdate=getdate($this->startdt);
                $uts=mktime(0,0,0,$stdate['mon'],$stdate['mday']+($d-$offset),$stdate['year']);                                                
                $daytxt=strtolower(date('D', $uts));
                $weekend=$daytxt=='sat' || $daytxt=='sun';                
                $dobj=(object) array('uts'=>$uts, 'day'=>(($d-$offset)+1), 'daytxt'=>$daytxt, 'weekend'=>$weekend);
            } else {
                $dobj=false;
            }
            $grid[$week][$day]=$dobj;
        }        
        $this->gridmonsun=$grid;
    }
}
    
?>
