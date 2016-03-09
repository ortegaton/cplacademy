<?php
require_once("../../config.php");
require_once("lib.php");
require_once("$CFG->dirroot/mod/rmc/locallib.php");

require_login();
global $DB, $CFG;

$token =  required_param('t', PARAM_RAW);

$query = "SELECT node_id FROM {rmc_embed_url_token} WHERE embed_token = '". trim($token) ."'";
$rec = $DB->get_record_sql($query);
if($rec) {
	$node_id = $rec->node_id;
	$cmis_client = new cmis_client();
	$node_obj = $cmis_client->get_item_info($node_id, 'yes');
	$file_name = $node_obj->properties['cmis:name'];
	$file_type = $node_obj->properties['cmis:contentStreamMimeType'];
	$file_size = $node_obj->properties['cmis:contentStreamLength'];
	$alf_ticket = $cmis_client->get_ticket();
	$auth_url = $cmis_client->get_rmc_auth_url($node_obj) . '&ticket=' . $alf_ticket;
	$headers = array(
		'Referer' => $CFG->wwwroot
	);
	$file_content = download_file_content($auth_url, $headers);
	if(strpos($file_content, 'sales@vetcommons.edu.au')) {
		echo "not allowed";
		die;
	}
	header('Content-Description: File Transfer');
    header('Content-Type: ' . $file_type);
    header('Content-Disposition: inline; filename='.basename($file_name));
    //header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . $file_size);
    echo $file_content;
}
die;