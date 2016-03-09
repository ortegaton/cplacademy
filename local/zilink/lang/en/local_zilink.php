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

$string['pluginname']         = 'ZiLink - Core';
$string['pluginname_short'] = 'ZiLink';
$string['pluginname_desc']     = 'ZiLink automatically enrols users on courses based on the information held in your managment information system.';
$string['zilink:addinstance']       = 'Add Block Instance';
$string['zilink_block_type']         = 'Block Type';
$string['zilink_block_settings']     = '{$a} Block Settings';
$string['zilink_block_title']         = 'Block Title';
$string['zilink']                         = 'ZiLink';
$string['staff']                        = 'Staff';
$string['config']                       = 'Configuration';
$string['manage']                       = 'Manage';
$string['general']                      = 'General';
$string['view']                         = 'View';
$string['information']                         = 'Information';
$string['requireddatamissing']          = 'Required data is not currently avaliable, Please try later or contact your Moodle Administrator';
$string['requiredpermissionmissing']    = 'This features is not currently available as you do not have the required permissions. Please contact your Moodle Administrator';
$string['done']                         = 'Done';
$string['back']                         = 'Back';
$string['return_to_course']             = 'Back to Course';
$string['above']                        = 'Above';
$string['below']                        = 'Below';
$string['level']                        = 'Level';
$string['enabled']                      = 'Enabled';
$string['disabled']                     = 'Disabled';
$string['attendance']                     = 'Attendance';
$string['present']                      = 'Present';
$string['late']                         = 'Late';
$string['authorised_absence']           = 'Authorised Absence';
$string['unauthorised_absence']         = 'Unauthorised Absence';
$string['assessment']                   = 'Assessment';
$string['assessment_overview']          = 'Assessment Overview';
$string['assessment_subjects']          = 'Assessment Subjects';
$string['recent']                     = 'Recent';
$string['overview']                     = 'Overview';
$string['subjects']                     = 'Subjects';
$string['information']                     = 'Information';

$string['present']                      = 'Present';
$string['late']                         = 'Late';
$string['absent']                       = 'Absent';
$string['authorisedabsence']            = 'Authorised Absence';
$string['unauthorisedabsence']          = 'Unauthorised Absence';
$string['schoolsclosed']                = 'School Closed';
$string['awaitpublication']             = 'Awaiting Publication';

$string['holiday']                      = 'Holiday';

$string['subject']                      = 'Subject';
$string['teacher']                      = 'Teacher';
$string['time']                         = 'Time';

$string['homework']                     = 'Homework';

$string['registration']                 = 'Registration';
$string['house']                        = 'House';
$string['year']                         = 'Year';

$string['filter']                         = 'Filter';
$string['unlink']                         = 'Unlink';
$string['unlinked']                       = 'Unlinked';
$string['linked']                       = 'Linked';

$string['create']                       = 'Create';
$string['created']                       = 'Created';
$string['relationship']                 = 'Relationship';
$string['priority']                     = 'Priority';
    
$string['plugins']                      = 'Plugins';
$string['plugin_missing']               = 'PLUGIN MISSING - Please Install';
$string['support_site']                 = 'Help Centre';
$string['zilink_plugin_deprecated']                     = 'Plugin Deprecated. Please delete this block.';
$string['zilink_plugin_missing']                         = 'Plugin Missing From Disk. Please delete this block.';
$string['zilink_plugin_installation_prohibited']         = 'No Plugins can be installed with your permissions.';
$string['zilink_plugin_data_missing']                     = 'Data Not Currently Available';
$string['zilink_plugin_security_failed']                 = 'Permissions Check Failed';

$string['zilink_plugin_missing_template']                = 'Course Template Missing';

$string['zilink_plugin_beta']                   = 'This Plugin is a BETA Release. ';
$string['zilink_plugin_support_desc']           = ' For more information please see our ';
$string['zilink_plugin_rc']                     = 'This Plugin is a CANDIDATE Release. ';

$string['report_writer_manage_reports'] = 'Manage Reports';

$string['cachedef_global'] = 'ZiLink - Global Data';
$string['cachedef_alluserdata'] = 'ZiLink - All User Data';

$path = dirname(dirname(dirname(__FILE__))).'/plugins';
$directories = array();
$ignore = array( '.',  '..', 'core');
$dh = @opendir( $path );

while ( false !== ( $file = readdir( $dh ) ) ) {
    if ( !in_array( $file, $ignore ) ) {
        if (is_dir( "$path/$file" ) ) {
            $directories[$file] = $file;
        }
    }
}
closedir( $dh );

foreach ($directories as $directory) {
    if (file_exists(dirname(dirname(dirname(__FILE__))).'/plugins/'.$directory.'/lang/en.php')) {
        include(dirname(dirname(dirname(__FILE__))).'/plugins/'.$directory.'/lang/en.php');
    }
}