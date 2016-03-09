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
$assessid=optional_param('assid', null, PARAM_TEXT);
$setid=optional_param('setid', null, PARAM_TEXT);
$outputXML=getexams($setid, $assessid);

?>
    <response>
    	<response_type>exams</response_type>
        <?php echo ($outputXML);?>
        
    </response>
    
<?php

function getexams($setid, $assessid){
    global $fdata;
    $output='';
    $sql='SELECT * FROM '.$fdata->prefix.'examinations WHERE assessid=\''.$assessid.'\'';
    $rs=$fdata->doQuery($sql);
    foreach ($rs as $row){
        $name=$row->name!='' ? $row->name : $row->examid;        
        $misexam=db_mis::get_exam($setid, $row->examid);        
        if ($misexam && $misexam->namecustom!=''){
            $namecustom=$misexam->namecustom;
        } else {
            $namecustom=$name;
        }
        $output.=$output=='' ? '' : "\n";        
        $output.="\t\t\t".'<exam>';
        $output.="\t\t\t\t".'<id>'.$row->examid.'</id>';
        $output.="\t\t\t\t".'<name>'.$name.'</name>';
        $output.="\t\t\t\t".'<namecustom>'.$namecustom.'</namecustom>';
        $output.="\t\t\t".'</exam>';
    }
    return ($output);
}