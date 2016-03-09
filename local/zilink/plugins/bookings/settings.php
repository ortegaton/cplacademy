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

$ADMIN->add('zilink_'.$directory.'_settings', new admin_category('zilink_'.$directory.'_rooms_settings', get_string($directory.'_settings_rooms', 'local_zilink')));
$ADMIN->add('zilink_bookings_rooms_settings', new admin_externalpage('zilink_'.$directory.'_rooms_settings_config', get_string($directory.'_config', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/rooms/admin/config.php",'moodle/site:config'));

//$ADMIN->add('zilink_bookings_rooms_settings', new admin_externalpage('zilink_'.$directory.'_settings_rooms_maintenance', get_string($directory.'_maintenance', 'local_zilink'), $CFG->httpswwwroot.'/local/zilink/plugins/'.$directory.'/rooms/admin/maintenance.php','local/zilink:bookings_rooms_maintenance_manage'));

//$ADMIN->add('zilink_'.$directory.'_settings', new admin_category('zilink_'.$directory.'_settings_resources', get_string($directory.'_settings_resources', 'local_zilink'))); 
//$ADMIN->add('zilink_bookings_settings_resources', new admin_externalpage('zilink_'.$directory.'_settings_resource_config', get_string($directory.'_config', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/resources/config.php",'moodle/site:config'));

//$ADMIN->add('zilink_room_booking_settings', new admin_externalpage('zilink_'.$directory.'_settings_maintenance', get_string($directory.'_maintenance', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/maintenance.php",'moodle/site:config'));
//$ADMIN->add('zilink_room_booking_settings', new admin_externalpage('zilink_'.$directory.'_settings_bookings', get_string($directory.'_bookings', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/".$directory."/admin/bookings.php",'moodle/site:config'));

/*
$path = $CFG->dirroot.'/local/zilink/plugins/bookings';
    $directories = array();
    $ignore = array( '.', '..','db','lang','admin');
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
        if(file_exists($CFG->dirroot.'/local/zilink/plugins/bookings/'.$directory.'/settings.php'))
        {
            $ADMIN->add('local_zilink', new admin_category('zilink_bookings_'.$directory.'_settings', get_string('bookings_'.$directory.'_settings', 'local_zilink')));
            
            include_once($CFG->dirroot.'/local/zilink/plugins/bookings/'.$directory.'/settings.php');
        }
        else {
            if(file_exists($CFG->dirroot.'/local/zilink/plugins/bookings/'.$directory.'/admin/index.php'))
            {
                $ADMIN->add('local_zilink', new admin_externalpage('zilink_bookings_'.$directory.'_settings', get_string('bookings_'.$directory.'_settings', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/bookings/".$directory."/admin/index.php",'moodle/site:config'));
            }
            if(file_exists($CFG->dirroot.'/local/zilink/plugins/bookings/'.$directory.'/admin/defaults.php'))
            {
                include_once($CFG->dirroot.'/local/zilink/plugins/bookings/'.$directory.'/admin/defaults.php');
            }
        }
    }
*/
