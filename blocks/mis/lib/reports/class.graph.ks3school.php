<?php

//
// Author: Guy Thomas
// Date:2007 - 11 - 01
// Purpose: KS3 graphing totals for entire school (y7,y8,y9,total)
//

// Set memory limit to 256MBytes - this is essential or script will fail.
ini_set('memory_limit', '256M');

// Set time out to 20 minutes
set_time_limit(1200);


// class.report.ks3year.php provides ../cfg , lib_results.php, class.propsbyparam.php, class.results.courseyear.php, lib_facility_db.php
require_once("class.report.ks3school.php");


class graph_ks3school extends report_ks3school{
    
    //
    // Protected properties
    //
    protected $_reportName='graph_ks3school';
    
    //
    // Class constructor
    //
    function graph_ks3school ($params=array()){        
        parent::report_ks3school($params);
    }
    
    //
    // Purpose: Setup columns for this report
    //
    function setDefaultColumns(){
        $this->columns=array(
            array('code'=>'l2s', 'title'=>'L2'),
            array('code'=>'l3s', 'title'=>'L3'),
            array('code'=>'l4s', 'title'=>'L4'),
            array('code'=>'l5s', 'title'=>'L5'),
            array('code'=>'l6s', 'title'=>'L6'),
            array('code'=>'l7s', 'title'=>'L7'),
            array('code'=>'l8s', 'title'=>'L8')   
        );
    }    
    
}

?>