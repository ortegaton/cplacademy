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
 * Defines the class for the ZiLink block sub plugin timetable_week
 *
 * @package     block_zilink
 * @subpackage    timetable_week
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/*
==============================================
    Moodle Required Plugin Text
==============================================
*/

$string['pluginname']         = 'ZiLink - Super Block';
$string['pluginname_short'] = 'ZiLink';
$string['pluginname_desc']     = 'ZiLink automatically enrols users on courses based on the information held in your managment information system.';

/*
=============================================
    Moodle Permission Text
=============================================
*/
$string['zilink:addinstance']       = 'Add Block Instance';
$string['zilink:myaddinstance']       = 'Add Block to My Instance';

/*
==============================================
    General ZiLink Block Text
==============================================
*/

$string['zilink_block_type']            = 'Block Type';
$string['zilink_block_settings']        = '{$a} Block Settings';
$string['zilink_block_title']           = 'Block Title';
$string['zilink_block_hide_title']      = 'Hide Title';

/*
==============================================
    Global ZiLink Plugin Text
==============================================
*/

/*
==============================================
    Global ZiLink Plugin Error Text
==============================================
*/

$string['zilink_plugin_deprecated']                     = 'Plugin Deprecated. Please delete this block.';
$string['zilink_plugin_missing']                         = 'Plugin Missing From Disk. Please delete this block.';
$string['zilink_plugin_installation_prohibited']         = 'No Plugins can be installed with your permissions.';
$string['zilink_plugin_data_missing']                     = 'Data Not Currently Available';
$string['zilink_plugin_security_failed']                 = 'Permissions Check Failed';
$string['zilink_no_settings_required']                  = 'No settings require configuration';

/*
==============================================
    Load ZiLink Plugin Text
==============================================
*/

$path = dirname(dirname(dirname(__FILE__))).'/plugins';
$directories = array();
$ignore = array( '.', '..', 'core');
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