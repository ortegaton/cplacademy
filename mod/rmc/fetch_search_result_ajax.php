<?php
require_once("../../config.php");
require_once("lib.php");
require_once("$CFG->dirroot/mod/rmc/locallib.php");

require_login();
global $SESSION;
$nodes = array();
$course = optional_param('course', 0, PARAM_INT);
if($course == 0) {
	redirect($SESSION->fromurl);
}
$submitbutton = optional_param('submitbutton', 'None', PARAM_RAW);
$search_type = optional_param('search_type', 'FTS', PARAM_RAW);  // search words
$search_type = $search_text = trim(strip_tags($search_type)); // trim & clean raw search type
$search_text = optional_param('search_string', ' ', PARAM_TEXT);;
$page = optional_param('page', 0, PARAM_INT);     // which page to show
$perpage = 10; // how many per page
$section = required_param('section', PARAM_INT);
$cmid = optional_param('cmid', 0, PARAM_INT);
$search_string = optional_param('search_string', '', PARAM_TEXT);  // search words
$publisher = optional_param_array('publisher', array(), PARAM_RAW);
$discipline = optional_param_array('discipline', array(), PARAM_RAW);
$training_package = optional_param_array('training_package', array(), PARAM_RAW);
$resource_type = optional_param_array('resource_type', array(), PARAM_RAW);
$type = optional_param('type', '', PARAM_ALPHA);
$returntomod = optional_param('return', 0, PARAM_BOOL);
$sr = optional_param('sr', 0, PARAM_BOOL);

$search_string = trim(strip_tags($search_string));

if($search_type == 'advanced') {
	$client = new cmis_client();
	//Perform advance searching
	$search_result = $client->search_advanced($search_string, $publisher, $discipline, $training_package, $resource_type);
} else if($search_type == 'FTS' && $search_string != "") {
	$client = new cmis_client();
	//Perform full text searching
	$search_result = $client->search_fts($search_string, $page);
}

