<?php

require_once("../../config.php");
require_once("../../course/lib.php");
require_once("../../lib/weblib.php");
require_once($CFG->libdir . '/adminlib.php');
require_once("$CFG->dirroot/mod/rmc/locallib.php");
require_once("$CFG->dirroot/mod/rmc/mod_form.php");
require_once ($CFG->dirroot . '/mod/rmc/lib/decrypt.class.php');
require_once ($CFG->dirroot . '/mod/rmc/lib/config.php');

require_login();

global $COURSE, $USER, $DB, $OUTPUT, $SITE;
$node_id = required_param("node_id", PARAM_TEXT);
$course_id = required_param('course', PARAM_RAW);
$section = required_param('section', PARAM_INT);
$search_text = required_param('search_string', PARAM_TEXT);
$search_type = required_param('search_type', PARAM_TEXT);
$type = optional_param('type', '', PARAM_ALPHA);
$returntomod = optional_param('return', 0, PARAM_BOOL);
$sr = optional_param('sr', 0, PARAM_BOOL);
$cmid = optional_param('cmid', 0, PARAM_INT);
$saveandredirect = optional_param('saveandredirect', 0, PARAM_BOOL);
$token = $CFG->mod_rmc_token;
$purchaseid = optional_param('purchaseid', 0, PARAM_INT);
$hasPublisher = optional_param('hasPublisher', 'no', PARAM_TEXT);
$ocurl = new Curl();
$COURSE = $DB->get_record('course', array('id'=> $course_id));
$is_valid = rmc_helper::validate_rmc_installation();


/* $isvaliduser = $ocurl->post("http://localhost/andresco_webfront/webservice/service.php", 
														array(
																'token' => $token,  
																'uname' => $USERNAME->name, 
																'sitename' => $SITE->shortname,
																'from' => $CFG->wwwroot,
																'method' => 'validate_customer')); */
