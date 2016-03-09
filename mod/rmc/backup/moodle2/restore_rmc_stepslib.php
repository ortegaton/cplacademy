<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.



/**
 * Structure step to restore one rmc activity
 */
class restore_rmc_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('rmc', '/activity/rmc');
        

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_rmc($data) {
        global $DB, $CFG, $USER;
        if(!class_exists('rmc_helper', FALSE)) {
        	require $CFG->dirroot . '/mod/rmc/locallib.php';
        }
        $is_valid = rmc_helper::validate_rmc_installation();
        if($is_valid) {
	        $data = (object)$data;
	        $oldid = $data->id;
	        $data->course = $this->get_courseid();
	        if(isset($data->timemodified)) {
	        	$data->timemodified = $this->apply_date_offset($data->timemodified);
	        }
			$purchase_obj = $DB->get_record('rmc_purchase_detail', array('id' => $data->purchase_id));
			$record = new stdClass ();
			$record->course = $data->course;
			$record->user_id = $USER->id;
			$record->node_id = $data->node_id;
			$record->alfresco_share_url = rmc_helper::get_auth_url ( $data->node_id,  $data->course);
			$data->purchase_id = $DB->insert_record ( 'rmc_purchase_detail', $record );
	        // insert the rmc record
	        $newitemid = $DB->insert_record('rmc', $data);
	        $is_free = rmc_helper::check_free_content($data->node_id);
	        if(!$is_free) {
	        	rmc_helper::add_purcharse_entry($data->course, $data->node_id, rmc_helper::get_course_enrol_users($data->course), $USER->email);
	        }
	        // immediately after inserting "activity" record, call this
	        $this->apply_activity_instance($newitemid);
		}
    }

    protected function after_execute() {
        // Add choice related files, no need to match by itemname (just internally handled context)
//        $this->add_related_files('mod_rmc', 'intro', null);
//        $this->add_related_files('mod_rmc', 'content', null);
    }
}
