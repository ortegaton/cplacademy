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

/*
==============================================
    Moodle Required Plugin Text
==============================================
*/

/* 
=============================================
    Moodle Permission Text
=============================================
*/

/*
==============================================
    ZiLink Block Text
==============================================
*/

$string['tools'] = 'Tools';
$string['tools_page_title'] = 'ZiLink Tools';
$string['tools_title'] = 'ZiLink Tools';
$string['tools_settings'] = $string['tools'];
$string['tools_database_collations'] = 'Collations';

$string['tools_database_title'] = 'ZiLink Database Tool';
$string['tools_database_collations_title'] = 'ZiLink Database Tools - Collations';

$string['tools_ldap'] = 'LDAP';
$string['tools_ldap_sync_title'] = 'ZiLink LDAP Sync Tool';
$string['tools_ldap_sync'] = 'Sync';
$string['tools_ldap_sync_non_contexts'] = 'No contexts found';

$string['tools_ldap_sync_desc'] =   'The ZiLink LDAP Sync Tool allows the LDAP enrolment Sync Users script to be started from within Moodle instead of the usual method of from the command line.<br>';
$string['tools_ldap_sync_ous'] =   'Users  with be imported from the OUs listed below.<br><br>';
$string['tools_ldap_sync_import'] = 'Run LDAP Sync';

$string['tools_database'] = 'Database';
$string['tools_database_charset'] = 'Charset';
$string['tools_database_collation'] = 'Collations';
$string['tools_database_collations_desc'] = 'This tool will update all database tables and fields to be the same charset and collation as defined below.<br/><br/>
                                            <strong>Please ensure you have a taken a backup of your database before proceeding</strong>';

$string['tools_database_exceute'] = 'Database Update';

$string['tools_permissions_override_title'] = 'ZiLink Permissions Tools - Override';
$string['tools_permissions'] = 'Permissions';
$string['tools_permissions_override'] = 'Override';

$string['tools_permissions_overide_title_desc'] ='By default an Administrator viewing a student\'s timetable will only see links for those courses that have been made available to students. If you wish to override this behaviour select <b>Enable</b> and click <b>Save changes</b>. With this feature enabled an Administrator will see links and be able to access all Moodle courses in a student\'s timetable.';

$string['tools_permissions_override_desc'] = 'View Hidden Courses & Categories';
$string['tools_permissions_override_desc_help'] = 'Enables an administrator to see timetable links for hidden courses when logged in a student.';

$string['tools_support_desc'] = 'More information about ZiLink Tool its avaliable on our ';

$string['tools_effective_date'] = 'Effective Date';

$string['tools_effective_date_title'] = 'ZiLink Effective Date';
$string['tools_effective_date_title_desc'] = 'This tool will enable ZiLink to display information in relation to a set date.';

$string['tools_current_date'] = 'Current Date';
$string['tools_current_date_gmt'] = 'Current Date (GMT/BST)';
$string['tools_current_date_utc'] = 'Current Date (UTC)';
$string['tools_current_date_title'] = 'ZiLink Current Date';
$string['tools_current_date_title_desc'] = 'This tool will display the current server date & time as ZiLink understands it.';

