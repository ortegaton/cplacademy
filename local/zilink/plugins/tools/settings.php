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

if((!defined('ZILINK_DISABLE_TOOLS_COLLATIONS') || ZILINK_DISABLE_TOOLS_COLLATIONS == false ) && ($CFG->dbtype == 'mysql' || $CFG->dbtype == 'mysqli')) {
    $ADMIN->add('zilink_'.$directory.'_settings', new admin_category('zilink_'.$directory.'_database_settings', get_string('tools_database','local_zilink')));
    $ADMIN->add('zilink_tools_database_settings', new admin_externalpage('zilink_tools_database_collations', get_string($directory.'_database_collations', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/collations.php",'moodle/site:config'));
}

if(is_enabled_auth('ldap'))
{
    $ADMIN->add('zilink_'.$directory.'_settings', new admin_category('zilink_'.$directory.'_ldap_settings', get_string('tools_ldap','local_zilink')));
    $ADMIN->add('zilink_tools_ldap_settings', new admin_externalpage('zilink_tools_ldap_sync', get_string($directory.'_ldap_sync', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/ldap_sync.php",'moodle/site:config'));
}


$ADMIN->add('zilink_tools_settings', new admin_category('zilink_tools_settings_permissions', get_string('tools_permissions','local_zilink')));
$ADMIN->add('zilink_tools_settings_permissions', new admin_externalpage('zilink_tools_settings_permissions_override', get_string('tools_permissions_override', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/permissions.php",'moodle/site:config'));

$ADMIN->add('zilink_tools_settings', new admin_externalpage('zilink_tools_current_date_settings', get_string('tools_current_date', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/current_date.php",'moodle/site:config'));

if($CFG->debug == DEBUG_DEVELOPER) 
{
    $ADMIN->add('zilink_tools_settings', new admin_externalpage('zilink_tools_effective_date_settings', get_string('tools_effective_date', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/effective_date.php",'moodle/site:config'));
    
}