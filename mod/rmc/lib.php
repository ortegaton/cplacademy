<?php

defined ( 'MOODLE_INTERNAL' ) || die ();
require_once ($CFG->dirroot . '/mod/resource/lib.php');
require_once ($CFG->dirroot . '/lib/resourcelib.php');
function rmc_supports($feature) {
	switch ($feature) {
		case FEATURE_MOD_ARCHETYPE :
			return MOD_ARCHETYPE_RESOURCE;
		case FEATURE_GROUPS :
			return false;
		case FEATURE_GROUPINGS :
			return false;
		case FEATURE_GROUPMEMBERSONLY :
			return true;
		case FEATURE_MOD_INTRO :
			return true;
		case FEATURE_COMPLETION_TRACKS_VIEWS :
			return true;
		case FEATURE_GRADE_HAS_GRADE :
			return false;
		case FEATURE_GRADE_OUTCOMES :
			return false;
		case FEATURE_BACKUP_MOODLE2 :
			return true;
		case FEATURE_SHOW_DESCRIPTION :
			return true;
		
		default :
			return null;
	}
}

/**
 * Returns all other caps used in module
 * 
 * @return array
 */
function rmc_get_extra_capabilities() {
	return array ();
}

/**
 * List of view style log actions
 * 
 * @return array
 */
function rmc_get_view_actions() {
	return array ('view', 'view all' );
}

function rmc_add_instance($rmc) {
	global $DB, $USER;
	$rmc->displayoptions = rmc_set_display_options ( $rmc );
	if ($rmc->purchase_id == 0) {
		$table = 'rmc_purchase_detail';
		$record = new stdClass ();
		$record->course_id = $rmc->course;
		$record->user_id = $USER->id;
		$record->node_id = $rmc->uuid;
		$record->alfresco_share_url = rmc_helper::get_auth_url ( $rmc->uuid,  $rmc->course);
		$rmc->purchase_id = $DB->insert_record ( $table, $record );
	}
	return $DB->insert_record ( 'rmc', $rmc );
}
function rmc_update_instance($rmc, $mform) {
	global $DB;
	$data = new stdClass ();
	$data->id = $rmc->instance;
	$data->name = $rmc->name;
	$data->displayoptions = rmc_set_display_options ( $rmc );
	$data->display = $rmc->display;
	return $DB->update_record ( 'rmc', $data );
}
function rmc_delete_instance($id) {
	global $DB;
	if (! $rmc_obj = $DB->get_record ( 'rmc', array ('id' => $id ) )) {
		return false;
	}
	$DB->delete_records ( 'rmc', array ('id' => $rmc_obj->id ) );
	
	return true;
}
function rmc_get_purchase_detail_from_rmc($rmc_id) {
	global $DB;
	$q = "SELECT pd.* FROM mdl_rmc_purchase_detail pd
        INNER JOIN mdl_rmc r on pd.id = r.purchase_id WHERE r.id = '$rmc_id'";
	$result = $DB->get_record_sql ( $q );
	return $result;
}
function rmc_get_purchase_detail($purchase_id) {
	global $DB;
	$q = "SELECT pd.* FROM mdl_rmc_purchase_detail pd
        WHERE id = $purchase_id";
	$result = $DB->get_record_sql ( $q );
	return $result;
}
/**
 *
 * @global type $DB
 * @param $startend type       	
 * @param $logid type       	
 * @param $error type       	
 * @param $filename type       	
 * @param $rows_to_process type       	
 * @param $rows_processed type       	
 * @param $operation type       	
 * @param $source type       	
 * @param $added type       	
 * @param $updated type       	
 * @param $untouched type       	
 * @return type
 */
function rmc_write_to_log($startend, $logid, $filename = '', $operation = '', $parameters = array(), $notes = '') {
	global $CFG, $DB;
//	if ($CFG->mod_rmc_module_logging == 1) {
		if ($startend == 'start') {
			$log_entry = new stdClass ();
			$log_entry->start_time = date ( 'Y-m-d H:i:s' );
			$log_entry->filename = $filename;
			$log_entry->operation = $operation;
			$log_entry->parameters = print_r ( $parameters, true );
			$log_entry->notes = $notes;
			// $logid = $DB->insert_record('rmc_logs', $log_entry);
		} else { // we do the same thing for both mid and end...
			$q = "	update {rmc_logs}
                            set     end_time = now(), 
                                    execution_secs = TIMESTAMPDIFF(SECOND, start_time, now())				
                            where id = $logid";
			// $DB->execute($q);
		}
		return $logid;
//	} else {
//		return 0;
//	}
}
function rmc_set_display_options($data) {
	$displayoptions = array ();
	if(isset($data->display)) {
		if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
			$displayoptions ['popupwidth'] = $data->popupwidth;
			$displayoptions ['popupheight'] = $data->popupheight;
		}
		if (in_array ( $data->display, array (RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME ) )) {
			$displayoptions ['printheading'] = ( int ) ! empty ( $data->printheading );
			$displayoptions ['printintro'] = ( int ) ! empty ( $data->printintro );
		}
	}
	if (! empty ( $data->showsize )) {
		$displayoptions ['showsize'] = 1;
	}
	if (! empty ( $data->showtype )) {
		$displayoptions ['showtype'] = 1;
	}
	return serialize ( $displayoptions );
}

function rmc_get_coursemodule_info($coursemodule) {
	global $CFG, $DB;
	require_once("$CFG->libdir/filelib.php");
	require_once("$CFG->libdir/resourcelib.php");
	require_once("$CFG->dirroot/mod/rmc/locallib.php");

	$context = context_module::instance($coursemodule->id);

	if (!$rmc = $DB->get_record('rmc', array('id'=>$coursemodule->instance),
			'id, name, display, displayoptions, intro, introformat')) {
			return NULL;
	}

	$info = new cached_cm_info();
	$info->name = $rmc->name;
	if ($coursemodule->showdescription) {
		// Convert intro to html. Do not filter cached version, filters run at display time.
		$info->content = format_module_intro('rmc', $rmc, $coursemodule->id, false);
	}

	if ($rmc->display == RESOURCELIB_DISPLAY_POPUP) {
		$fullurl = "$CFG->wwwroot/mod/rmc/view.php?id=$coursemodule->id&amp;redirect=1";
		$options = empty($rmc->displayoptions) ? array() : unserialize($rmc->displayoptions);
		$width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
		$height = empty($options['popupheight']) ? 450 : $options['popupheight'];
		$wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
		$info->onclick = "window.open('$fullurl', '', '$wh'); return false;";

	}
	return $info;
}

