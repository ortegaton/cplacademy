<?php

    global $USER, $CFG, $fdata;

    require_once('../../config.php');
    require_once($CFG->dirroot.'/lib/formslib.php');
    require_once($CFG->dirroot.'/blocks/mis/cfg/config.php');
    require_once($CFG->dirroot.'/blocks/mis/lib/lib_facility_db.php');  
    require_once($CFG->dirroot.'/blocks/mis/lib/moodledb.php');
    require_once($CFG->dirroot.'/blocks/mis/lib/manageassessments_lib.php');
    
    $delete       = optional_param('delete', 0, PARAM_INT);
	$edit         = optional_param('edit', 0, PARAM_INT);
	$save         = optional_param('save', 0, PARAM_INT);
    $confirm      = optional_param('confirm', '', PARAM_ALPHANUM);   //md5 confirmation hashconfirmation hash    
   
    $fdata=new facilityData();
    $capsmanage=has_capability('block/mis:manageassessments', get_context_instance(CONTEXT_SYSTEM));
	//$gtl1 = new moodle_url('/lib/gtlib_yui/lib.yahoocompat.js');
	$gtl2 = new moodle_url($CFG->wwwroot.'/blocks/mis/js/gtlib_yui/lib.gt_all-min.js');
	//$gtl3 = new moodle_url('/lib/gtlib_yui/widgets/dialog/lib.gt.dialog.js');
	//$gtl4 = new moodle_url('/lib/gtlib_yui/widgets/tree/lib.gt.ajajTree.js');
	$misJS = new moodle_url('/blocks/mis/js/yui-build/json/json-beta.js');
	$misJS2 = new moodle_url('/blocks/mis/js/datepicker/js/datepicker.js');
	$misJS3 = new moodle_url('/blocks/mis/js/manageassessments.js');
    
    require_login();  
	$params=array('sesskey'=>sesskey());  
    $url= new moodle_url('/blocks/mis/manageassessments.php',$params);
	$PAGE->set_url($url);
	$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
	$PAGE->requires->css('/blocks/mis/css/style.css');
    $PAGE->requires->css('/lib/gtlib_yui/widgets/dialog/themes/standard/dialog.css');
    $PAGE->requires->css('/blocks/mis/js/datepicker/css/datepicker.css'); 
    $PAGE->set_heading(get_string('mis:manageassessments', 'block_mis'));
	$PAGE->set_title(get_string('mis:manageassessments', 'block_mis'));
	//$PAGE->requires->yui2_lib('yahoo'); 
	//$PAGE->requires->yui2_lib('dom'); 
	//$PAGE->requires->yui2_lib('event'); 
	//$PAGE->requires->yui2_lib('dragdrop'); 
	//$PAGE->requires->js($gtl1);
	$PAGE->requires->js($gtl2);
	//$PAGE->requires->js($gtl3);
	//$PAGE->requires->js($gtl4);
	$PAGE->requires->js($misJS);
	$PAGE->requires->js($misJS2);
	$PAGE->requires->js($misJS3);
	$PAGE->navbar->add('Manage Assessments...',new moodle_url('/blocks/mis/manageassessments.php',array('sesskey'=>sesskey())));
    
	echo $OUTPUT->header();
	
	if (!$site = get_site()) {
		echo $OUTPUT->box(get_string('noaccess','block_mis'), 'generalbox boxaligncenter');
		echo $OUTPUT->footer();
		die();
	}
    if ((!has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))&& !$capsmanage) || !confirm_sesskey()) {
        error("You do not have access to this area");
    }
	$content  = html_writer::start_tag('div', array('class'=>'misMain'));
	$content .= html_writer::end_tag('div');
    $content .= manage_assessments();
	$content .= html_writer::end_tag('div'); 
	echo $content;  
    echo ("\n".'<form method="post" action="updateassessments.php?sesskey='.$USER->sesskey.'" name="assessments" id="frm_assessments">');
    echo ("\n\t".'<input name="assessmentjson" id="assessmentjson" type="hidden" value="" />');
    echo ("\n".'</form>');
    $js='<script type="text/javascript">var mis_ajax_url="'.$CFG->wwwroot.'/blocks/mis/ajax/manageassessments/"; var mdlsessid="'.$USER->sesskey.'"; var courseId="'.$COURSE->id.'";</script>'."\n";    
    $js .="<script type=\"text/javascript\">GTLib_ExportFuncs=true;</script>\n";
    echo ($js);        
    echo $OUTPUT->footer();
?>