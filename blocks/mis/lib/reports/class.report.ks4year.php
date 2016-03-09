<?php

//
// Author: Guy Thomas
// Date:2007 - 10 - 30
// Purpose: ks4 totals for an entire year group
//

// Set memory limit to 256MBytes - this is essential or script will fail.
ini_set('memory_limit', '256M');

// Set time out to 20 minutes
set_time_limit(1200);


// class.report.super.php provides ../cfg , lib_results.php, class.propsbyparam.php
require_once("class.report.super.php");
require_once("class.results.courseyear.php");
require_once("lib_facility_db.php");


//
// Class report_ks4year
//
class report_ks4year extends superreport{

    //
    // Mandatory public properties (passed on construction)
    //
    var $year;
      
    //
    // Protected properties
    //
    protected $_reportName='report_ks4year';
    protected $_examId='';
    
    function report_ks4year ($params=array(), $year){
        $this->year=$year;        
        parent::superreport($params, $year);
    }
    
    //
    // Purpose: Default columns
    //
    function setDefaultColumns(){
        $this->columns=array(
            array('code'=>'AC_P', 'title'=>'%A*-Cs'),
            array('code'=>'ACs', 'title'=>'#A*-Cs'),
            array('code'=>'5AC_P', 'title'=>'%5+A*-Cs'),
            array('code'=>'5ACs', 'title'=>'#5+A*-Cs'),
            array('code'=>'5ACs_ME', 'title'=>'#5+A*-Cs (M/E)'),
            array('code'=>'5AC_P_ME'  , 'title'=>'%5+A*-Cs (M/E)'),
            array('code'=>'AC_P_ME', 'title'=>'%A*-Cs (M/E)'),
            array('code'=>'ACs_ME', 'title'=>'#A*-Cs (M/E)'),
            array('code'=>'AG_P', 'title'=>'%A*-Gs'),
            array('code'=>'AGs', 'title'=>'#A*-Gs'),
            array('code'=>'5AG_P', 'title'=>'%5+A*-Gs'),
            array('code'=>'5AGs', 'title'=>'#5+A*-Gs'),            
            array('code'=>'totTargPoints', 'title'=>'Total Target Points'),
            array('code'=>'totLevelPoints', 'title'=>'Total Level Points')      
        );
    }
            
