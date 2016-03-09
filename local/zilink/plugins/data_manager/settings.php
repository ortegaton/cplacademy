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
 * Defines the settings for the ZiLink block
 *
 * @package     local_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$ADMIN->add('zilink_data_manager_settings', new admin_externalpage('zilink_data_manager_sessions_settings', get_string($directory.'_sessions', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/sessions.php?sesskey=".sesskey(),'moodle/site:config'));
$ADMIN->add('zilink_data_manager_settings', new admin_externalpage('zilink_data_manager_components_settings', get_string($directory.'_components', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/components.php?sesskey=".sesskey(),'moodle/site:config'));
$ADMIN->add('zilink_data_manager_settings', new admin_externalpage('zilink_data_manager_component_groups_settings', get_string($directory.'_component_groups', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/component_groups.php?sesskey=".sesskey(),'moodle/site:config'));
//$ADMIN->add('zilink_data_manager_settings', new admin_externalpage('zilink_'.$directory.'_settings_resultsets', get_string($directory.'_resultsets', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/resultsets.php",'moodle/site:config'));
//$ADMIN->add('zilink_data_manager_settings', new admin_externalpage('zilink_'.$directory.'_settings_gradesets', get_string($directory.'_gradesets', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/gradesets.php",'moodle/site:config'));
//$ADMIN->add('zilink_data_manager_settings', new admin_externalpage('zilink_'.$directory.'_settings_results', get_string($directory.'_results', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/aspects.php",'moodle/site:config'));