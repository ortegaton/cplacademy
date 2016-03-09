<?php

require_once(dirname(__FILE__) . '/../../../../../../../../config.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();
$sesskey = required_param('sesskey',PARAM_RAW);
$offset = required_param('offset',PARAM_INT);
confirm_sesskey($sesskey);

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => $sesskey);
$PAGE->https_required();
$PAGE->set_url('/local/zilink/plugins/guardian/view/pages/default/index.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('guardian_view_default_timetable_page_title', 'local_zilink');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->requires->css('/local/zilink/plugins/timetable/styles.css');
$PAGE->requires->css('/local/zilink/plugins/guardian/view/interfaces/default/styles.css');

$PAGE->navbar->add(get_string('pluginname_short','local_zilink'));
$PAGE->navbar->add(get_string('guardian', 'local_zilink'));
$PAGE->navbar->add(get_string('view', 'local_zilink'));
$PAGE->navbar->add(get_string('guardian_view_default_timetable', 'local_zilink'), new moodle_url('/local/zilink/plugins/guardian/view/pages/default/index.php', $urlparams));

$PAGE->set_pagelayout('base');

$security = new ZiLinkSecurity();

if(!$security->IsAllowed('local/zilink:guardian_view'))
{
    redirect($CFG->httpswwwroot.'/course/view.php?id='.$courseid,get_string('requiredpermissionmissing','local_zilink'),1);
}

try {

    $portal = new ZiLinkGuardianView($offset);
    $content = $portal->Timetable();
    
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
