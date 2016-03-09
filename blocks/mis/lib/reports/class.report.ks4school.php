<?php

//
// Author: Guy Thomas
// Date:2007 - 10 - 30
// Purpose: ks4 totals for entire school (y7,y8,y9,total)
//

// Set memory limit to 256MBytes - this is essential or script will fail.
ini_set('memory_limit', '256M');

// Set time out to 20 minutes
set_time_limit(1200);


// class.report.ks4year.php provides ../cfg , lib_results.php, class.propsbyparam.php, class.results.courseyear.php, lib_facility_db.php
require_once("class.report.ks4year.php");

class report_ks4school extends superreport{
  
    //
    // Protected properties
    //
    protected $_reportName='report_ks4school';    
    
    //
    // Class constructor
    //
    function report_ks4school ($params=array()){  
        parent::superreport($params);
        $this->setupRowCatLinks();      
    }
    
    function setupRowCatLinks(){
        // Set up links for each rowcat item (excludes whole keystage)
        $this->rowcatLinks=array (
            'y10'=>'ks4subjectyearanalysis.php?year=10&amp;primarySet='.$this->primarySet,
            'y11'=>'ks4subjectyearanalysis.php?year=11&amp;primarySet='.$this->primarySet,         
        );      
    }    
    
    //
    // Purpose: Default columns
    //
    function setDefaultColumns(){
        $detailAtts=array('style'=>'display:none;', 'class'=>'extraDetail');
        $this->columns=array(
            array('code'=>'5AC_P', 'title'=>'%5+A*-Cs'),
            array('code'=>'5ACs', 'title'=>'#5+A*-Cs', 'colatts'=>$detailAtts),
            array('code'=>'5ACs_ME', 'title'=>'#5+A*-Cs (M/E)', 'colatts'=>$detailAtts),
            array('code'=>'5AC_P_ME'  , 'title'=>'%5+A*-Cs (M/E)'),
            array('code'=>'5AG_P', 'title'=>'%5+A*-Gs'),
            array('code'=>'5AGs', 'title'=>'#5+A*-Gs', 'colatts'=>$detailAtts),
            array('code'=>'AC_P', 'title'=>'%A*-Cs'),
            array('code'=>'ACs', 'title'=>'#A*-Cs', 'colatts'=>$detailAtts),
            array('code'=>'AG_P', 'title'=>'%A*-Gs'),
            array('code'=>'AGs', 'title'=>'#A*-Gs', 'colatts'=>$detailAtts),      
            array('code'=>'A*s', 'title'=>'#A*s', 'colatts'=>$detailAtts),
            array('code'=>'As', 'title'=>'#As', 'colatts'=>$detailAtts),
            array('code'=>'Bs', 'title'=>'#Bs', 'colatts'=>$detailAtts),
            array('code'=>'Cs', 'title'=>'#Cs', 'colatts'=>$detailAtts),
            array('code'=>'Ds', 'title'=>'#Ds', 'colatts'=>$detailAtts),
            array('code'=>'Es', 'title'=>'#Es', 'colatts'=>$detailAtts),
            array('code'=>'Fs', 'title'=>'#Fs', 'colatts'=>$detailAtts),
            array('code'=>'Gs', 'title'=>'#Gs', 'colatts'=>$detailAtts),
            array('code'=>'totTargPoints', 'title'=>'Total Target Points'),
            array('code'=>'totLevelPoints', 'title'=>'Total Level Points')      
        );
    }

    //
    // Purpose: report engine - gets the data
    // This function is never directly called - it is called by the class superreport::getCacheOrBuild
    //
    protected function build(){
    
        $resParams=array(
            'anyDataSet'=>$this->anyDataSet,
            'dataSets'=>$this->dataSets,
            'primarySet'=>$this->primarySet,
            'anyDSCriteriaInc'=>$this->anyDSCriteriaInc,
            'anyDSCriteriaExc'=>$this->anyDSCriteriaExc,
        );
    
        $report=new report_ks4year($resParams, 10);
        $report->getCacheOrBuild($this->refreshCash);
        $y10repArr=$report->getOutput();
        $report=new report_ks4year($resParams, 11);
        $report->getCacheOrBuild($this->refreshCash);
        $y11repArr=$report->getOutput();
                
        $ks4Arr=array();
        
        $repCollection=array($y10repArr['Year10'], $y11repArr['Year11']);
        
        foreach ($repCollection as $rep){
            foreach ($rep as $key=>$item){
                // Make sure each key is initialised
                if (!isset($ks4Arr[$key])){
                    $ks4Arr[$key]['val']=0;
                }
                $ks4Arr[$key]['val']+=$item['val'];//add value to existing ks4 key total
            }
        }
        
        // Devide totalled percentages by 2 (because we have 2 rowcat - year10 and year 11)
        $ks4Arr['AC_P']['val']/=2;
        $ks4Arr['AC_P_ME']['val']/=2;
        $ks4Arr['AG_P']['val']/=2;
        $ks4Arr['5AC_P']['val']/=2;
        $ks4Arr['5AC_P_ME']['val']/=2;
        $ks4Arr['5AG_P']['val']/=2;
        
        // Round all ks4 values to 2 decimal places
        foreach ($ks4Arr as $key=>$val){
            $ks4Arr[$key]['val']=round($ks4Arr[$key]['val'],2);
        }
        
        $this->_outputArray=array ('ks4'=>$ks4Arr, 'y10'=>$y10repArr['Year10'], 'y11'=>$y11repArr['Year11']);
    }
    
}
?>