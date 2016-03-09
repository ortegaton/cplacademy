<?php

require_once(dirname(__FILE__) . '/../../../../../../../../config.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();
$courseid = required_param('cid',PARAM_INTEGER);
$sesskey = required_param('sesskey',PARAM_RAW);
confirm_sesskey($sesskey);

if (!$courseid == SITEID) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = context_system::instance();
    $PAGE->set_context($context);
}

$urlparams = array('cid' => $courseid, 'sesskey' => $sesskey);
$PAGE->https_required();
$PAGE->set_url('/local/zilink/plugins/homework/report/interfaces/default/pages/view.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('homework_report_page_title', 'local_zilink');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->requires->css('/local/zilink/plugins/homework/report/interfaces/default/styles.css');
$tt_url = new moodle_url('/local/zilink/plugins/homework/report/interfaces/default/pages/view.php', $urlparams);
$PAGE->navbar->add(get_string('pluginname_short','local_zilink'));
$PAGE->navbar->add(get_string('homework', 'local_zilink'));
$PAGE->navbar->add(get_string('homework_report_report', 'local_zilink'), $tt_url);
$PAGE->set_pagelayout('base');

$security = new ZiLinkSecurity();

//if(!$security->IsAllowed('local/zilink:homework_report_subject_teacher_edit') &&
//    !$security->IsAllowed('local/zilink:homework_report_subject_leader_edit') &&
 //   !$security->IsAllowed('local/zilink:homework_report_senior_management_team_edit'))
//{
//    redirect($CFG->httpswwwroot.'/course/view.php?id='.$courseid,get_string('requiredpermissionmissing','local_zilink'),1);
//}

try {

    $homework_report = new ZiLinkHomeworkReport($courseid);
    $content = $homework_report->View(array( 'action'       => optional_param('action', 'list', PARAM_RAW),
                                             'cohort'          => optional_param('cohort', -1, PARAM_INTEGER),
                                             'category'          => optional_param('category', -1, PARAM_INTEGER),
                                             'teacher'     => optional_param('teacher', -1, PARAM_INTEGER),
                                             'uid' => $USER->id,
                                             //'homeworksetperiodstart' => optional_param('homeworksetperiodstart', 0, PARAM_INTEGER),
                                             //'homeworksetperiodend' => optional_param('homeworksetperiodend', 0, PARAM_INTEGER)
                                             ));
    
} catch (Exception $e)  {
    
    $message = get_string('requireddatamissing','local_zilink');
    
    if($CFG->debug == DEBUG_DEVELOPER) {
            
        $message .= '<br>';
        $message .= '<pre>'. print_r($e->getTrace(),true).'</pre>';
         
    }
    
    redirect($CFG->httpswwwroot.'/course/view.php?id='.$courseid,$message,1);
}

$header = $OUTPUT->header();
$footer = $OUTPUT->footer();
echo $header.$content.$footer;