if($is_valid) {
//rmc_helper::validate_customer();
$client = new cmis_client();
$base_url = substr(Decryption::decrypt(ALFRESCO_URL), 0, strpos(Decryption::decrypt(ALFRESCO_URL), '/alfresco/s/cmis'));
$node_obj = $client->get_item_info($node_id, $hasPublisher);
$is_scorm = $client->is_scorm_package($node_id);



$params = array(
		'publisher_id' => $node_obj->properties["nvc:publisherID"],
		'node_id' => $node_id,
		'cost_value' => $node_obj->properties["nvc:costValue"],
		'course_sname' => $COURSE->shortname,
		'token' => $token,
		'uname' => $USER->username,
		'sitename' => $SITE->shortname,
		'from' => $CFG->wwwroot.'/',
		'method' => 'content_status'
);
$purchase_status = json_decode($ocurl->post(Decryption::decrypt(ALFRESCO_WEBFRONT_URL), $params));
if(isset($purchase_status->loginerror)) {
	//throw new Exception($purchase_status->message);
}

$share_url = $client->get_rmc_auth_url($node_obj, $course_id);

//$purchase_status = rmc_helper::get_node_purchase_status($USER->id, $node_id);
if(isset($node_obj->properties["nvc:classificationCompetency"])) {
	if (is_array($node_obj->properties["nvc:classificationCompetency"])) {
	    $node_obj->properties["nvc:classificationCompetency"] = implode(", ", $node_obj->properties["nvc:classificationCompetency"]);
	}
}

if (isset($node_obj->properties["nvc:thumbURL"]) && trim($node_obj->properties["nvc:thumbURL"]) != '') {
    $thumbnail_url = $base_url . $node_obj->properties["nvc:thumbURL"];
} else {
    $thumbnail_url = $client->get_andresco_thumbnail_url($node_obj);
}

//$share_url = $client->get_andresco_share_url($node_obj);

if($saveandredirect){
    //this happens if we are updating the RMC resource with a new url
    $sql = "SELECT pd.* FROM mdl_course_modules cm
        INNER JOIN mdl_modules m on cm.module = m.id
        INNER JOIN mdl_rmc r ON cm.instance = r.id
        INNER JOIN mdl_rmc_purchase_detail pd ON r.purchase_id = pd.id
        WHERE m.name = 'rmc' and cm.id = $cmid";
    $existing_purchase_detail = $DB->get_record_sql($sql);
    if($existing_purchase_detail){
        //$DB->delete_records('rmc_purchase_detail',array('id',$existing_purchase_detail->id));
        $existing_purchase_detail->node_id = $node_id;
        $existing_purchase_detail->alfresco_share_url = $share_url;
        $DB->update_record('rmc_purchase_detail',$existing_purchase_detail);         
    }
    else{
        $rmc_purchase_detail = new stdClass();
        $rmc_purchase_detail->course_id = $course_id;
        $rmc_purchase_detail->user_id = $USER->id;
        $rmc_purchase_detail->node_id = $node_id;
        $rmc_purchase_detail->alfresco_share_url = $share_url;
        $id = $DB->insert_record('rmc_purchase_detail',$rmc_purchase_detail);
        //update the rmc table with this purchase detail now
        $sql = "SELECT r.* FROM mdl_course_modules cm
        INNER JOIN mdl_modules m on cm.module = m.id
        INNER JOIN mdl_rmc r ON cm.instance = r.id
        WHERE m.name = 'rmc' and cm.id = $cmid";
        $rmc = $DB->get_record_sql($sql);
        $rmc->purchase_id = $id;
        $DB->update_record('rmc',$rmc);  
    }
    //off we go
    redirect($CFG->wwwroot."/course/modedit.php?update=$cmid");
}
$PAGE->requires->jquery();
$PAGE->requires->js("/mod/rmc/js/jquery.mousewheel-3.0.6.pack.js");
$PAGE->requires->js("/mod/rmc/js/jquery.fancybox.js", TRUE);
$PAGE->requires->css('/mod/rmc/css/kendo.common.min.css');
$PAGE->requires->css('/mod/rmc/css/kendo.default.min.css');
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/rmc/js/kendojs/kendo.web.min.js'));
$PAGE->requires->js("/mod/rmc/js/rmc.js", TRUE);
$PAGE->requires->css("/mod/rmc/css/jquery.fancybox.css");


$course_obj = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);
$system_context = context_system::instance();
$PAGE->set_context($system_context);
$course_url = new moodle_url('/course/index.php');
$view_url = new moodle_url('/course/view.php?id=' . $course_id);
$page_url = new moodle_url("/mod/rmc/view_content.php?repo_id=&type=$type&course=$course_id&section=$section&return=$returntomod&sr=$sr&node_id=$node_id&cmid=$cmid&search_string=$search_text&search_type=$search_type&hasPublisher=$hasPublisher");
$PAGE->navbar->add(get_string('courses'), $course_url);
$PAGE->navbar->add($course_obj->fullname, $view_url);
$PAGE->requires->css('/mod/rmc/css/rmc.css');
$PAGE->requires->css('/mod/rmc/css/view_content.css');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('standard');
$PAGE->set_button("");
$PAGE->set_url($page_url);


//Set PAGE properties
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('search_result', 'rmc'));
echo "<div class='details_main'>";
if(isset($node_obj->properties['nvc:titleTitle']) && (trim($node_obj->properties["nvc:pubResourceType"]) != 'eBook') && trim($node_obj->properties['nvc:titleTitle']) != '') {
	echo "<h1>" . $node_obj->properties["cm:title"] . " (" . $node_obj->properties['nvc:titleTitle'] . ") </h1>";
	$content_title = $node_obj->properties["cm:title"] . " (" . $node_obj->properties['nvc:titleTitle'] . ")";
} else {
	echo "<h1>" . $node_obj->properties["cm:title"] . "</h1>";
	$content_title = $node_obj->properties["cm:title"];
}
echo "<div class='thumb_large' style='text-align: center; margin-bottom: 15px'><img src='$thumbnail_url' title='". $node_obj->properties["nvc:thumbDesc"] ."' alt='". $node_obj->properties["nvc:thumbDesc"] ."' style='width: auto !important; max-height: 300px !important' /></div>";

echo "<div><p>" . $node_obj->properties["cm:description"] . "</p></div>";

