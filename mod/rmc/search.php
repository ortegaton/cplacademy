<?php

/*
 * this page can receive posts from itself and from advanced search
 * it is redirected to from course/mod.php
 * we may be in add mode or update mode
 * 
 */
require_once("../../config.php");
require_once($CFG->dirroot . '/course/lib.php');
require_once("lib.php");
require_once("$CFG->dirroot/mod/rmc/locallib.php");
require_once("{$CFG->dirroot}/mod/rmc/search_form.php");

require_login();
//write to log
$logid = rmc_write_to_log('start', 0, __FILE__, '', $_POST);
//params
$submitbutton = optional_param('submitbutton', 'None', PARAM_RAW);
$search_type = optional_param('search_type', 'FTS', PARAM_RAW);  // search words
$search_type = $search_text = trim(strip_tags($search_type)); // trim & clean raw search type
$search_text = optional_param('search_string', ' ', PARAM_TEXT);;
$page = optional_param('page', 0, PARAM_INT);     // which page to show
$perpage = 10; // how many per page
$course = required_param('course', PARAM_RAW);
$section = required_param('section', PARAM_INT);
$cmid = optional_param('cmid', 0, PARAM_INT);
$search_string = optional_param('search_string', '', PARAM_TEXT);  // search words
$publisher = optional_param_array('publisher', array(), PARAM_TEXT);
$discipline = optional_param_array('discipline', array(), PARAM_TEXT);
$training_package = optional_param_array('training_package', array(), PARAM_TEXT);
$resource_type = optional_param_array('resource_type', array(), PARAM_TEXT);
$type = optional_param('type', '', PARAM_ALPHA);
$returntomod = optional_param('return', 0, PARAM_BOOL);
$sr = optional_param('sr', 0, PARAM_BOOL);

$is_valid = rmc_helper::validate_rmc_installation();

$search_string = trim(strip_tags($search_string)); // trim & clean raw searched string
//Set PAGE properties
$page_url = new moodle_url('/mod/rmc/search.php', array('submitbutton' => $submitbutton, 'search_type' => $search_type, 'page' => $page, 'perpage' => $perpage, 'course' => $course, 'section' => $section,
    //'type' => $type, 'return' => $returntomod, 'sr' => $sr, 
    'search_string' => $search_string));
$PAGE->set_url($page_url);
$system_context = context_system::instance();
$PAGE->set_context($system_context);
$course_url = new moodle_url('/course/index.php');
$view_url = new moodle_url('/course/view.php?id=' . $course);
$PAGE->navbar->add(get_string('courses'), $course_url);
$course_obj = $DB->get_record('course', array('id' => $course), 'fullname', MUST_EXIST);
$PAGE->navbar->add($course_obj->fullname, $view_url);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->requires->css('/mod/rmc/css/rmc.css');
$PAGE->requires->css('/mod/rmc/css/kendo.common.min.css');
$PAGE->requires->css('/mod/rmc/css/kendo.default.min.css');
$PAGE->requires->css('/mod/rmc/css/tooltipster.css');
$PAGE->requires->jquery();
//$PAGE->requires->js('/mod/rmc/js/jquery-1.9.0.min.js', TRUE);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/rmc/js/kendojs/kendo.web.min.js'), TRUE);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/rmc/js/jquery.tooltipster.js'), TRUE);
$PAGE->requires->js("/mod/rmc/js/jquery.fancybox.js");
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/rmc/js/rmc.js'), TRUE);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/rmc/js/embed_url.js'), TRUE);
$PAGE->requires->css("/mod/rmc/css/jquery.fancybox.css"); 

$PAGE->set_button("");

echo $OUTPUT->header();

if ($search_string != "") {
    $client = new cmis_client();
    if ($search_type == 'FTS') {
        //Perform full text searching
        $search_result = $client->search_fts($search_string, $page);
    } else if ($search_type == 'advanced') {
        //Perform advance searching
        $search_result = $client->search_advanced($search_string, $publisher, $discipline, $training_package, $resource_type);
    }
}
$img_html = '<div class="smalltree">'.get_string('search_result', 'rmc').' &nbsp; <img src="pix/logo3.png"/></div>';
        
