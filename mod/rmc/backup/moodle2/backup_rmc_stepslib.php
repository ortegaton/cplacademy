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


defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete rmc structure for backup, with file and id annotations
 */
class backup_rmc_activity_structure_step extends backup_activity_structure_step {

   protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $rmc = new backup_nested_element('rmc', array('id'), array(
            'name','course','purchase_id', 'node_id'));
        
        // Build the tree
        // (none)
        

        // Define sources
        $rmc->set_source_sql('SELECT R.id AS id, R.name AS name, R.course AS course, R.purchase_id AS purchase_id, P.node_id AS node_id FROM mdl_rmc AS R INNER JOIN mdl_rmc_purchase_detail AS P ON R.purchase_id = P.id WHERE R.id = ?', array(backup::VAR_ACTIVITYID));
        

        // Define id annotations
        // (none)

        // Define file annotations
//        $rmc->annotate_files('mod_rmc', 'intro', null); // This file areas haven't itemid
//        $rmc->annotate_files('mod_rmc', 'content', null); // This file areas haven't itemid

        // Return the root element (rmc), wrapped into standard activity structure
        return $this->prepare_activity_structure($rmc);
    }
}
