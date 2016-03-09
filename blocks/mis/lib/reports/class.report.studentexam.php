<?php

//
// Author: Guy Thomas
// Date:2008 - 04 - 09
// Purpose: Get a table of data for a specific student and exam
//

// Set memory limit to 256MBytes - this is essential or script will fail.
ini_set('memory_limit', '256M');

// Set time out to 20 minutes
set_time_limit(1200);


// class.report.super.php provides ../cfg , lib_results.php, class.propsbyparam.php
require_once("class.report.super.php");
require_once("class.results.student.php");
require_once($CFG->dirroot.'/blocks/mis/lib/lib_facility_db.php');
require_once($CFG->dirroot.'/blocks/mis/lib/moodledb.php');


//
// Class report_studentexam
//
class report_studentexam extends superreport{

    //
    // Mandatory public properties (passed on construction)
    //
    var $studentid;
    var $examid;
    var $rowcatColumnTitle='Module';
   
    //
    // Protected properties
    //
    protected $_reportName='report_studentexam';
    
    //
    // Purpose: Class contructor
    // In: $params - Parameters (always first)
    //     $studentid - admin number of student
    //     $examid  - examid (lower case)
    //
    function report_studentexam ($params=array(), $studentid, $examid){
        $this->studentid=$studentid;        
        $this->examid=$examid;
        if (!isset($params['reportTitle'])){
            $params['reportTitle']='Student '.$studentid.' results for '.$examid;
        }
        parent::superreport($params, $studentid, $examid);
    }
    
    //
    // Purpose: Setup columns for this report
    //
    function setDefaultColumns(){
        // Set columns to empty arrau - columns are set up according to columns in exam   (during build)     
        $this->columns=array();  
    }
            
    //
    // Purpose: report engine - gets the data
    // This function is never directly called - it is called by the class superreport::getCacheOrBuild
    //
    protected function build(){   
        // set globals
        global $CFG, $fdata;
                
        // Get student results
        $results=new results_students($this->studentid, array('getComments'=>true, 'valsToDescs'=>true, 'examIds'=>array($this->examid), 'debugOutToScreen'=>$CFG->mis->debug, 'anyDataSet'=>true, 'dataSets'=>$this->dataSets));        
        $rs=$results->getAllResultsByExam();
        
        // get criteria used in results
        $criteria=$results->getCriteriaByExamId($this->examid);
        
        // get assessment id for this exam
        $assIds=$results->getAssessIdsByExamIds();
        $assId=$assIds[$this->examid];
        
        // create columns array from criteria
        foreach ($criteria['bylabel'] as $crit){
            // check to see if this column should be displayed according to config
            $dispcol=true; // default is to display if no config set

            $critconf=db_mis::get_assessment_criteria($this->primarySet, $assId, $crit['critlabel']);
            if ($critconf){
                if (isset($critconf->display)){
                    if ($critconf->display==0){
                        $dispcol=false;
                    }
                }
            }
            
            // only display column if config has not specified it as hidden
            if ($dispcol){
                $this->columns[]=array('code'=>$crit['mapvalue'], 'title'=>$crit['critlabeldisp']);
            }
        }        
        
        // get array of modules (hashed by module id)
        $modules=getModules();
        
        // create report data array
        $reportdata=array();               
        foreach ($rs as $exam=>$row){
            foreach ($row as $rowItem){            
                // Go through criteria and create table cells
                $rowarray=array(); // stores cells for this particular row
                foreach ($criteria['bylabel'] as $crit){
                    if (isset($rowItem['criteriaarray'][$crit['mapvalue']]['val'])){
                        $celval=$rowItem['criteriaarray'][$crit['mapvalue']]['val'];
                    } else {
                        $celval='';
                    }
                    // reportdata is hashed by moduleid as a row category
                    $module=$modules[$rowItem['moduleid']];
                    $rowarray[$crit['mapvalue']]=array('val'=>$celval);
                }
                $reportdata[$module->name]=$rowarray;
            }
        }
                
                
        $this->_outputArray=$reportdata;
        return ($this->_outputArray);
    }    
    
}
?>