echo $OUTPUT->heading('');
echo '<div class="rmc_logo"></div>';
if($is_valid) {
$search_form = new search_form();
$search_form->display();
$nodes = array();
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
				$repo_id = $search_result[$i]['repo_id'];
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
				$temp['link_post_url'] = $post_url;
				$temp['purchase_id'] = $purchase_id;
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

echo "<script>var search_result = ".json_encode($nodes)."; var page_no = ".$page."; var base_path = '". $CFG->wwwroot ."';</script>";
echo "<script>var img_data = '". rmc_helper::get_spinner_binary() ."';</script>";
if(count($nodes) > 0) {
	echo "<div id='search-result'><div id='search-load-image' style='text-align: center !important;'><img  src='$CFG->wwwroot/mod/rmc/pix/search-progress-bar.gif' /> <br /> ". get_string('search_loading_message', 'rmc') ."</div></div>";
}
echo "<div id='search-result-message'></div>";
echo "<div id='search-result'></div>";
echo "<div id='embed_popup' style='display: none;'></div>";
} else {
	echo '<p style="border: 1px solid grey !important; width: 80% !important;font-family: sans-serif !important; text-align: center !important; padding : 11px !important;">'. get_string('invalid_rmc_error', 'rmc') .'</p></div>';
}

rmc_write_to_log('end', $logid);
?>
<script id="searchRowTemplate" type="text/x-kendo-tmpl">
	            <tr>
		            <td >
                      <div class='thumb_crop'>
                          <a href=#: url#><img alt = '#: thumbnail_desc#' title = '#: thumbnail_desc#'  src = '#: thumbnail#'/></a>
                      <div>
		            </td>
		            <td class="details">
			           <div class='search_content' #: backcolor# ><span class='color #: color_class#'></span>
								   <h1 class='search-title-align'><a href=#: url#>#: node_title#</a><span class='time_stamp'>#if(titleTitle != 'NA'){#(#: titleTitle#)#}#</span>	</h1><p>#: description_text#</p>
								                 #if(has_publisher){#	
																			#if(publisherID != 'NA'){#
																			<div class='data_guide'>#: publisher_id_label#:&nbsp;<span>#: publisherID#</span></div>
																			#}#
																			#if(resourceType != 'NA'){#
																			<div class='data_guide'>#: resource_type_label#:&nbsp;<span>#: resourceType#</span></div>
																			#}#
																			#if(qualification != 'NA'){#
																			<div class='data_guide'>#: qualification_label#:&nbsp;<span>#: qualification#</span></div>
																			#}#
																			#if(education_level != 'NA'){#
								   									  <div class='data_guide'>#: education_label#:&nbsp;<span>#: education_level#</span></div>
																			#}#
																			#if(trainingPackage != 'NA'){#
	                 										<div class='data_guide'>#: training_package_label#:&nbsp;<span>#: trainingPackage#</span></div>
																			#}#																	
                                   #} else { #
																			#if(competency_text != 'NA'){#
																			<div class='data_guide'>#: competency_label#:&nbsp;<span>#: competency_text#</span></div>
																			#}#
																			#if(education_level != 'NA'){#
																		  <div class='data_guide'>#: education_label#:&nbsp;<span>#: education_level#</span></div>
																			#}#
																			#if(trainingPackage != 'NA'){#
																			<div class='data_guide'>#: training_package_label#:&nbsp;<span>#: trainingPackage#</span></div>
																			#}#
																			#if(toolbox != 'NA'){#
																			<div class='data_guide'>#: toolbox_label#:&nbsp;<span>#: toolbox#</span></div>
																			#}#
																	 # } #
                                  </div>
		            </td>
	           </tr>
            </script>
<?php $PAGE->requires->js('/mod/rmc/js/search-grid.js'); echo $OUTPUT->footer(); echo '<div id="preloader" width="100%" height="100%"></div>'; ?>