    //
    // Purpose: report engine - gets the data
    // This function is never directly called - it is called by the class superreport::getCacheOrBuild
    //
    protected function build(){   
        // set globals
        global $CFG, $fdata;
        
        $year=$this->year;
        $dataSets=$this->dataSets;

        // initialise variables
        $validLevels=array('a*', 'a', 'b','c', 'd', 'e', 'f', 'g');
        $numA_s=0; // number of A*s
        $numAs=0;
        $numBs=0;
        $numCs=0;        
        $numDs=0;
        $numEs=0;
        $numFs=0;
        $numGs=0;
        $numACsME=0;
        $numAssEnts=0; // number of assessments entries with a target and level        
        $totTargPoints=0;
        $totLevelPoints=0;
        $students=array(); // array of student ids hashed by id (just to count inique students)
        $stuACs=array(); // array of students hashed by id number with the number of A*Cs counted against their name
        $stumaAC=array(); // array of students hashed by id number with mass pass at A*to C as a 1 else 0
        $stueaAC=array(); // array of students hashed by id number with english pass at A*to C as a 1 else 0
        $stuAGs=array(); // array of students hashed by id number with the number of A*Gs counted against their name      
        $this->_examId='Year'.$year;
        $examIds=array($this->_examId);
        
        // If dataSets not specified (empty) then use config current dataset
        if (empty($dataSets)){
            $dataSets=array($CFG->mis->cmisDataSet);
        }

        // connect to Facility database
        $fdata=new facilityData();
        

        // Get criteria
        $ks4criteria=CriteriaForAssessment('ks4Assess');
        $ks4critPVs=AssessmentCriteriaPoints('ks4Assess');

        // Get map vals
        $sumMV=$ks4criteria['bylabel']['sum']['mapvalue'];
        $sprMV=$ks4criteria['bylabel']['spr']['mapvalue'];
        $autMV=$ks4criteria['bylabel']['aut']['mapvalue'];
        $targMV=$ks4criteria['bylabel']['target']['mapvalue'];

        
        $resParams=array(
            'anyDataSet'=>$this->anyDataSet,
            'dataSets'=>$dataSets,
            'examIds'=>$examIds,
            'anyDSCriteriaInc'=>$this->anyDSCriteriaInc,
            'anyDSCriteriaExc'=>$this->anyDSCriteriaExc,
            'debugOutToScreen'=>false
        );
        
        $yres=new results_yeargroup($year, $resParams);
        
        $results=$yres->getAllResultsByExam();
        foreach ($results as $exam=>$row){
            foreach ($row as $rowItem){
                   
                /* Necessary for debugging only
                $critStr='';    
                if ($rowItem['criteriaarray']){
                    foreach ($rowItem['criteriaarray'] as $mv=>$crit){
                        $critStr.=$critStr!='' ? ' ' : '';
                        $critStr.='MAPVAL='.$mv.' LAB='.$crit['label'].' VAL='.$crit['val'].' PTS='.$crit['pts'];
                    }
                }
                */
                
                $critArr=$rowItem['criteriaarray'];
                $modId=strtolower($rowItem['moduleid']);
                $studentId=$rowItem['studentid'];
                                
                
                // is this a short course, if so set course value to half otherwise full
                if (in_array($modId, $CFG->mis->GCSE_ShortCourses)){
                    $courseVal=0.5;
                } else {
                    $courseVal=1;
                }
                
                // Get most recent level
                unset($curLevel);
                if (isset($critArr[$sumMV])){
                    $curLevel=$critArr[$sumMV];
                } else if (isset($critArr[$sprMV])){
                    $curLevel=$critArr[$sprMV];
                } else if (isset($critArr[$autMV])){
                    $curLevel=$critArr[$autMV];
                }
                
                // Only process assessment entries (exams) that have both a level and target AND level is a ks4 level
                if (isset($curLevel) && in_array(strtolower($curLevel['val']), $validLevels) && isset($critArr[$targMV])){
                
                    // force lower case
                    $curLevel['val']=strtolower($curLevel['val']);
                
                    // increment number of assessment entries (exams)
                    $numAssEnts++;                   

                    // Add student to list of students with valid target and grade.
                    $students[$studentId]=$studentId;

                    switch ($curLevel['val']){
                        case 'a*': $numA_s+=$courseVal; break;
                        case 'a': $numAs+=$courseVal; break;
                        case 'b': $numBs+=$courseVal; break;
                        case 'c': $numCs+=$courseVal; break;
                        case 'd': $numDs+=$courseVal; break;
                        case 'e': $numEs+=$courseVal; break;
                        case 'f': $numFs+=$courseVal; break;
                        case 'g': $numGs+=$courseVal; break;
                    }
                    
                    // Add A*Cs to students
                    if (ord($curLevel['val'])<=ord('c')){
                        // make sure $stuACs has a value
                        $stuACs[$studentId]=isset($stuACs[$studentId]) ? $stuACs[$studentId] : 0;
                        // increment value by courseVal
                        $stuACs[$studentId]+=$courseVal;
                            
                        // register AC english pass
                        if ($modId=='ea'){
                            $stueaAC[$studentId]=1;
                        }                  

                        // register AC maths pass
                        if ($modId=='ma'){
                            $stumaAC[$studentId]=1;
                        }
                        
                    }
                    
                    // Add A*Gs to students
                    if (ord($curLevel['val'])<=ord('g')){
                        // make sure $stuAGs has a value
                        $stuAGs[$studentId]=isset($stuAGs[$studentId]) ? $stuAGs[$studentId] : 0;
                        // increment value by courseVal
                        $stuAGs[$studentId]+=$courseVal;
                    }                    
                    

                    
                    if ($modId=='ea' or $modId=='ma'){
                        if ($curLevel['val']=='a*'){
                            $numACsME++;
                        } else if ($curLevel['val']=='a') {
                            $numACsME++;
                        } else if ($curLevel['val']=='b') {
                            $numACsME++;
                        } else if ($curLevel['val']=='c') {
                            $numACsME++;
                        }
                    }
                    
                    // Add target points to total
                    $totTargPoints+=$critArr[$targMV]['pts'];
                    
                    // Add level points to total
                    $totLevelPoints+=$curLevel['pts'];
                } else {
                    //echo ('<span>No level - '.$critStr.', '.$rowItem['StudentId'].', '.$rowItem['ExamId'].', '.$rowItem['ModuleId'].'</span><br />');            
                }

                //echo ('<p>'.$rowItem['ExamId'].' '.$rowItem['StudentId'].' '.$rowItem['ModuleId'].' '.var_export($critArr[$targMV]).'</p>');
            }
        }

        $AC_P=$numAssEnts==0 ? 0 : round(((($numA_s+$numAs+$numBs+$numCs)/$numAssEnts)*100),2);
        $AG_P=$numAssEnts==0 ? 0 : round(((($numA_s+$numAs+$numBs+$numCs+$numDs+$numEs+$numFs+$numGs)/$numAssEnts)*100),2);
        $ACsME_P=$numAssEnts==0 ? 0 : round((($numACsME/$numAssEnts)*100),2);
        $A_P=$numAssEnts==0 ? 0 : round((($numAs/$numAssEnts)*100),2);
        $B_P=$numAssEnts==0 ? 0 : round((($numBs/$numAssEnts)*100),2);
        $C_P=$numAssEnts==0 ? 0 : round((($numCs/$numAssEnts)*100),2);
        $D_P=$numAssEnts==0 ? 0 : round((($numDs/$numAssEnts)*100),2);
        $E_P=$numAssEnts==0 ? 0 : round((($numEs/$numAssEnts)*100),2);
        $F_P=$numAssEnts==0 ? 0 : round((($numFs/$numAssEnts)*100),2);
        $G_P=$numAssEnts==0 ? 0 : round((($numGs/$numAssEnts)*100),2);
        
        // Total number of students with 5+ A*Cs   &&  5+ A*Cs inc maths and english
        $ACs5=0;
        $ACs5ME=0;        
        foreach ($stuACs as $stuId=>$stres){
            if ($stres>=5){
                $ACs5++;
                // If student has maths and english A*C then add to count of 5 A*Cs+ including maths and english
                if (isset($stumaAC[$stuId])){
                    if ($stumaAC[$stuId]==1){
                        if (isset($stueaAC[$stuId])){
                            if ($stueaAC[$stuId]){
                                $ACs5ME++;
                            }
                        }
                    }
                }
            }            
        }
        
        // Total number of students with 5+ A*Cs
        $AGs5=0;
        foreach ($stuAGs as $stres){
            if ($stres>=5){
                $AGs5++;
            }
        }
        
        $AC_P5=count($students)==0 ? 0 : round((($ACs5/count($students))*100),2);
        $AC_P_ME5=count($students)==0 ? 0 : round((($ACs5ME/count($students))*100),2);
        $AG_P5=count($students)==0 ? 0 : round((($AGs5/count($students))*100),2);

        $reportdata=array(
            'numAssEnts'    => array('val'=>$numAssEnts),
            'AC_P'          => array('val'=>$AC_P),
            'ACs'           => array('val'=>($numA_s+$numAs+$numBs+$numCs)),
            '5AC_P'         => array('val'=>$AC_P5),
            '5ACs'          => array('val'=>$ACs5),
            '5ACs_ME'       => array('val'=>$ACs5ME),
            '5AC_P_ME'      => array('val'=>$AC_P_ME5),
            '5AG_P'         => array('val'=>$AG_P5),
            '5AGs'          => array('val'=>$AGs5),            
            'AC_P_ME'       => array('val'=>$ACsME_P),
            'ACs_ME'        => array('val'=>$numACsME),
            'AG_P'          => array('val'=>$AG_P),
            'AGs'           => array('val'=>($numA_s+$numAs+$numBs+$numCs+$numDs+$numEs+$numFs+$numGs)),
            'A*s'           => array('val'=>($numA_s)),
            'As'            => array('val'=>($numAs)),
            'Bs'            => array('val'=>($numBs)),
            'Cs'            => array('val'=>($numCs)),
            'Ds'            => array('val'=>($numDs)),
            'Es'            => array('val'=>($numEs)),
            'Fs'            => array('val'=>($numFs)),
            'Gs'            => array('val'=>($numGs)),
            'totTargPoints' => array('val'=>$totTargPoints),
            'totLevelPoints'=> array('val'=>$totLevelPoints)
        );
            
        $this->_outputArray=array($this->_examId=>$reportdata);
        return ($this->_outputArray);
    }    
    
}
?>