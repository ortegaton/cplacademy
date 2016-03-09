<?php

require_once(dirname(__FILE__) . '/../../../../../../../../config.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();
$courseid = required_param('cid',PARAM_RAW);
$sesskey = required_param('sesskey',PARAM_RAW);
$view = optional_param('view','all',PARAM_RAW);

confirm_sesskey($sesskey);

$context = context_system::instance();
$PAGE->set_context($context);


$urlparams = array();
$PAGE->https_required();
$PAGE->set_url('/local/zilink/plugins/student/view/view.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('student_view_general_page_title', 'local_zilink');


$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);


$PAGE->requires->css('/local/zilink/plugins/student/view/styles.css');
$PAGE->requires->js('/local/zilink/plugins/student/view/interfaces/default/module.js');

$PAGE->navbar->add(get_string('pluginname_short','local_zilink'));
$PAGE->navbar->add(get_string('student', 'local_zilink'));
$PAGE->navbar->add(get_string('view', 'local_zilink'));
$PAGE->navbar->add(fullname($USER), new moodle_url('/local/zilink/plugins/guardian/view/pages/default/index.php', $urlparams));

$PAGE->set_pagelayout('report');

$security = new ZiLinkSecurity();

if(!$security->IsAllowed('local/zilink:student_view'))
{
    redirect($CFG->httpswwwroot.'/course/view.php?id='.$COURSE->id,get_string('requiredpermissionmissing','local_zilink'),1);
}

try {

    $portal = new ZiLinkStudentView($courseid);
    $content = $portal->View();
    
} catch (Exception $e)  {
    
    $message = get_string('requireddatamissing','local_zilink');
    
    if($CFG->debug == DEBUG_DEVELOPER) {
            
        $message .= '<br>';
        $message .= '<pre>'. print_r($e->getTrace(),true).'</pre>';
         
    }
    
    redirect($CFG->httpswwwroot.'/course/view.php?id=1',$message,1);
}

$header = $OUTPUT->header();
$footer = $OUTPUT->footer();
echo $header.$content.$footer;