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
 * Defines the capabilities for the ZiLink block
 *
 * @package     local_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    defined('MOODLE_INTERNAL') || die();

$ADMIN->add('zilink_categories_settings', new admin_externalpage('zilink_'.$directory.'_settings_create', get_string($directory.'_create', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/create.php",'moodle/site:config'));
//$ADMIN->add('zilink_categories_settings', new admin_externalpage('zilink_'.$directory.'_settings_view', get_string($directory.'_view', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/view.php",'moodle/site:config'));
//$ADMIN->add('zilink_categories_settings', new admin_externalpage('zilink_'.$directory.'_settings_delete', get_string($directory.'_delete', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/delete.php",'moodle/site:config'));
