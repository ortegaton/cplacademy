<?php

require_once(dirname(__FILE__) . '/../../../../../../../config.php');
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
$PAGE->set_url('/local/zilink/plugins/report_writer/interfaces/default/pages/view.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('report_writer_page_title', 'local_zilink');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->requires->css('/local/zilink/plugins/report_writer/interfaces/default/styles.css');
$tt_url = new moodle_url('/local/zilink/plugins/report_writer/interfaces/default/pages/view.php', $urlparams);
$PAGE->navbar->add(get_string('pluginname_short','local_zilink'));
$PAGE->navbar->add(get_string('report_writer', 'local_zilink'));
$PAGE->navbar->add(get_string('report_writer_view', 'local_zilink'), $tt_url);
$PAGE->set_pagelayout('base');

$security = new ZiLinkSecurity();

if(!$security->IsAllowed('local/zilink:report_writer_subject_teacher_edit') &&
    !$security->IsAllowed('local/zilink:report_writer_subject_leader_edit') &&
    !$security->IsAllowed('local/zilink:report_writer_senior_management_team_edit'))
{
    redirect($CFG->httpswwwroot.'/course/view.php?id='.$courseid,get_string('requiredpermissionmissing','local_zilink'),1);
}

try {

    $report_writer = new ZiLinkReportWriter($courseid);
    $content = $report_writer->View(array(   'action'       => optional_param('action', 'list', PARAM_RAW),
                                             'rid'          => optional_param('rid', 0, PARAM_INTEGER),
                                             'uid'          => optional_param('uid', 0, PARAM_INTEGER),
                                             'cohortid'     => optional_param('cohortid', 0, PARAM_INTEGER)));
    
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