if(isset($node_obj->properties["nvc:publisherID"])){
	if(isset($node_obj->properties["nvc:classificationFutureDiscipline"]) && count($node_obj->properties["nvc:classificationFutureDiscipline"]) > 1){
	echo "<div><p><strong>" . get_string('discipline_label', 'rmc') . " : </strong></p>" . implode(', ', $node_obj->properties["nvc:classificationFutureDiscipline"]) . "</div>";
	}
	if(isset($node_obj->properties["nvc:publisherID"]) && (trim($node_obj->properties["nvc:publisherID"]) != '')){
		echo "<div><p><strong>" . get_string('publisher_id_label', 'rmc') . " : </strong></p>" . $node_obj->properties["nvc:publisherID"] . "</div>";
	}
	if(isset($node_obj->properties["nvc:pubResourceType"]) && (trim($node_obj->properties["nvc:pubResourceType"]) != '')){
		echo "<div><p><strong>" . get_string('resource_type_label', 'rmc') . " : </strong></p>" . $node_obj->properties["nvc:pubResourceType"] . "</div>";
	}
	if(isset($node_obj->properties["nvc:qualification"]) && trim($node_obj->properties["nvc:qualification"]) != ''){
		echo "<div><p><strong>" . get_string('qualification_label', 'rmc') . " :</strong></p>" . $node_obj->properties["nvc:qualification"] . "</div>";
	}
	if(isset($node_obj->properties["nvc:classificationCompetency"]) && trim($node_obj->properties["nvc:classificationCompetency"]) != ''){
		echo "<div><p><strong>" . get_string('competency_label', 'rmc') . " : </strong></p> " . $node_obj->properties["nvc:classificationCompetency"] . "</div>";
	}
	if(isset($node_obj->properties["nvc:classificationEducationalLevel"]) && (count($node_obj->properties["nvc:classificationEducationalLevel"]) > 1)){
		echo "<div><p><strong>" . get_string('education_label', 'rmc') . " : </strong></p> " . implode(', ', $node_obj->properties["nvc:classificationEducationalLevel"]) . "</div>";
	}
	if(isset($node_obj->properties["nvc:trainingPackage"]) && (trim($node_obj->properties["nvc:trainingPackage"]) != '')){
	    echo "<div><p><strong>" . get_string('trainingpackage_label', 'rmc') . " : </strong></p>" . $node_obj->properties["nvc:trainingPackage"] . "</div>";
	} 
	/* if(isset($node_obj->properties["cm:author"])){
	echo "<div><p><strong>" . get_string('author_label', 'rmc') . " : </strong></p> " . $node_obj->properties["cm:author"] . "</div>";
	}
	if(isset($node_obj->properties["pub:publisherName"])){
	echo "<div><p><strong>" . get_string('publisher_label', 'rmc') . " : </strong></p>" . $node_obj->properties["pub:publisherName"] . "</div>";
	} */
	
} else {
	if(isset($node_obj->properties["nvc:classificationFutureDiscipline"]) && (trim($node_obj->properties["nvc:classificationFutureDiscipline"]) != '')){
		echo "<div><p><strong>" . get_string('discipline_label', 'rmc') . " : </strong></p>" . $node_obj->properties["nvc:classificationFutureDiscipline"] . "</div>";
	}
	if(isset($node_obj->properties["nvc:classificationCompetency"]) && (trim($node_obj->properties["nvc:classificationCompetency"]) != '')){
		echo "<div><p><strong>" . get_string('competency_label', 'rmc') . " : </strong></p> " . $node_obj->properties["nvc:classificationCompetency"] . "</div>";
	}
	if(isset($node_obj->properties["nvc:classificationEducationalLevel"]) && (count($node_obj->properties["nvc:classificationEducationalLevel"]) > 1)){
		echo "<div><p><strong>" . get_string('education_label', 'rmc') . " : </strong></p> " . implode(', ', $node_obj->properties["nvc:classificationEducationalLevel"]) . "</div>";
	}
	if(isset($node_obj->properties["nvc:trainingPackage"]) && (trim($node_obj->properties["nvc:trainingPackage"]) != '')){
		echo "<div><p><strong>" . get_string('trainingpackage_label', 'rmc') . " : </strong></p>" . $node_obj->properties["nvc:trainingPackage"] . "</div>";
	}
	if(isset($node_obj->properties["nvc:toolboxCode"]) && (trim($node_obj->properties["nvc:toolboxCode"]) != '')){
		echo "<div><p><strong>" . get_string('toolbox_label', 'rmc') . " : </strong></p>" . $node_obj->properties["nvc:toolboxCode"] . "</div>";
	}
}
if(isset($node_obj->properties["nvc:costValue"])){
	if($node_obj->properties["nvc:costValue"] == 'no') {
		echo "<div><p><strong>" . get_string('costvalue_label', 'rmc') . " : </strong></p>" . strtoupper(get_string('content_free_label', 'rmc')) . "</div>";
	} else {
		echo "<div><p><strong>" . get_string('costvalue_label', 'rmc') . " : </strong></p>" . $node_obj->properties["nvc:costValue"] . "</div>";
	}
} else {
	echo "<div><p><strong>" . get_string('costvalue_label', 'rmc') . " : </strong></p>" . strtoupper(get_string('content_free_label', 'rmc')) . "</div>";
}

