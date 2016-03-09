<?php

require_once('../../config.php');
require_once('lib.php');
require_once('locallib.php');
require_once($CFG->libdir.'/resourcelib.php');

require_login();
global $DB, $SITE, $PAGE, $USER, $OUTPUT;

$id = required_param('id', PARAM_INT);
$is_valid = rmc_helper::validate_rmc_installation();
if (!$cm = get_coursemodule_from_id('rmc', $id)) {
    print_error('Course Module ID was incorrect');
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');
}
if (!$rmc_obj = $DB->get_record('rmc', array('id'=> $cm->instance))) {
    print_error('resource id is incorrect');
}

if (!$rmc_content_obj = $DB->get_record('rmc_purchase_detail', array('id'=> $rmc_obj->purchase_id))) {
    print_error('content id is incorrect');
}

// Check login and get context.
require_login($course, false, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
$PAGE->set_url('/mod/rmc/view.php', array('id' => $cm->id));
$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->requires->css('/mod/rmc/css/rmc.css');
$PAGE->requires->jquery();
$PAGE->requires->js("/mod/rmc/js/jquery.fancybox.js", TRUE);
$PAGE->requires->js("/mod/rmc/js/embed_url.js", TRUE);
$PAGE->requires->css("/mod/rmc/css/jquery.fancybox.css");

/* $client = new cmis_client();
$frame_height = 'height="100%"';
$node_obj = $client->get_item_info($rmc_content_obj->node_id);
if(strstr($node_obj->properties['cmis:contentStreamMimeType'], 'video')) {
	$frame_height = 'height="500px"';
} else if(strstr($node_obj->properties['cmis:contentStreamMimeType'], 'audio')) {
	$frame_height = 'height="100%"';
} else {
	$frame_height = 'height="1000px"';
}

echo $OUTPUT->header();
echo '<h2 class="main" id="urlheading">'.$rmc_obj->name.'</h2>';
echo '<iframe frameBorder="0" width="100%" '. $frame_height. ' src="'.$rmc_content_obj->alfresco_share_url.'"></iframe> ';
echo $OUTPUT->footer(); */
if($is_valid) {
$client = new cmis_client();
$display = $rmc_obj->display;
$ticket = $client->get_ticket();
$rmc_content_obj->alfresco_share_url .= "&ticket=" . $ticket;

switch($display) {
	case RESOURCELIB_DISPLAY_EMBED:
	case RESOURCELIB_DISPLAY_AUTO:
		$frame_height = 'height="100%"';
		$node_obj = $client->get_item_info($rmc_content_obj->node_id);
		if(strstr($node_obj->properties['cmis:contentStreamMimeType'], 'video')) {
			$frame_height = 'height="500px"';
		} else if(strstr($node_obj->properties['cmis:contentStreamMimeType'], 'audio')) {
			$frame_height = 'height="100%"';
		} else {
			$frame_height = 'height="1000px"';
		}
		$PAGE->requires->js_init_code("window.base_path = '". $CFG->wwwroot ."';");
		echo $OUTPUT->header();
		echo '<h2 class="main" id="urlheading">'.$rmc_obj->name.'</h2>';
		if(strstr($node_obj->properties['cmis:contentStreamMimeType'], 'image') && ($USER->editing == '1')) {
			echo '<input type="button" data-id="'. $rmc_content_obj->node_id .'" value="'. get_string('gen_embed_url_lbl', 'rmc') .'" id="rmc_generate_url" class="view-con-button" />';
		}
		echo '<iframe frameBorder="0" width="100%" '. $frame_height. ' src="'.$rmc_content_obj->alfresco_share_url.'"></iframe> ';
		echo "<div id='embed_popup' style='display: none;'></div>";
		echo $OUTPUT->footer();
		break;
	case RESOURCELIB_DISPLAY_POPUP:
	case RESOURCELIB_DISPLAY_OPEN:
		$frame_height = 'height="100%"';
		$node_obj = $client->get_item_info($rmc_content_obj->node_id);
		$fit_content_frame_to_window = true;
		if(strstr($node_obj->properties['cmis:contentStreamMimeType'], 'video')) {
			$frame_height = 'height="500px"';
			$fit_content_frame_to_window = false;
		} else if(strstr($node_obj->properties['cmis:contentStreamMimeType'], 'audio')) {
			$frame_height = 'height="100%"';
			$fit_content_frame_to_window = false;
		} else {
			$frame_height = 'height="1000px"';
		}
		echo '<!doctype html>
			<html lang="en">
			<head>
			<style>
				html, body, h2, iframe {padding: 0; margin: 0; border: none}
				h2 {padding: 0.25em 1em}
			</style>
			<script>
			';
		if ($fit_content_frame_to_window) {
			echo '
			function fitContentFrameToWindow () {
				var w = window,
					d = document,
					e = d.documentElement,
					g = d.getElementsByTagName("body")[0],
					y = w.innerHeight|| e.clientHeight|| g.clientHeight,
					c = document.getElementById("content-frame"),
					p = 6; /* Extra space for bottom of the window help avoid double scroll bars in IE. */
				c.style.height = y - c.offsetTop - p + "px";
			}
			window.onload = fitContentFrameToWindow;
			window.onresize = fitContentFrameToWindow;
			';
		}
		echo 'window.base_path = "'. $CFG->wwwroot .'"
			</script>
			</head>
			<body>
			';
		if ($display == RESOURCELIB_DISPLAY_OPEN) {
			echo '<h2 class="main" id="urlheading">'.$rmc_obj->name.'</h2>';
			if(strstr($node_obj->properties['cmis:contentStreamMimeType'], 'image') && ($USER->editing == '1')) {
				echo '<input type="button" data-id="'. $rmc_content_obj->node_id .'" value="'. get_string('gen_embed_url_lbl', 'rmc') .'" id="rmc_generate_url" class="view-con-button" />';
			}
		}
		echo '<iframe id="content-frame" frameBorder="0" width="100%" '. $frame_height. ' src="'.$rmc_content_obj->alfresco_share_url.'"></iframe> ';
		echo "<div id='embed_popup' style='display: none;'></div>";
		echo "</body></html>";
		break;
}
} else {
	echo '<p style="border: 1px solid grey !important; width: 80% !important;font-family: sans-serif !important; text-align: center !important; padding : 11px !important;">'. get_string('invalid_rmc_error', 'rmc') .'</p></div>';
}

