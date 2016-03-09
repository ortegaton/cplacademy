<?php

//
// Author: Guy Thomas
// Date:2007 - 10 - 30
// Purpose: KS3 totals for all subjects in a year group
//

// Set memory limit to 256MBytes - this is essential or script will fail.
ini_set('memory_limit', '256M');

// Set time out to 20 minutes
set_time_limit(1200);


// class.report.super.php provides ../cfg , lib_results.php, class.propsbyparam.php
require_once("class.report.super.php");
require_once("class.results.courseyear.php");
require_once("lib_facility_db.php");
require_once("lib_array.php");


//
// Class report_ks3yearsubject
//
class report_ks3yearsubject extends superreport{

    //
    // Mandatory public properties (passed on construction)
    //
    var $year;
   
    //
    // Protected properties
    //
    protected $_reportName='report_ks3yearsubject';
    protected $_examId='';
    protected $_subjectArr=array();
    
    //
    // Purpose: Class contructor
    //
    function report_ks3yearsubject ($params=array(), $year){
        $this->year=$year;
        parent::superreport($params, $year);
    }
    
    //
    // Purpose: Setup columns for this report
    //
    function setDefaultColumns(){
        $detailAtts=array('style'=>'display:none;', 'class'=>'extraDetail');
        $this->columns=array(
            'l2Perc'=>array('code'=>'l2Perc', 'title'=>'%L2'),
            'l2s'=>array('code'=>'l2s', 'title'=>'#L2', 'colatts'=>$detailAtts),
            'l3Perc'=>array('code'=>'l3Perc', 'title'=>'%L3'),
            'l3s'=>array('code'=>'l3s', 'title'=>'#L3', 'colatts'=>$detailAtts),
            'l4Perc'=>array('code'=>'l4Perc', 'title'=>'%L4'),
            'l4s'=>array('code'=>'l4s', 'title'=>'#L4', 'colatts'=>$detailAtts),
            'l5Perc'=>array('code'=>'l5Perc', 'title'=>'%L5'),
            'l5s'=>array('code'=>'l5s', 'title'=>'#L5', 'colatts'=>$detailAtts),
            'l6Perc'=>array('code'=>'l6Perc', 'title'=>'%L6'),
            'l6s'=>array('code'=>'l6s', 'title'=>'#L6', 'colatts'=>$detailAtts),
            'l7Perc'=>array('code'=>'l7Perc', 'title'=>'%L7'),
            'l7s'=>array('code'=>'l7s', 'title'=>'#L7', 'colatts'=>$detailAtts),
            'l8Perc'=>array('code'=>'l8Perc', 'title'=>'%L8'),
            'l8s'=>array('code'=>'l8s', 'title'=>'#L8', 'colatts'=>$detailAtts),
            'totTargPoints'=>array('code'=>'totTargPoints', 'title'=>'Total Target Points'),
            'totLevelPoints'=>array('code'=>'totLevelPoints', 'title'=>'Total Level Points'),
            'targLevelDif'=>array('code'=>'targLevelDif', 'title'=>'Points Difference')            
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
        $sumMV=$ks3criteria['ByLabel']['Sum']['MapValue'];
        $sprMV=$ks3criteria['ByLabel']['Spr']['MapValue'];
        $autMV=$ks3criteria['ByLabel']['Aut']['MapValue'];
        $targMV=$ks3criteria['ByLabel']['Target']['MapValue'];

        $yres=new results_yeargroup($year, array('anyDataSet'=>$this->anyDataSet, 'dataSets'=>$dataSets, 'examIds'=>$examIds));
        
        $results=$yres->getAllResultsByExam();
        foreach ($results as $exam=>$row){
            foreach ($row as $rowItem){
                   
                // Make sure this module has been added to subject array
                $modId=$rowItem['ModuleId'];
                if (!isset($this->_subjectArr[$modId]) || !is_array($this->_subjectArr[$modId]) || empty($this->_subjectArr[$modId])){
              
        
                    $this->_subjectArr[$modId]=array('l1s'=>0, 'l2s'=>0, 'l3s'=>0, 'l4s'=>0, 'l5s'=>0, 'l6s'=>0, 'l7s'=>0, 'l8s'=>0, 'numAssEnts'=>0, 'totTargPoints'=>0, 'totLevelPoints'=>0, 'targLevelDif'=>0);
                }
                
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
                    $this->_subjectArr[$modId]['numAssEnts']++; // number of assessment entries processed
                    
                    // Increment appropriate level count
                    $levelChar=substr($curLevel['val'],0,1);
                    //echo ('<span>'.$curLevel['val'].', '.$rowItem['StudentId'].', '.$rowItem['ExamId'].', '.$rowItem['ModuleId'].'</span><br />');
                    switch ($levelChar){
                        case '1': $this->_subjectArr[$modId]['l1s']++; break;
                        case '2': $this->_subjectArr[$modId]['l2s']++; break;
                        case '3': $this->_subjectArr[$modId]['l3s']++; break;
                        case '4': $this->_subjectArr[$modId]['l4s']++; break;
                        case '5': $this->_subjectArr[$modId]['l5s']++; break;
                        case '6': $this->_subjectArr[$modId]['l6s']++; break;
                        case '7': $this->_subjectArr[$modId]['l7s']++; break;
                        case '8': $this->_subjectArr[$modId]['l8s']++; break;
                    }
                    
                    // Add target points to total
                    $this->_subjectArr[$modId]['totTargPoints']+=$critArr[$targMV]['pts'];
                    
                    // Add level points to total
                    $this->_subjectArr[$modId]['totLevelPoints']+=$curLevel['pts'];
                    
                    // Recalc dif
                    $this->_subjectArr[$modId]['targLevelDif']=$this->_subjectArr[$modId]['totLevelPoints']-$this->_subjectArr[$modId]['totTargPoints'];
 
                } else {
                    //echo ('<span>No level - '.$critStr.', '.$rowItem['StudentId'].', '.$rowItem['ExamId'].', '.$rowItem['ModuleId'].'</span><br />');            
                }

                //echo ('<p>'.$rowItem['ExamId'].' '.$rowItem['StudentId'].' '.$rowItem['ModuleId'].' '.var_export($critArr[$targMV]).'</p>');
            }
        }


        $reportdata=array();
        
        // Create final report data array
        foreach ($this->_subjectArr as $modId=>$modData){
            $arrayData=array();
            foreach ($modData as $key=>$val){
                $arrayData[$key]=array('val'=>$val);
            }
            $arrayData['l1Perc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['l1s']/$modData['numAssEnts'])*100),2));
            $arrayData['l2Perc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['l2s']/$modData['numAssEnts'])*100),2));
            $arrayData['l3Perc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['l3s']/$modData['numAssEnts'])*100),2));         
            $arrayData['l4Perc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['l4s']/$modData['numAssEnts'])*100),2));    
            $arrayData['l5Perc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['l5s']/$modData['numAssEnts'])*100),2));
            $arrayData['l6Perc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['l6s']/$modData['numAssEnts'])*100),2));
            $arrayData['l7Perc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['l7s']/$modData['numAssEnts'])*100),2));
            $arrayData['l8Perc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['l8s']/$modData['numAssEnts'])*100),2));            
            $reportdata[$modId]=$arrayData;
        }
        
        $this->_outputArray=array($this->_examId=>$reportdata);
        return ($this->_outputArray);
    }

    //
    // Purpose: Render (to a string) a HTML table based on data in $_outputArray
    //
    function renderHTMLTable($className='epExt_table', $caption=''){
        $className=$className!=null ? $className : 'epExt_table';
        $output='<table class="'.$className.'"><caption>'.$caption.'</caption>';
        
        // Add table head data
        $output.='<thead><tr>';
        // first column title is always rowcat title
        $output.='<th>'.$this->rowcatColumnTitle.'</th>';
        
        $tablearray=array();        
        foreach ($this->_outputArray as $subject=>$dataarray){       
            foreach ($dataarray as $rowcat=>$row){
                $rowarray=array();
                $rowarray['_rowcat']=$rowcat;
                foreach ($this->columns as $colcode=>$col){                    
                    $rowarray[$colcode]=$row[$colcode]['val'];
                }
                $tablearray[]=$rowarray;
            }
        }
        
        // sort by target level difference
        table_sort($tablearray, array('targLevelDif'=>true));
         
        foreach ($this->columns as $col){
            // Apply column title cell attributes
            if (isset($col['cellatts'])){
                $atSt=$this->attributesArrayToString($col['cellatts']);
            } else {
                $atSt='';
            }
            // Apply column attributes (for entire column)
            if (isset($col['colatts'])){
                $atSt.=' '.$this->attributesArrayToString($col['colatts']);
            }     
            $output.='<th'.$atSt.'>'.$col['title'].'</th>';
        }
        $output.='</tr></thead>';
        
        // Add table body data
        $output.='<tbody>';      

        foreach ($tablearray as $tabrow){
            if ($tabrow['totTargPoints']!=0 || $tabrow['totLevelPoints']!=0){
                $output.='<tr>';
                $rowcat=$tabrow['_rowcat'];
                foreach ($tabrow as $col=>$val){   
                    // Get attributes for cell
                    if (isset($dataarray[$rowcat][$col]['cellatts'])){
                        $atSt=$this->attributesArrayToString($row[$colcode]['cellatts']);
                    } else {
                        $atSt='';
                    }
                    // Get attributes for column
                    if (isset($this->columns[$col]['colatts'])){
                        $atSt.=$atSt!='' ? ' ' : '';
                        $atSt.=$this->attributesArrayToString($this->columns[$col]['colatts']);
                    }
                    
                    // Set up coloring for target level difference column
                    $htmlcol='';
                    if ($col=='targLevelDif'){
                        if ($val<0){
                            $htmlcol=' style="background-color:#e20; color:#fff;"';
                        } else {
                            $htmlcol=' style="background-color:#0c5; color:#fff;"';
                        }
                    }                
                    
                    $output.='<td'.$atSt.$htmlcol.'>'.$val.'</td>';
                }
                $output.='</tr>';
            }
        }
        
        $output.='</tbody>';
        $output.='</table>';
        return ($output);
    }
    
    
}
?>