//$purchase_status = rmc_helper::get_content_status($node_obj, $course_id);

if ($purchase_status->status == TRUE) {
	//If the item is already allowed from the webfront then add a entry.
	$sql = "SELECT pd.* FROM {rmc_purchase_detail} pd
        WHERE user_id = $USER->id AND node_id = '$node_id'";
    $existing_purchase_detail = $DB->get_record_sql($sql);
    if(!$existing_purchase_detail){
        $rmc_purchase_detail = new stdClass();
        $rmc_purchase_detail->course_id = $course_id;
        $rmc_purchase_detail->user_id = $USER->id;
        $rmc_purchase_detail->node_id = $node_id;
        $rmc_purchase_detail->alfresco_share_url = $share_url;
        $purchase_id = $DB->insert_record('rmc_purchase_detail',$rmc_purchase_detail);
    }
    else{
        $purchase_id = $existing_purchase_detail->id;
        $rmc_purchase_detail = new stdClass();
        $rmc_purchase_detail->id = $existing_purchase_detail->id;
        $rmc_purchase_detail->course_id = $course_id;
        $rmc_purchase_detail->user_id = $USER->id;
        $rmc_purchase_detail->node_id = $node_id;
        $rmc_purchase_detail->alfresco_share_url = $share_url;
        $DB->update_record('rmc_purchase_detail', $rmc_purchase_detail);
        $purchase_id = $existing_purchase_detail->id;
    }
    if ($cmid == 0) {
        $post_url = "$CFG->wwwroot/mod/rmc/add_rmc_content.php?add=rmc&type=$type&course=$course_id&section=$section&return=$returntomod&sr=$sr&prid=$purchase_id";
    } else {
        $post_url = "$CFG->wwwroot/mod/rmc/view_content.php?cmid=$cmid&return=$returntomod&sr=$sr&saveandredirect=1&purchaseid=$purchase_id";
    }
    echo "<div><form method='post' action='$post_url'>";
    echo "<input type='hidden' name='uuid' value='$node_id' />";
    echo "<input type='hidden' name='node_id' value='$node_id' />";
    echo "<input type='hidden' name='course' value='$course_id' />";
    echo "<input type='hidden' name='section' value='$section' />";
    echo "<input type='hidden' name='search_string' value='$search_text' />";
    echo "<input type='hidden' name='search_type' value='$search_type' />";
    echo "<input type='hidden' name='section' value='$section' />";
    echo "<input type='hidden' name='content_title' value='".$content_title."' />";
    echo "<input type='submit' id='link_to_course' name='link_to_course' value='Add to course' class='view-con-button' />";
    echo "</form></div>";
    
} elseif ($node_obj->properties["nvc:costValue"] == "no" || trim($node_obj->properties["nvc:costValue"]) == "") {
    //no actual purchase needed for this type, so if we don't have one, just add one
    $sql = "SELECT pd.* FROM {rmc_purchase_detail} pd
        WHERE user_id = $USER->id AND node_id = '$node_id'";
    $existing_purchase_detail = $DB->get_record_sql($sql);
    if(!$existing_purchase_detail){
        $rmc_purchase_detail = new stdClass();
        $rmc_purchase_detail->course_id = $course_id;
        $rmc_purchase_detail->user_id = $USER->id;
        $rmc_purchase_detail->node_id = $node_id;
        $rmc_purchase_detail->alfresco_share_url = $share_url;
        $purchase_id = $DB->insert_record('rmc_purchase_detail',$rmc_purchase_detail);
    }
    else{
        $rmc_purchase_detail = new stdClass();
        $rmc_purchase_detail->id = $existing_purchase_detail->id;
        $rmc_purchase_detail->course_id = $course_id;
        $rmc_purchase_detail->user_id = $USER->id;
        $rmc_purchase_detail->node_id = $node_id;
        $rmc_purchase_detail->alfresco_share_url = $share_url;
        $DB->update_record('rmc_purchase_detail', $rmc_purchase_detail);
        $purchase_id = $existing_purchase_detail->id;
    }
    if ($cmid == 0) {        
        $post_url = "$CFG->wwwroot/mod/rmc/add_rmc_content.php?add=rmc&type=$type&course=$course_id&section=$section&return=$returntomod&sr=$sr&prid=$purchase_id";
    } else {
        $post_url = "$CFG->wwwroot/mod/rmc/view_content.php?cmid=$cmid&return=$returntomod&sr=$sr&saveandredirect=1&purchaseid=$purchase_id";
    }
    if(isset($node_obj->properties['nvc:downloadTarget'])) {
    	echo "<div style='display:inline'><input type='submit' data-url='".$base_url.$node_obj->properties['nvc:downloadTarget']."' id='preview-button'  value='".get_string('preview', 'moodle')."' /></div>";
    } else {
	    echo "<div><form method='post' action='$post_url'>";
	    echo "<input type='hidden' name='uuid' value='$node_id' />";
	    echo "<input type='hidden' name='node_id' value='$node_id' />";
	    echo "<input type='hidden' name='course' value='$course_id' />";
	    echo "<input type='hidden' name='search_string' value='$search_text' />";
	    echo "<input type='hidden' name='search_type' value='$search_type' />";
	    echo "<input type='hidden' name='section' value='$section' />";
	    echo "<input type='hidden' name='content_title' value='".$content_title."' />";
	    echo "<input type='submit' id='link_to_course' name='link_to_course' value='Add to course' class='view-con-button' />";
	    echo "</form></div>";
   }
} else {
    echo "<div><input type='submit' class='view-con-button' id='buy' name='buy' value='Add to course' /></div>";

}
$cancelurl = "$CFG->wwwroot/mod/rmc/search.php";
echo "<div ><form method='post' action='$cancelurl'>";
echo "<input type='hidden' name='search_string' value='$search_text' />";
echo "<input type='hidden' name='search_type' value='$search_type' />";
echo "<input type='hidden' name='course' value='$course_id' />";
echo "<input type='hidden' name='section' value='$section' />";
echo "<input type='submit' id='link_to_course' name='link_to_course' class='view-con-button' value='".get_string('cancel', 'moodle')."' />";
echo "</form></div>";
$preview_url = $client->get_content_view_url_lite($node_obj);
if($preview_url != "#") {
	echo "<div style='display:inline'><input type='submit' class='view-con-button' data-url='". $preview_url."' id='preview-button'  value='".get_string('preview', 'moodle')."' /></div>";
}

echo "</div>";
echo "</div>";
//print pop up data
echo rmc_helper::print_buy_popup($node_obj, $course_id, $share_url);
echo '<div id="preloader" width="100%" height="100%"></div>';
echo '<script>var img_data = "'. rmc_helper::get_spinner_binary() .'";</script>';
} else {
	echo '<p style="border: 1px solid grey !important; width: 80% !important;font-family: sans-serif !important; text-align: center !important; padding : 11px !important;">'. get_string('invalid_rmc_error', 'rmc') .'</p></div>';
}

echo $OUTPUT->footer();






