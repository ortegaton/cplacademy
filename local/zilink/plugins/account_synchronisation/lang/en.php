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
 * Defines the capabilities for the ZiLink local
 *
 * @package     local_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
$string['account_synchronisation'] = 'Account Synchronisation';
$string['account_synchronisation_page_title'] = $string['zilink'] .' '.'Account Synchronisation';
$string['account_synchronisation_settings'] = $string['account_synchronisation'];
$string['account_synchronisation_cron'] = $string['account_synchronisation'] . ' Cron';
$string['account_synchronisation_export'] = 'Export';

$string['account_synchronisation_config'] = 'Configuration';
$string['account_synchronisation_config_desc'] = 'ZiLink for Moodle will automatically synchronise and make information from your school management information system available for matching with user information in Moodle. Usually, you will select ‘Enable’ and click ‘Save changes’.';

$string['account_synchronisation_matched'] = 'Matched';
$string['account_synchronisation_matched_desc'] = 'A list of students or staff in Moodle that have been successfully matched with their information from your school management information system.';

$string['account_synchronisation_unmatched'] = 'Unmatched';
$string['account_synchronisation_unmatched_desc'] = 'A list of students or staff in Moodle that have not yet been matched with their information from your school management information system.
<br><br> 
The information in the first four columns is from your school management information system. In the final ‘Account Matched Against’ column will be one of three options.
<ul>
<li>Matched against xxxxxxx – where you see a username matched against information from your school management information system, ZiLink for Moodle has successfully created a link between the Moodle User Profile and a record in your school management information system.</li>
<li>No matches available – there is no Moodle User Profile/Account to match against the information from the school management information system. You will need to create a Moodle User Profile/Account so that a match con be created.</li>
<li>A drop-down list of potential matches – select the correct Moodle User Profile/Account from the drop-down that goes with the information from the school management information system.</li>
<ul>
';

$string['account_synchronisation_exclude_usernames'] = 'Excluded Usernames';
$string['account_synchronisation_exclude_usernames_help'] = 'Enter either part or all of the username(s) you wish to exclude from account matching. Use a comma to seperate the usernames eg test,ocr_';