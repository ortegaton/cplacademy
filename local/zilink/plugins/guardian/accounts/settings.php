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

//$ADMIN->prune('zilink_guardian_accounts_settings');
//$ADMIN->add('zilink_guardian_settings', new admin_externalpage('zilink_guardian_accounts_settings', get_string('zilink_guardian_accounts_settings', 'local_zilink'), $CFG->httpswwwroot."/admin/auth_config.php?auth=zilink_guardian",'moodle/site:config'));
  
$ADMIN->add('zilink_guardian_accounts_settings', new admin_externalpage('zilink_guardian_accounts_config_settings', get_string('zilink_guardian_accounts_config_settings', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/guardian/accounts/admin/config.php",'moodle/site:config'));
$ADMIN->add('zilink_guardian_accounts_settings', new admin_externalpage('zilink_guardian_accounts_create_settings', get_string('zilink_guardian_accounts_create_settings', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/guardian/accounts/admin/create_filter.php",'moodle/site:config'));
$ADMIN->add('zilink_guardian_accounts_settings', new admin_externalpage('zilink_guardian_accounts_manage_settings', get_string('zilink_guardian_accounts_manage_settings', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/guardian/accounts/admin/manage_filter.php",'moodle/site:config'));
$ADMIN->add('zilink_guardian_accounts_settings', new admin_externalpage('zilink_guardian_accounts_export_settings', get_string('zilink_guardian_accounts_export_settings', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/guardian/accounts/admin/export.php",'moodle/site:config'));