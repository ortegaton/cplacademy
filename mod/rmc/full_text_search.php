<?php
require_once("../../config.php");
require_once("../../course/lib.php");
require_once("../../lib/weblib.php");
require_once($CFG->libdir.'/adminlib.php');
require_once("{$CFG->dirroot}/mod/rmc/full_text_search_form.php");

require_login();

$course_id = required_param('course', PARAM_INT);
$system_context = get_context_instance(CONTEXT_SYSTEM);
$courseobj = $DB->get_record('course', array('id' => $course_id), 'fullname', MUST_EXIST);
$urlparams = array();

//Set PAGE properties
$PAGE->set_context($system_context);
$PAGE->requires->css('/mod/rmc/css/rmc.css');
$PAGE->set_url('/mod/rmc/full_text_search.php', $urlparams);
$course_url = new moodle_url('/course/index.php');
$view_url = new moodle_url('/course/view.php?id='.$course_id);
$PAGE->navbar->add(get_string('courses'),$course_url);
$PAGE->navbar->add($courseobj->fullname,$view_url);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('standard');
$PAGE->set_button("");
if ($CFG->forcelogin) {
    require_login();
}
echo $OUTPUT->header();
$full_text_form = new full_text_search_form('search.php');
$full_text_form->display();

echo $OUTPUT->footer();
