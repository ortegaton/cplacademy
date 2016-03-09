<?php

require_once(dirname(__FILE__) . '/../../../../../../../../config.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();
$courseid = required_param('cid',PARAM_RAW);
$sesskey = required_param('sesskey',PARAM_RAW);
$view = optional_param('view','all',PARAM_RAW);
$subject = optional_param('subject',0,PARAM_INT);
$uid = optional_param('uid',0,PARAM_INT);
confirm_sesskey($sesskey);
$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => $sesskey);
$PAGE->https_required();
$PAGE->set_url('/local/zilink/plugins/class/view/interfaces/default/pages/index.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('class_view_general_page_title', 'local_zilink');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->requires->css('/local/zilink/plugins/class/view/styles.css');
$PAGE->requires->js('/local/zilink/plugins/class/view/module.js');

$PAGE->navbar->add(get_string('pluginname_short','local_zilink'));
$PAGE->navbar->add(get_string('class', 'local_zilink'));
$PAGE->navbar->add(get_string('view', 'local_zilink'), new moodle_url('/local/zilink/plugins/class/view/pages/default/index.php', $urlparams));


$PAGE->set_pagelayout('base');

$security = new ZiLinkSecurity();

if(!$security->IsAllowed('local/zilink:class_view'))
{
    redirect($CFG->httpswwwroot.'/course/view.php?id='.$courseid,get_string('requiredpermissionmissing','local_zilink'),1);
}

try {

    $portal = new ZiLinkClassView($courseid);
    if ($view == 'single') {
        $args = array('subject' => $subject, 'uid' => $uid);
        $content = $portal->View($args);
    } else {
        $content = $portal->Assessment();
    }
    
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