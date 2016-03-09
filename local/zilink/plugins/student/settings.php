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
 
$path = $CFG->dirroot.'/local/zilink/plugins/student/';
$sub_directories = array();
$ignore = array( '.', '..','db','admin','lang');
$dh = @opendir( $path );

while( false !== ( $file = readdir( $dh ) ) )
{
    if( !in_array( $file, $ignore ) )
    {
        if(is_dir( "$path/$file" ) )
        {
            $sub_directories[$file] = $file;
        }
    }
}
ksort($sub_directories);
closedir( $dh );

foreach($sub_directories as $sub_directory)
{
    if(file_exists($CFG->dirroot.'/local/zilink/plugins/student/'.$sub_directory.'/settings.php'))
    {
        $ADMIN->add('zilink_student_settings', new admin_category('zilink_student_'.$sub_directory.'_settings', get_string('zilink_student_'.$sub_directory.'_settings', 'local_zilink')));
        
        include($CFG->dirroot.'/local/zilink/plugins/student/'.$sub_directory.'/settings.php');
        if(file_exists($CFG->dirroot.'/local/zilink/plugins/student/'.$sub_directory.'/admin/defaults.php'))
        {
            include($CFG->dirroot.'/local/zilink/plugins/student/'.$sub_directory.'/admin/defaults.php');
        }

    }
}


 
