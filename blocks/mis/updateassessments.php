<?php
    global $USER, $CFG, $fdata;

    require_once('../../config.php');
    require_once($CFG->dirroot.'/lib/formslib.php');
    require_once($CFG->dirroot.'/blocks/mis/cfg/config.php');
    require_once($CFG->dirroot.'/blocks/mis/lib/lib_facility_db.php');        
    require_once($CFG->dirroot.'/blocks/mis/lib/manageassessments_lib.php');
    require_once($CFG->dirroot.'/blocks/mis/lib/moodledb.php');
    
    require_login();  
	$params=array('sesskey'=>sesskey());  
    $url= new moodle_url('/blocks/mis/updateassessments.php',$params);
	$PAGE->set_url($url);
	$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
	$PAGE->set_heading(get_string('mis:manageassessments', 'block_mis'));
	$PAGE->set_title(get_string('mis:manageassessments', 'block_mis'));
	$PAGE->requires->css('/blocks/mis/css/style.css');  
    $PAGE->navbar->add('Update Assessments...',$url);
    echo $OUTPUT->header();
	
	if (!$site = get_site()) {
		echo $OUTPUT->box(get_string('noaccess','block_mis'), 'generalbox boxaligncenter');
		echo $OUTPUT->footer();
		die();
	}
    if ((!has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))&& !$capsmanage) || !confirm_sesskey()) {
        error("You do not have access to this area");
    }
    $fdata = new facilityData();   
    $json = optional_param('assessmentjson', '', PARAM_RAW);
    $json = stripslashes($json);
	$capsmanage = has_capability('block/mis:manageassessments', get_context_instance(CONTEXT_SYSTEM));
	
	$content  = html_writer::start_tag('div', array('class'=>'misMain'));
    $content .= update_assessments($json);
	$content .= html_writer::end_tag('div');
	echo $content;
    echo $OUTPUT->footer();
?>