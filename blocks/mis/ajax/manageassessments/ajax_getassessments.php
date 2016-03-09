<?php

/***************************************************************
/* Purpose of this file: Return assessment config for specific
/* dataset
****************************************************************/

// Turn off all error reporting
// error_reporting(0);

// prevent caching
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// important - this is so AJAX can parse response as xml
header('Content-Type: text/xml');

require_once('../../../../config.php');
require_once($CFG->dirroot.'/blocks/mis/cfg/config.php');
require_once($CFG->dirroot.'/blocks/mis/lib/lib_facility_db.php');
require_once($CFG->dirroot.'/blocks/mis/lib/moodledb.php');

global $fdata;
$fdata=new facilityData();

$outputXML=getassessments();

?>
    <response>
    	<response_type>assessments</response_type>
        <?php echo ($outputXML);?>
        
    </response>
    
<?php

function getassessments(){
    global $fdata;
    $output='';
    $sql='SELECT DISTINCT a.assessid, a.name FROM ('.$fdata->prefix.'assessments AS a LEFT JOIN '.$fdata->prefix.'examinations AS e ON a.assessid=e.assessid)  LEFT JOIN '.$fdata->prefix.'rtexams as rt ON e.examid=rt.examid WHERE rt.examid<>\'\' AND a.disabled<>\'Y\' ORDER BY a.name, a.assessid';
    $rs=$fdata->doQuery($sql);
    foreach ($rs as $row){
        $name=$row->name!='' ? $row->name : $row->assessid;
        $output.=$output=='' ? '' : "\n";
        $output.="\t\t\t".'<assessment>';
        $output.="\t\t\t\t".'<id>'.$row->assessid.'</id>';
        $output.="\t\t\t\t".'<name>'.$name.'</name>';
        $output.="\t\t\t".'</assessment>';
    }
    return ($output);
}