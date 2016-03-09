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

class results_classgroup extends superresults{

    //public properties
    var $classGroupId='';

	//
	// Class Constructor
	//
	function results_classgroup($params=array(), $classGroupId){
        // Set private properties
        $this->classGroupId=$classGroupId;
        
        $params['classGroupIds']=array($classGroupId);
    
        // Set base sql
        $this->baseSQL='SELECT ns.*, nstu.Surname, nstu.Forename, nstu.Forename2, nstu.CalledName, nstu.DateOfBirth, nstu.StuSex, stu.ClassGroupId, stu.CourseYear, tg.GroupCode FROM ((students AS stu LEFT JOIN nstupersonal AS nstu ON (nstu.StudentId=stu.StudentId AND nstu.SetId=stu.SetId)) LEFT JOIN nsturesults AS ns ON (ns.StudentId=stu.StudentId)) LEFT JOIN teachinggroups as tg ON (tg.GroupId=ns.GroupId AND tg.SetId=ns.SetId)';
    
        // Call parent constructor	
        parent::superresults($params);       
    }
}