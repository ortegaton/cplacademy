<?php

//
// Author: Guy Thomas
// Date:2007 - 10 - 09
// Purpose: Returns class group results object
//
// Requires:
//    class.propsbyparam.php
//    lib_results.php
//    cfg.php (config file)
//
//

require_once("class.results.super.php");

class results_yeargroup extends superresults{

    //private properties
    var $crsYr='';

	//
	// Class Constructor
	//
	function results_yeargroup($crsYr, $params=array()){
        // Set private properties
        $this->crsYr=$crsYr;
        
        $params['crsYrs']=array($crsYr);
    
        // Set base sql
        $this->baseSQL='SELECT ns.*, nstu.Surname, nstu.Forename, nstu.Forename2, nstu.CalledName, nstu.DateOfBirth, nstu.StuSex, stu.ClassGroupId, stu.CourseYear, tg.GroupCode FROM ((students AS stu LEFT JOIN nstupersonal AS nstu ON (nstu.StudentId=stu.StudentId AND nstu.SetId=stu.SetId)) LEFT JOIN nsturesults AS ns ON (ns.StudentId=stu.StudentId)) LEFT JOIN teachinggroups as tg ON (tg.GroupId=ns.GroupId AND tg.SetId=ns.SetId)';
    
        // Call parent constructor	
        parent::superresults($params);       
    }
}