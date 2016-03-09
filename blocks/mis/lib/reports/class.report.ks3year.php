<?php

//
// Author: Guy Thomas
// Date:2007 - 10 - 30
// Purpose: KS3 totals for an entire year group
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
// Class report_ks3year
//
class report_ks3year extends superreport{

    //
    // Mandatory public properties (passed on construction)
    //
    var $year;
   
    //
    // Protected properties
    //
    protected $_reportName='report_ks3year';
    protected $_examId='';
    
    //
    // Purpose: Class contructor
    // In: $params - Parameters (always first)
    //     $year - Year group
    //
    function report_ks3year ($params=array(), $year){
        $this->year=$year;
        parent::superreport($params, $year);
    }
    
    //
    // Purpose: Setup columns for this report
    //
    function setDefaultColumns(){
        $detailAtts=array('style'=>'display:none;', 'class'=>'extraDetail');
        $this->columns=array(
            array('code'=>'l2Perc', 'title'=>'%L2'),
            array('code'=>'l2s', 'title'=>'#L2', 'colatts'=>$detailAtts),
            array('code'=>'l3Perc', 'title'=>'%L3'),
            array('code'=>'l3s', 'title'=>'#L3', 'colatts'=>$detailAtts),
            array('code'=>'l4Perc', 'title'=>'%L4'),
            array('code'=>'l4s', 'title'=>'#L4', 'colatts'=>$detailAtts),
            array('code'=>'l5Perc', 'title'=>'%L5'),
            array('code'=>'l5s', 'title'=>'#L5', 'colatts'=>$detailAtts),
            array('code'=>'l6Perc', 'title'=>'%L6'),
            array('code'=>'l6s', 'title'=>'#L6', 'colatts'=>$detailAtts),
            array('code'=>'l7Perc', 'title'=>'%L7'),
            array('code'=>'l7s', 'title'=>'#L7', 'colatts'=>$detailAtts),
            array('code'=>'l8Perc', 'title'=>'%L8'),
            array('code'=>'l8s', 'title'=>'#L8', 'colatts'=>$detailAtts),
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
        $validLevels=array('8a', '8b', '8c','7a', '7b', '7c', '6a', '6b', '6c', '5a', '5b', '5c', '4a', '4b', '4c', '3a', '3b', '3c', '2a', '2b', '2c', '1a', '1b', '1c');
        $numL1s=0;
        $numL2s=0;
        $numL3s=0;
        $numL4s=0;
        $numL5s=0;
        $numL6s=0;
        $numL7s=0;
        $numL8s=0;
        $numAssEnts=0; // number of assessments entries with a target and level
        $totTargPoints=0;
        $totLevelPoints=0;
        $this->_examId='Year'.$year;
        $examIds=array($this->_examId);
        
        // If dataSets not specified (empty) then use config current dataset
        if (empty($dataSets)){
            $dataSets=array($CFG->mis->cmisDataSet);
        }

        // connect to Facility database
        $fdata=new facilityData();
        

        // Get criteria
        $ks3criteria=CriteriaForAssessment('KS3Assess');
        $ks3critPVs=AssessmentCriteriaPoints('KS3Assess');

        // Get map vals
        $sumMV=$ks3criteria['bylabel']['sum']['mapvalue'];
        $sprMV=$ks3criteria['bylabel']['spr']['mapvalue'];
        $autMV=$ks3criteria['bylabel']['aut']['mapvalue'];
        $targMV=$ks3criteria['bylabel']['target']['mapvalue'];

        // result parameters
        $resParams=array('anyDataSet'=>$this->anyDataSet, 'dataSets'=>$this->dataSets, 'examIds'=>$examIds);
        
        // create results object
        $yres=new results_yeargroup($year, $resParams);
        
        $results=$yres->getAllResultsByExam();
        
        foreach ($results as $exam=>$row){
            foreach ($row as $rowItem){
                   
                /* Necessary for debugging only*/
                /*
                $critStr='';    
                if ($rowItem['criteriaarray']){
                    foreach ($rowItem['criteriaarray'] as $mv=>$crit){
                        $critStr.=$critStr!='' ? ' ' : '';
                        $critStr.='MAPVAL='.$mv.' LAB='.$crit['label'].' VAL='.$crit['val'].' PTS='.$crit['pts'];
                    }
                }
                */
                
                $critArr=$rowItem['criteriaarray'];
                
                // Get most recent level
                unset($curLevel);
                if (isset($critArr[$sumMV])){
                    $curLevel=$critArr[$sumMV];
                } else if (isset($critArr[$sprMV])){
                    $curLevel=$critArr[$sprMV];
                } else if (isset($critArr[$autMV])){
                    $curLevel=$critArr[$autMV];
                }
                
                // Only process assessment entries (exams) that have both a level and target AND level is a KS3 level
                if (isset($curLevel) && in_array($curLevel['val'], $validLevels) && isset($critArr[$targMV])){ 
                    // increment number of assessment entries (exams)
                    $numAssEnts++;
                    
                    //echo ('<p> target val is '.$critArr[$targMV]['val'].' and points is '.$critArr[$targMV]['pts']);
                    
                    // Increment appropriate level count
                    $levelChar=substr($curLevel['val'],0,1);
                    //echo ('<span>'.$curLevel['val'].', '.$rowItem['StudentId'].', '.$rowItem['ExamId'].', '.$rowItem['ModuleId'].'</span><br />');
                    switch ($levelChar){
                        case '1': $numL1s++; break;
                        case '2': $numL2s++; break;
                        case '3': $numL3s++; break;
                        case '4': $numL4s++; break;
                        case '5': $numL5s++; break;
                        case '6': $numL6s++; break;
                        case '7': $numL7s++; break;
                        case '8': $numL8s++; break;
                    }
                    
                    // Add target points to total
                    $totTargPoints+=$critArr[$targMV]['pts'];
                    
                    // Add level points to total
                    $totLevelPoints+=$curLevel['pts'];
                } else {
                    //echo ('<span>No level - '.$critStr.', '.$rowItem['studentid'].', '.$rowItem['examid'].', '.$rowItem['moduleid'].'</span><br />');            
                }

                //echo ('<p>'.$rowItem['examid'].' '.$rowItem['studentid'].' '.$rowItem['moduleid'].' '.var_export($critArr[$targMV]).'</p>');
            }
        }

        $l1perc=$numAssEnts==0 ? 0 : round((($numL1s/$numAssEnts)*100),2);
        $l2perc=$numAssEnts==0 ? 0 : round((($numL2s/$numAssEnts)*100),2);
        $l3perc=$numAssEnts==0 ? 0 : round((($numL3s/$numAssEnts)*100),2);
        $l4perc=$numAssEnts==0 ? 0 : round((($numL4s/$numAssEnts)*100),2);
        $l5perc=$numAssEnts==0 ? 0 : round((($numL5s/$numAssEnts)*100),2);
        $l6perc=$numAssEnts==0 ? 0 : round((($numL6s/$numAssEnts)*100),2);
        $l7perc=$numAssEnts==0 ? 0 : round((($numL7s/$numAssEnts)*100),2);
        $l8perc=$numAssEnts==0 ? 0 : round((($numL8s/$numAssEnts)*100),2);

        $reportdata=array(
            'numAssEnts'    => array('val'=>$numAssEnts),
            'l1s'           => array('val'=>$numL1s),
            'l1Perc'        => array('val'=>$l1perc),
            'l2s'           => array('val'=>$numL2s),
            'l2Perc'        => array('val'=>$l2perc),
            'l3s'           => array('val'=>$numL3s),
            'l3Perc'        => array('val'=>$l3perc),
            'l4s'           => array('val'=>$numL4s),
            'l4Perc'        => array('val'=>$l4perc),
            'l5s'           => array('val'=>$numL5s),
            'l5Perc'        => array('val'=>$l5perc),
            'l6s'           => array('val'=>$numL6s),
            'l6Perc'        => array('val'=>$l6perc),
            'l7s'           => array('val'=>$numL7s),
            'l7Perc'        => array('val'=>$l7perc),
            'l8s'           => array('val'=>$numL8s),
            'l8Perc'        => array('val'=>$l8perc),
            'totTargPoints' => array('val'=>$totTargPoints),
            'totLevelPoints'=> array('val'=>$totLevelPoints)
        );
            
        $this->_outputArray=array($this->_examId=>$reportdata);
        return ($this->_outputArray);
    }    
    
}
?>