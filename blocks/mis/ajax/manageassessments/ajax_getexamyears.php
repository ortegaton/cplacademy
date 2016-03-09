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
$setid=optional_param('setid', null, PARAM_TEXT);
$examid=optional_param('examid', null, PARAM_TEXT);
$outputXML=getexamyears($setid, $examid);

?>
    <response>
    	<response_type>examyears</response_type>
        <?php echo ($outputXML);?>
        
    </response>
    
<?php

function getexamyears($setid, $examid){
    global $fdata;
    $output='';
    $sql='SELECT DISTINCT crsyear FROM '.$fdata->prefix.'classgroup WHERE setid=\''.$setid.'\' ORDER BY crsyear';
    $rs=$fdata->doQuery($sql);
    foreach ($rs as $row){
        $examyear=db_mis::get_exam_year($setid, $examid, $row->crsyear);    
        $display=$examyear ? $examyear->display : 0;        
        $from=isset($examyear->displayfrom) && $examyear->displayfrom!=0 ? date('d-m-Y', $examyear->displayfrom) : '';
        $to=isset($examyear->displayto) &&$examyear->displayto!=0 ? date('d-m-Y', $examyear->displayto) : '';
        $output.=$output=='' ? '' : "\n";
        $output.="\t\t\t".'<examyear>';
        $output.="\t\t\t\t".'<id>'.$row->crsyear.'</id>';        
        $output.="\t\t\t\t".'<year>'.$row->crsyear.'</year>';
        $output.="\t\t\t\t".'<display>'.$display.'</display>';
        $output.="\t\t\t\t".'<displayfrom>'.$from.'</displayfrom>';
        $output.="\t\t\t\t".'<displayto>'.$to.'</displayto>';
        $output.="\t\t\t".'</examyear>';
    }
    return ($output);
}