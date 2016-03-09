<?php

//
// Author: Guy Thomas
// Date:2007 - 10 - 30
// Purpose: ks4 totals for all subjects in a year group
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
// Class report_ks4yearsubject
//
class report_ks4yearsubject extends superreport{

    //
    // Mandatory public properties (passed on construction)
    //
    var $year;
   
    //
    // Protected properties
    //
    protected $_reportName='report_ks4yearsubject';
    protected $_examId='';
    protected $_subjectArr=array();
    
    //
    // Purpose: Class contructor
    //
    function report_ks4yearsubject ($params=array(), $year){
        $this->year=$year;
        parent::superreport($params, $year);
    }
    
    //
    // Purpose: Setup columns for this report
    //
    function setDefaultColumns(){       
        $detailAtts=array('style'=>'display:none;', 'class'=>'extraDetail');
        $this->columns=array(
            'a_Perc'=>array('code'=>'a_Perc', 'title'=>'%A*s'),
            'aPerc'=>array('code'=>'aPerc', 'title'=>'%As'),
            'bPerc'=>array('code'=>'bPerc', 'title'=>'%Bs'),
            'cPerc'=>array('code'=>'cPerc', 'title'=>'%Cs'),
            'dPerc'=>array('code'=>'dPerc', 'title'=>'%Ds'),
            'ePerc'=>array('code'=>'ePerc', 'title'=>'%Es'),
            'fPerc'=>array('code'=>'fPerc', 'title'=>'%Fs'),
            'gPerc'=>array('code'=>'gPerc', 'title'=>'%Gs'),
            'a_s'=>array('code'=>'a_s', 'title'=>'#A*s', 'colatts'=>$detailAtts),
            'as'=>array('code'=>'as', 'title'=>'#As', 'colatts'=>$detailAtts),
            'bs'=>array('code'=>'bs', 'title'=>'#Bs', 'colatts'=>$detailAtts),
            'cs'=>array('code'=>'cs', 'title'=>'#Cs', 'colatts'=>$detailAtts),
            'ds'=>array('code'=>'ds', 'title'=>'#Ds', 'colatts'=>$detailAtts),
            'es'=>array('code'=>'es', 'title'=>'#Es', 'colatts'=>$detailAtts),
            'fs'=>array('code'=>'fs', 'title'=>'#Fs', 'colatts'=>$detailAtts),
            'gs'=>array('code'=>'gs', 'title'=>'#Gs', 'colatts'=>$detailAtts),
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
        $validLevels=array('a*', 'a', 'b','c', 'd', 'e', 'f', 'g');           
        $this->_examId='Year'.$year;
        $examIds=array($this->_examId);
        
        // If dataSets not specified (empty) then use config current dataset
        if (empty($dataSets)){
            $dataSets=array($CFG->mis->cmisDataSet);
        }

        // connect to Facility database
        $fdata=new facilityData();
        

        // Get criteria
        $ks4criteria=CriteriaForAssessment('KS4Assess');
        $ks4critPVs=AssessmentCriteriaPoints('KS4Assess');

        // Get map vals
        $sumMV=$ks4criteria['ByLabel']['Sum']['MapValue'];
        $sprMV=$ks4criteria['ByLabel']['Spr']['MapValue'];
        $autMV=$ks4criteria['ByLabel']['Aut']['MapValue'];
        $targMV=$ks4criteria['ByLabel']['Target']['MapValue'];

        $yres=new results_yeargroup($year, array('anyDataSet'=>$this->anyDataSet, 'dataSets'=>$dataSets, 'examIds'=>$examIds));
        
        $results=$yres->getAllResultsByExam();
        foreach ($results as $exam=>$row){
            foreach ($row as $rowItem){
                   
                // Make sure this module has been added to subject array
                $modId=$rowItem['ModuleId'];
                if (!isset($this->_subjectArr[$modId]) || !is_array($this->_subjectArr[$modId]) || empty($this->_subjectArr[$modId])){
                    $this->_subjectArr[$modId]=array('a_s'=>0, 'as'=>0, 'bs'=>0, 'cs'=>0, 'ds'=>0, 'es'=>0, 'fs'=>0, 'gs'=>0, 'numAssEnts'=>0, 'totTargPoints'=>0, 'totLevelPoints'=>0, 'targLevelDif'=>0);                   
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

                // force lower case
                if (isset($curLevel)){
                    $curLevel['val']=strtolower($curLevel['val']);    
                }
                
                // Only process assessment entries (exams) that have both a level and target AND level is a KS4 level
                if (isset($curLevel) && in_array($curLevel['val'], $validLevels) && isset($critArr[$targMV])){ 
                    // increment number of assessment entries (exams)
                    $this->_subjectArr[$modId]['numAssEnts']++; // number of assessment entries processed
                    
                    // Increment appropriate level count
                    switch ($curLevel['val']){
                        case 'a*': $this->_subjectArr[$modId]['a_s']++; break;
                        case 'a': $this->_subjectArr[$modId]['as']++; break;
                        case 'b': $this->_subjectArr[$modId]['bs']++; break;
                        case 'c': $this->_subjectArr[$modId]['cs']++; break;
                        case 'd': $this->_subjectArr[$modId]['ds']++; break;
                        case 'e': $this->_subjectArr[$modId]['es']++; break;
                        case 'f': $this->_subjectArr[$modId]['fs']++; break;
                        case 'g': $this->_subjectArr[$modId]['gs']++; break;
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
           $arrayData['a_Perc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['a_s']/$modData['numAssEnts'])*100),2));
           $arrayData['aPerc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['as']/$modData['numAssEnts'])*100),2));
           $arrayData['bPerc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['bs']/$modData['numAssEnts'])*100),2));
           $arrayData['cPerc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['cs']/$modData['numAssEnts'])*100),2));      
           $arrayData['dPerc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['ds']/$modData['numAssEnts'])*100),2));
           $arrayData['ePerc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['es']/$modData['numAssEnts'])*100),2));
           $arrayData['fPerc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['fs']/$modData['numAssEnts'])*100),2));
           $arrayData['gPerc']=$modData['numAssEnts']==0 ? 0 : array('val'=>round((($modData['gs']/$modData['numAssEnts'])*100),2));           
           
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