if (isset($search_result['result'])) {
	$result_count = $search_result['total_count'];
	if($result_count > 50) {
		$message =  '<div class="result_label"> ' . get_string('result_found_more', 'rmc') . '</div>';
	} else {
		$message =  '<div class="result_label"><strong>' . $result_count . '</strong> ' . get_string('result_found', 'rmc') . '</div>';
	}
	if ($result_count != 0) {
		/* $table = new html_table();
		 $table->attributes['class'] = 'search_result';
		$table->head = array(
				$image,
				$desc
		); */
		//$start_index = $perpage * $page;
		$class_id = 1;
		$is_admin = is_siteadmin();
		$search_result = $search_result['result'];
		$labels = array();
		$labels['modified_label'] = get_string('modified_on', 'mod_rmc');
		$labels['competency_label'] = get_string('competency_label', 'rmc');
		$labels['education_label'] = get_string('education_label', 'rmc');
		$labels['toolbox_label'] = get_string('toolbox_label', 'rmc');
		$labels['publisher_id_label'] = get_string('publisher_id_label', 'rmc');
		$labels['resource_type_label'] = get_string('resource_type_label', 'rmc');
		$labels['publisher_name_label'] = get_string('publisher_name_label', 'rmc');
		$labels['embed_button_text'] = get_string('embed_button_text', 'rmc');
		$labels['embed_button_label'] = get_string('embed_button_label', 'rmc');
		$labels['qualification_label'] = get_string('qualification_label', 'rmc');
		$labels['training_package_label'] = get_string('training_package_label', 'rmc');
		for ($i = 0; $i <= count($search_result); $i++) {
			$link_html = "";
			if (isset($search_result[$i])) {
				if ($class_id == 6) {
					$class_id = 1;
				}

				$competency_text = ' ';
				$education_level = ' ';
				if(isset($search_result[$i]['repo_id'])) {
					$repo_id = $search_result[$i]['repo_id'];
				} else {
					$repo_id = '';
				}
				$node_id = $search_result[$i]['source'];
				$purchase_status = rmc_helper::get_node_purchase_status($USER->id, $node_id);
				$url = $CFG->wwwroot."/mod/rmc/view_content.php?repo_id=$repo_id&type=$type&course=$course&section=$section&return=$returntomod&sr=$sr&node_id=$node_id&cmid=$cmid&search_string=".urlencode($search_string)."&search_type=$search_type";
				$node_title = $search_result[$i]['title'];
				$resource_type = $search_result[$i]['resourceType'];
				$description_text = $search_result[$i]['description'];
				$discipline_text = $search_result[$i]['discipline'];
				$training_package_text = $search_result[$i]['trainingPackage'];
				if(isset($search_result[$i]['toolbox'])) {
					$toolbox = $search_result[$i]['toolbox'];
				} else {
					$toolbox = 'NA';
				}
				$count = 0;
				if ($search_result[$i]['costValue'] == "no" || trim($search_result[$i]['costValue']) == "" && strstr($search_result[$i]['file_type'], 'image'))  {
					$is_free = TRUE;
				} else {
					$is_free = FALSE;
				}
				if(!isset($search_result[$i]['competency'])) {
					$search_result[$i]['competency'] = array();
				}
				if (is_array($search_result[$i]['competency'])) {
					$competency_text = implode(', ', $search_result[$i]['competency']);
					/* foreach ($search_result[$i]['competency'] as $competency) {
					 if ($count != 0) {
					$competency_text .= ' , ';
					}
					$competency_text .= $competency;
					$count++;
					} */
				} else {
					if(isset($search_result[$i]['competency'])) {
						$competency_text = $search_result[$i]['competency'];
					} else {
						$competency_text = 'NA';
					}
				}
				$count = 0;
				if (is_array($search_result[$i]['education_level'])) {
					$education_level = implode(', ', $search_result[$i]['education_level']);
					/* foreach ($search_result[$i]['education_level'] as $education) {
					 if ($count != 0) {
					$education_level .= ' , ';
					}
					$education_level .= $education;
					$count++;
					} */
				} else {
					if(isset($search_result[$i]['education_level'])) {
						$education_level = $search_result[$i]['education_level'];
					} else {
						$education_level = 'NA';
					}
				}
				if(is_array($search_result[$i]['qualification'])) {
					$qualification_text = implode(', ', $search_result[$i]['qualification']);
				} else {
					$qualification_text = $search_result[$i]['qualification'];
				}
				$color_class = "search_result" . $class_id;
				$class_id++;
				if ($purchase_status) {
					$purchase_id = rmc_helper::get_purchase_id($node_id);
					$post_url = "$CFG->wwwroot/mod/rmc/add_rmc_content.php?course=$course&section=$section&prid=$purchase_id";
				}
				$publisher_info = "";
				$backcolor = "";
				if(isset($search_result[$i]['publisherID'])) {
					if($is_admin) {
						$backcolor = "style='background-color : ". $search_result[$i]['backcolor'] ."'" ;
					}
					$url = $url.'&hasPublisher=yes';
				} else {
					$url = $url.'&hasPublisher=no';
				}
				$temp = array();

				if(!$competency_text) {
					$competency_text = "NA";
				}
				if(!$education_level) {
					$education_level = "NA";
				}
				if(!$search_result[$i]['description'])  {
					$search_result[$i]['description'] = "";
				}
				if((!$search_result[$i]['titleTitle']) || (trim($search_result[$i]['pubResourceType']) == 'eBook') )  {
					$search_result[$i]['titleTitle'] = "NA";
				}

				$temp['url'] = $url;
				$temp['is_free'] = $is_free;
				$temp['thumbnail'] = $search_result[$i]['thumbnail'];
				$temp['backcolor'] = $backcolor;
				$temp['color_class'] = $color_class;
				$temp['node_title'] = $node_title;
				$temp['node_id'] = $node_id;
				$temp['titleTitle'] = $search_result[$i]['titleTitle'];
				//$temp['datemodified_f'] = $search_result[$i]['datemodified_f'];
				$temp['description_text'] = $search_result[$i]['description'];
				$temp['competency_text'] = $competency_text;
				$temp['education_level'] = $education_level;
				$temp['toolbox'] = $toolbox;
				$temp['link_html'] = $link_html;
				$temp['is_admin'] = $is_admin;
				$temp['has_publisher'] = isset($search_result[$i]['publisherID']);
				if(isset($search_result[$i]['publisherID'])) {
					$temp['publisherID'] = $search_result[$i]['publisherID'];
				} else {
					$temp['publisherID'] = 'NA';
				}
				$temp['publisherName'] = $search_result[$i]['publisherName'];
				if(isset($search_result[$i]['pubResourceType']) && trim($search_result[$i]['pubResourceType']) != '') {
					$temp['resourceType'] = $search_result[$i]['pubResourceType'];
				} else {
					$temp['resourceType'] = 'NA';
				}
				$temp['Id'] = $i + 1;
				$temp['purchase_status'] = false;
				//$temp['link_post_url'] = $post_url;
				//$temp['purchase_id'] = $purchase_id;
				$temp['uuid'] = $node_id;
				if(isset($qualification_text) && trim($qualification_text) != '') {
					$temp['qualification'] = $qualification_text;
				} else {
					$temp['qualification'] = 'NA';
				}

				if(isset($search_result[$i]['trainingPackage']) && trim($search_result[$i]['trainingPackage']) != '') {
					$temp['trainingPackage'] = $search_result[$i]['trainingPackage'];
				} else {
					$temp['trainingPackage'] = 'NA';
				}
				$temp['thumbnail_desc'] = $search_result[$i]['thumbnail_desc'];
				$temp = array_merge($temp, $labels);
				$nodes[] = $temp;
			}
		}
	}
}
$return = array('nodes' => $nodes, 'message' => $message);
echo json_encode($return); 
die;
