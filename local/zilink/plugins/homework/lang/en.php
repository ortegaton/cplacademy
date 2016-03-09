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
$string['homework']                                 = 'Homework';
$string['homework_report']                            = 'Homework Report';


/*
==============================================
    ZiLink Settings Text
==============================================
*/


$string['homework_settings'] = $string['homework'];

$string['homework_account_settings'] = 'Accounts';
$string['homework_meeting_settings'] = 'Meetings';

$string['homework_report_support_desc'] = 'For more information about configuring the ZiLink Guardian View please see our ';


$string['homework_report_interface'] = 'Interface';

$string['homework_accounts_settings']                       = $string['homework']. ' Accounts';


/*
==============================================
    ZiLink Panels Text
==============================================
*/

$string['homework_report_subject_reports']              = 'Subject Reports';
$string['homework_report_no_reports']                   = 'No homework has been set';
$string['homework_report_report']                 = 'Report';
$string['homework_report_homework_set']                 = 'Homework Set';

$string['homework_report_recent'] = 'Recent';

$path = $CFG->dirroot.'/local/zilink/plugins/homework/';

$directories = array();
$ignore = array( '.', '..','core','admin','db','lang');
$dh = @opendir( $path );

while( false !== ( $file = readdir( $dh ) ) )
{
        if( !in_array( $file, $ignore ) )
        {
            if(is_dir( "$path/$file" ) )
            {
                $directories[$file] = $file;
            }
    }
}
closedir( $dh );

foreach($directories as $directory)
{
    if(file_exists($CFG->dirroot.'/local/zilink/plugins/homework/'.$directory.'/lang/en.php'))
    {

        include($CFG->dirroot.'/local/zilink/plugins/homework/'.$directory.'/lang/en.php');
    }
}