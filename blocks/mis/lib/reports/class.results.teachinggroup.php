<?php

//
// Author: Guy Thomas
// Date:2007 - 10 - 09
// Purpose: Returns teaching group results object
//
// Requires:
//    class.propsbyparam.php
//    lib_results.php
//    cfg.php (config file)
//
//

require_once("class.results.super.php");

class results_teachinggroup extends superresults{

    var $teachingGroupId='';

	//
	// Class Constructor
	//
	function results_teachinggroup($params=array(), $teachingGroupId){
    
        global $CFG;
    
        $this->sqlAppendWhere=''; // initialise
        
        // set params
        propsbyparam::propsbyparam($params);
    
        // Set private properties
        $this->teachingGroupId=$teachingGroupId;
        
        $params['groupCodes']=array($teachingGroupId);
                    
        if ($CFG->mis->cmisDBType!="access"){    
            // Set base sql for MSSQL
            $this->baseSQL='SELECT ns.*, nstu.Surname, nstu.Forename, nstu.Forename2, nstu.CalledName, nstu.DateOfBirth, nstu.StuSex, stu.ClassGroupId, stu.CourseYear, tg.GroupCode FROM (((teachinggroups AS tg LEFT JOIN stugroups AS sg ON (sg.groupid=tg.groupid AND sg.setid=tg.setid)) LEFT JOIN students AS stu ON (stu.studentid=sg.studentid AND stu.setid=tg.setid)) LEFT JOIN nstupersonal AS nstu ON (nstu.studentid=sg.studentid AND nstu.setid=sg.setid)) LEFT JOIN nsturesults AS ns ON (ns.moduleid=tg.moduleid AND ns.studentid=sg.studentid)'; 
        } else {
            // Set base sql for MSACCESS
            $this->baseSQL='SELECT ns.*, nstu.Surname, nstu.Forename, nstu.Forename2, nstu.CalledName, nstu.DateOfBirth, nstu.StuSex, stu.ClassGroupId, stu.CourseYear, tg.GroupCode FROM (((teachinggroups AS tg LEFT JOIN stugroups AS sg ON (sg.groupid=tg.groupid AND sg.setid=tg.setid)) LEFT JOIN students AS stu ON (stu.studentid=sg.studentid AND stu.setid=tg.setid)) LEFT JOIN nstupersonal AS nstu ON (nstu.studentid=sg.studentid AND nstu.setid=sg.setid)) LEFT JOIN nsturesults AS ns ON (ns.studentid=sg.studentid)';
            $this->sqlAppendWhere=' AND ns.moduleid=tg.moduleid';   
        }
        
        // Filter to specific dataset
        if (!$this->anyDataSet){
            $this->sqlAppendWhere.=' AND ns.setid=tg.setid';
        }
        
        // Call parent constructor        
        parent::superresults($params);
        
        // Create student array (contains more detail than standard results array)
        $stusql='SELECT nstu.*, stu.* FROM ((teachinggroups AS tg LEFT JOIN stugroups AS sg ON (sg.groupid=tg.groupid AND sg.setid=tg.setid)) LEFT JOIN students AS stu ON (stu.studentid=sg.studentid AND stu.setid=tg.setid)) LEFT JOIN nstupersonal AS nstu ON (nstu.studentid=sg.studentid AND nstu.setid=sg.setid) WHERE tg.setid=\''.$this->primarySet.'\' AND tg.groupcode=\''.$teachingGroupId.'\'';
        $this->generateStudentArray($stusql);
        
    }
}