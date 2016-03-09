<?php
include_once("../../config.php");

$method = $_POST['method'];

switch($method) {
	case 'check_login':
		if(isloggedin()) {
			echo 'yes';
		} else {
			global $SESSION, $CFG;
			$course = $_POST['course'];
			$section = $_POST['section'];
			$cmis = $_POST['cmid'];
			$SESSION->wantsurl = $CFG->wwwroot . "/mod/rmc/search.php?course=$course&section=$section&cmid=$cmid";
			$login_url = get_login_url();
			echo $login_url;
			die;
		}
		break;
}
