<?php 
require_once("../../config.php");
require_once("../../course/lib.php");
require_once("../../lib/weblib.php");
require_once("{$CFG->dirroot}/mod/rmc/advanced_search_form.php");

$course_id = required_param('course', PARAM_INT);

$system_context = get_context_instance(CONTEXT_SYSTEM);
$course_obj = $DB->get_record('course', array('id' => $course_id), 'fullname', MUST_EXIST);
$PAGE->set_context($system_context);
$PAGE->requires->css('/mod/rmc/css/rmc.css');
$PAGE->set_url('/mod/rmc/advanced_search.php');
$course_url = new moodle_url('/course/index.php');
$view_url = new moodle_url('/course/view.php?id='.$course_id);
$PAGE->navbar->add(get_string('courses'),$course_url);
$PAGE->navbar->add($course_obj->fullname,$view_url);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('standard');
$PAGE->set_button("");

echo $OUTPUT->header();

$img_html = '<div class="smalltree">'.get_string('search_result', 'rmc').' &nbsp; <img src="pix/logo3.png"/></div>';

echo $OUTPUT->heading($img_html);

$advanced_search_form = new advanced_search_form('search.php');
$advanced_search_form->display();

echo $OUTPUT->footer();


