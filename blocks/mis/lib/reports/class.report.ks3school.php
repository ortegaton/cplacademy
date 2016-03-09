<?php

//
// Author: Guy Thomas
// Date:2007 - 10 - 30
// Purpose: KS3 totals for entire school (y7,y8,y9,total)
//

// Set memory limit to 256MBytes - this is essential or script will fail.
ini_set('memory_limit', '256M');

// Set time out to 20 minutes
set_time_limit(1200);


// class.report.ks3year.php provides ../cfg , lib_results.php, class.propsbyparam.php, class.results.courseyear.php, lib_facility_db.php
require_once("class.report.ks3year.php");

class report_ks3school extends superreport{

     
    //
    // Protected properties
    //
    protected $_reportName='report_ks3school';    
    
    
    //
    // Class constructor
    //
    function report_ks3school ($params=array()){        
        parent::superreport($params);
        $this->setupRowCatLinks();         
    }
    
    function setupRowCatLinks(){
        // Set up links for each rowcat item (excludes whole keystage)        
        $this->rowcatLinks=array (
            'y7'=>'ks3subjectyearanalysis.php?year=7&amp;primarySet='.$this->primarySet,
            'y8'=>'ks3subjectyearanalysis.php?year=8&amp;primarySet='.$this->primarySet,
            'y9'=>'ks3subjectyearanalysis.php?year=9&amp;primarySet='.$this->primarySet,
        );      
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
    
        $resParams=array('anyDataSet'=>$this->anyDataSet, 'dataSets'=>$this->dataSets);
    
        $report=new report_ks3year($resParams, 7);
        $report->getCacheOrBuild($this->refreshCash);
        $y7repArr=$report->getOutput();
        
        $report=new report_ks3year($resParams, 8);
        $report->getCacheOrBuild($this->refreshCash);
        $y8repArr=$report->getOutput();
        
        $report=new report_ks3year($resParams, 9);
        $report->getCacheOrBuild($this->refreshCash);
        $y9repArr=$report->getOutput();
        
        $ks3Arr=array();
        
        $repCollection=array($y7repArr['Year7'], $y8repArr['Year8'], $y9repArr['Year9']);
        
        foreach ($repCollection as $rep){
            foreach ($rep as $key=>$item){
                // Make sure each key is initialised
                if (!isset($ks3Arr[$key])){
                    $ks3Arr[$key]['val']=0;
                }
                $ks3Arr[$key]['val']+=$item['val'];//add value to existing KS3 key total
            }
        }
        
        // Devide totalled percentages by 3 (because we have 3 rowcat - year7, year8 and year 9)
        $ks3Arr['l2Perc']['val']/=3;
        $ks3Arr['l3Perc']['val']/=3;
        $ks3Arr['l4Perc']['val']/=3;
        $ks3Arr['l5Perc']['val']/=3;
        $ks3Arr['l6Perc']['val']/=3;
        $ks3Arr['l7Perc']['val']/=3;
        $ks3Arr['l8Perc']['val']/=3;
        
        // Round all ks3 values to 2 decimal places
        foreach ($ks3Arr as $key=>$val){
            $ks3Arr[$key]['val']=round($ks3Arr[$key]['val'],2);
        }
      
        //var_dump($y8repArr);        
        //var_dump($y9repArr);
        
        $this->_outputArray=array ('ks3'=>$ks3Arr, 'y7'=>$y7repArr['Year7'], 'y8'=>$y8repArr['Year8'], 'y9'=>$y9repArr['Year9']);
    }
    
}
?>