<?php

//
// Author: Guy Thomas
// Date:2007 - 09 - 18
// Purpose: Returns student results object
//
// Requires:
//    class.propsbyparam.php
//    lib_results.php
//    cfg.php (config file)
//
//

require_once("class.results.super.php");

class results_students extends superresults{

    //private properties
    var $studentId='';

	//
	// Class Constructor
	//
	function results_students($studentId, $params=array()){
        // Set private properties
        $this->studentId=$studentId;
        
        $params['studentIds']=array($studentId);
    
        // Call parent constructor
        parent::superresults($params);       
    }
}