<?php
require_once("../../config.php");
require_once("$CFG->dirroot/course/lib.php");
require_once("$CFG->dirroot/lib/weblib.php");
require_once("$CFG->dirroot/mod/rmc/locallib.php");

global $DB, $USER, $CFG;
$section = required_param('section', PARAM_INT);
$content_title = required_param('item', PARAM_TEXT);
$course_id = required_param('course_id', PARAM_RAW);
$uuid = required_param('node_id', PARAM_TEXT);
$cmid = optional_param('cmid', 0, PARAM_INT);
$sr = optional_param('sr', 0, PARAM_INT);
$purchase_id = optional_param('purchase_id', 0, PARAM_INT);
$agreement = optional_param('agreement', 'No', PARAM_TEXT);
$publisher_id = required_param('publisher_id', PARAM_TEXT);	
$authorise_mail = required_param('authorise_mail', PARAM_EMAIL);
$publisher_email = required_param('publisher_email', PARAM_EMAIL);
$no_licenses = optional_param('user_count', 0, PARAM_INT);
$enrol_count = optional_param('enrol_count', 0, PARAM_INT);


/* $purchase_status = rmc_helper::get_node_purchase_status($USER->id, $uuid);
if($purchase_status) {
	echo get_string('content_already_purchased', 'mod_rmc');
	die;
} */
$data = array();
rmc_helper::add_purcharse_entry($course_id, $uuid, $no_licenses, $authorise_mail);
rmc_helper::add_rmc_to_course($course_id, $section, $sr, $purchase_id, $uuid, $content_title, 0);
$mail_info = rmc_helper::get_mail_content($uuid, $course_id,$agreement);
$course_obj = $DB->get_record('course', array('id'=> $course_id));
$variable_list = array(
		'{item.name}' => $mail_info['item_name'],
		'{item.cost}' => $mail_info['item_cost'],
		'{item.id}' => str_replace('workspace://SpacesStore/', '' , $uuid),
		'{item.url}' => rmc_helper::get_auth_url($uuid, $course_id),
		'{item.resourcetype}' => $mail_info['resource_type'],
		'{item.publisher}' => $publisher_id,
		'{course.name}' => $course_obj->fullname,
		'{course.url}' => $CFG->wwwroot . '/course/view.php?id='.$course_id,
		'{rmc.licences}' => $no_licenses,
		'{rmc.purchasername}' => $USER->firstname .' '. $USER->lastname . ' ('. $USER->username . ')',
		'{rmc.purchaseremailaddress}' => $authorise_mail,
		'{rmc.customername}' => rmc_helper::get_customer_name(),
		'{course.enrolments}' => $enrol_count
		);
$cus_intro_mail = rmc_helper::process_html($mail_info['mail_data']->cus_intro_mail, $variable_list);
$cus_outro_mail = rmc_helper::process_html($mail_info['mail_data']->cus_outro_mail, $variable_list);
$publisher_html = rmc_helper::process_html($mail_info['mail_data']->publisher_html, $variable_list);
$publisher_mail_subject = rmc_helper::process_html($mail_info['mail_data']->publisher_mail_subject, $variable_list);
$publisher_mail_html = rmc_helper::process_html($mail_info['mail_data']->publisher_mail_html, $variable_list);
$data['customer_mail'] = $cus_intro_mail . '<br /><br />' . $publisher_html . '<br /><br />' . $cus_outro_mail;
$variable_list['{rmc.purchaseremailbody}'] = stripslashes(stripslashes($data['customer_mail']));
$data['publisher_mail_html'] = rmc_helper::process_html($publisher_mail_html, $variable_list);
$data['publisher_mail_subject'] = rmc_helper::process_html($publisher_mail_subject, $variable_list);
$data['publisher_from_email'] = $mail_info['mail_data']->publisher_from_email;
$data['publisher_name'] = $mail_info['mail_data']->publisher_name;
$data['cust_mail_subject'] = $mail_info['cust_mail_subject'];
$data['authorise_mail'] = $authorise_mail;
$send_status = rmc_helper::send_mail($data);
if($send_status) {
	echo $CFG->wwwroot."/course/view.php?id=".$course_id;
}
else {
	echo "error";
}
die;
/* $send_status = rmc_helper::send_mail($_POST);
$send_status = false;
if($send_status) {
	$table = 'rmc_purchase_detail';
	$record = new stdClass();
	$record->course_id = $course_id;
	$record->user_id = $USER->id;
	$record->node_id = $_POST['node_id'];
	$record->alfresco_share_url = rmc_helper::get_auth_url($uuid, $course_id);
	$DB->insert_record($table, $record, false);
	echo $CFG->wwwroot."/course/view.php?id=".$course_id;
	die;
}
else {
	echo get_string('mail_fail', 'mod_rmc');;
	die;
} */
