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
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$path = dirname(dirname(__FILE__)).'/plugins';

$directories = array();
$ignore = array( '.', '..');
$dh = @opendir( $path );

while ( false !== ( $file = readdir( $dh ) ) ) {
    if ( !in_array( $file, $ignore ) ) {
        if (is_dir( "$path/$file" ) ) {
            $directories[$file] = $file;
        }
    }
}
closedir( $dh );

$capabilities = array();

foreach ($directories as $directory) {
    if (file_exists(dirname(dirname(__FILE__)).'/plugins/'.$directory.'/db/access.php')) {
        include(dirname(dirname(__FILE__)).'/plugins/'.$directory.'/db/access.php');
        if (isset(${$directory.'_capabilities'})) {
            $capabilities = array_merge($capabilities, ${$directory.'_capabilities'});
        }
    }
}