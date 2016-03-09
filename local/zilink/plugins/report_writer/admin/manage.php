<?php

require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();
$courseid = optional_param('cid',1,PARAM_INTEGER);
//$sesskey = required_param('sesskey',PARAM_RAW);
//confirm_sesskey($sesskey);

if (!$courseid == SITEID) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = context_system::instance();
    $PAGE->set_context($context);
}

$urlparams = array('cid' => $courseid, 'sesskey' => sesskey());
$PAGE->https_required();
$PAGE->set_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('report_writer_page_title', 'local_zilink');

admin_externalpage_setup('zilink_report_writer_config_settings',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/report_writer/admin/manage.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->requires->css('/local/zilink/plugins/report_writer/interfaces/default/styles.css');
$tt_url = new moodle_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams);
//$PAGE->navbar->add(get_string('pluginname_short','local_zilink'));
//$PAGE->navbar->add(get_string('report_writer', 'local_zilink'));
$PAGE->navbar->add(get_string('report_writer_manage_reports', 'local_zilink'), $tt_url);
$PAGE->set_pagelayout('base');

$security = new ZiLinkSecurity();

if(!$security->IsAllowed('local/zilink:report_writer_configure'))
{
    redirect($CFG->httpswwwroot.'/course/view.php?id='.$courseid,get_string('requiredpermissionmissing','local_zilink'),1);
}

try {

    $timetable = new ZiLinkReportWriterManager($courseid);
    $content = $timetable->Manage(array(    'rid'    => optional_param('rid', 0, PARAM_INTEGER),
                                            'tid'        => optional_param('tid', 0, PARAM_INTEGER),
                                            'ctid'        => optional_param('ctid', 0, PARAM_INTEGER),
                                            'ctname'      => optional_param('ctname', 0, PARAM_RAW),
                                            'cttype'      => optional_param('cttype', 0, PARAM_RAW),
                                            'action'      => optional_param('action', 'view', PARAM_TEXT),
                                            'step'        => optional_param('step', 'first', PARAM_TEXT)));

} catch (Exception $e)  {
    
    $message = $e->getMessage();
    //$message = get_string('requireddatamissing','local_zilink');
    
    if($CFG->debug == DEBUG_DEVELOPER) {
            
        $message .= '<br>';
        $message .= '<pre>'. print_r($e->getTrace(),true).'</pre>';
         
    }
    
    redirect($CFG->httpswwwroot.'/course/view.php?id='.$courseid,$message,1);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report_writer_manage_title', 'local_zilink'));
echo $OUTPUT->box(get_string('report_writer_manage_title_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('report_writer_support_desc', 'local_zilink').html_writer::link('http://support.schoolsict.net/hc',get_string('support_site','local_zilink'),array('target'=> '_blank')));
echo $OUTPUT->box(get_string('zilink_plugin_beta', 'local_zilink').get_string('zilink_plugin_support_desc', 'local_zilink') .html_writer::link('http://support.zilink.co.uk/hc/en-us/articles/200914139',get_string('support_site','local_zilink'),array('target'=> '_blank')),array('generalbox','error'));
echo '<br /><br />'.$content;
echo $OUTPUT->footer();
