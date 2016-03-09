<?php
    
// Turn off all error reporting
//error_reporting(0);

// Set to display errors on screen (enable this if you want to debug)
ini_set('display_errors', 'stdout');

// prevent caching
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

require_once('../../../../../config.php');
require_once('../../../../../course/lib.php');
require_once('../../../cfg/config.php');
require_once('../../../lib/lib_facility_db.php');
require_once('../../../lib/reports/lib_results.php');
require_once('../../../lib/reports/class.results.student.php');
require_once('../../../lib/chart/FusionCharts.php');
require_once('../lib/assLibs.php');
require_once('../../../lib/reports/class.report.studentexam.php');

global $CFG, $fdata;
$fdata=new facilityData();
$edt=new examdatatable();
$edt->output();

//
// Class examdatatable
//
class examdatatable {
    var $studentid='';
    var $examid='';
    var $dataset='';
    
    //
    // Purpose : Constructor
    //
    function examdatatable(){

        $this->setvars();
        
    }
    
    //
    // Purpose: Set class variables
    //
    private function setvars(){
    
        global $USER, $CFG, $fdata;
        
        // Get selected user from session
        $userDetails = get_record('user', 'id', $USER->mis_mdlstuid);
        if($userDetails === false) {
            error('Unable to locate this user');
        }   
        $this->studentid = $fdata->getStuAdminNo($userDetails->idnumber);                
        $this->examid=isset($_POST['examid']) ? $_POST['examid'] : false;
        $this->dataset=isset($_POST['dataset']) ? $_POST['dataset'] : $CFG->mis->cmisDataSet;           
    }
    
    function output(){
        $dataSets=array($this->dataset);
        $report=new report_studentexam (array('anyDataSet'=>true, 'dataSets'=>$dataSets), $this->studentid, $this->examid);
        $report->getCacheOrBuild(true);
        echo($report->renderHTMLTable());    
    }
    
}
?>