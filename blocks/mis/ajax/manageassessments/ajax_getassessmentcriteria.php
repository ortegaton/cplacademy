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
require_once($CFG->dirroot.'/blocks/mis/lib/reports/lib_results.php');

global $fdata;
$fdata=new facilityData();
$setid=optional_param('setid', '', PARAM_TEXT);
$assid=optional_param('assid', '', PARAM_TEXT);

$outputXML=getassessmentcriteria($setid, $assid);

?>
    <response>
    	<response_type>assessmentcriteria</response_type>        
<?php
    if ($setid==''){
        echo ("\n\t\t".'<critical_error>missing post variable - setid</critical_error>');
        echo ("\n\t</response>");
        die;
    }
    if ($assid==''){
        echo ("\n\t\t".'<critical_error>missing post variable - assid</critical_error>');
        echo ("\n\t</response>");
        die;
    }    
?>    
        <?php echo ($outputXML);?>        
    </response>    
<?php

function getassessmentcriteria($setid, $assessid){
    global $fdata;
    $output='';

    $criteria= CriteriaForAssessment($assessid);

    if ($criteria && !empty($criteria)){
        foreach ($criteria['bymap'] as $map=>$details){    
            $label=$details['critlabel'];
            $row=db_mis::get_assessment_criteria($setid, $assessid, $label);
            if ($row){
                if ($row->labelcustom!=''){
                    $labelcustom=$row->labelcustom;
                } else {
                    $labelcustom=$label;
                }
                $display=$row->display;
            } else {
                $labelcustom=$label;
                $display=1; // default is checked
            }
            $output.="\n\t\t\t".'<crit>';
            $output.="\n\t\t\t\t".'<map>'.$map.'</map>';
            $output.="\n\t\t\t\t".'<label>'.$label.'</label>';
            $output.="\n\t\t\t\t".'<labelcustom>'.$labelcustom.'</labelcustom>';
            $output.="\n\t\t\t\t".'<display>'.$display.'</display>';
            $output.="\n\t\t\t".'</crit>';
        }
    }
    return ($output);
}