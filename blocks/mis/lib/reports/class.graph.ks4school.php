<?php

//
// Author: Guy Thomas
// Date:2007 - 11 - 01
// Purpose: ks4 graphing totals for entire school (y7,y8,y9,total)
//

// Set memory limit to 256MBytes - this is essential or script will fail.
ini_set('memory_limit', '256M');

// Set time out to 20 minutes
set_time_limit(1200);


// class.report.ks4year.php provides ../cfg , lib_results.php, class.propsbyparam.php, class.results.courseyear.php, lib_facility_db.php
require_once("class.report.ks4school.php");


class graph_ks4school extends report_ks4school{
        
    //
    // Protected properties
    //
    protected $_reportName='graph_ks4school';
    
    //
    // Class constructor
    //
    function graph_ks4school ($params=array()){        
        parent::report_ks4school($params);
    }
    
    //
    // Purpose: Default columns
    //
    function setDefaultColumns(){
        $this->columns=array(
            array('code'=>'AC_P', 'title'=>'%A*-Cs'),
            array('code'=>'AC_P_ME', 'title'=>'%A*-Cs (M/E)'),
            array('code'=>'AG_P', 'title'=>'%A*-Gs')   
        );
    }       
}

?>