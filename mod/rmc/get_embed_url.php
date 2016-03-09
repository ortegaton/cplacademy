<?php
require_once("../../config.php");
require_once("lib.php");
require_once("$CFG->dirroot/mod/rmc/locallib.php");

require_login();
global $DB;

//$course_id = required_param('course', PARAM_INT);
$node_id = required_param('node_id', PARAM_TEXT);
$token = rmc_helper::get_embed_token($node_id);
$token_url = $CFG->wwwroot . '/mod/rmc/embed.php?t=' . $token;
echo $token_url;
die;