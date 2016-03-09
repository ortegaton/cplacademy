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
$string['student']                                 = 'Student';
$string['student_settings'] = $string['student'];
$string['student_view']                            = 'Student View';
$string['student_appointment']                     = 'Student Appointment';

$string['zilink_student_view_settings']            = 'View';
$string['zilink_student_appointment_settings']     = 'Appointment';

$string['student_view_support_desc']            = 'For more information about configuring the ZiLink student View please see our ';
$string['student_appointment_support_desc']     = 'For more information about configuring the ZiLink Student Appointment please see our ';




$path = $CFG->dirroot.'/local/zilink/plugins/student/';

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
    if(file_exists($CFG->dirroot.'/local/zilink/plugins/student/'.$directory.'/lang/en.php'))
    {

        include($CFG->dirroot.'/local/zilink/plugins/student/'.$directory.'/lang/en.php');
    }
}