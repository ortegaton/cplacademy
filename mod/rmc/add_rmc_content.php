<?php
include_once("../../config.php");
include_once("../../course/lib.php");
include_once("../../lib/weblib.php");
include_once($CFG->libdir . '/adminlib.php');
include_once("$CFG->dirroot/mod/rmc/locallib.php");
global $DB;

require_login();

$section = required_param('section', PARAM_INT);
$content_title = required_param('content_title', PARAM_TEXT);
$course_id = required_param('course', PARAM_RAW);
$uuid = required_param('uuid', PARAM_TEXT);
$cmid = optional_param('cmid', 0, PARAM_INT);
$sr = optional_param('sr', 0, PARAM_INT);
$purchase_id = optional_param('prid', 0, PARAM_INT);

rmc_helper::add_rmc_to_course($course_id, $section, $sr, $purchase_id, $uuid, $content_title);
redirect("$CFG->wwwroot/course/view.php?id=$course_id");





