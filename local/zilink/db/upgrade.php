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
 * @package     block_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_local_zilink_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    $path = $CFG->dirroot . '/local/zilink/plugins';

    $directories = array();
    $ignore = array('.', '..');
    $dh = @opendir($path);

    while (false !== ($file = readdir($dh))) {
        if (!in_array($file, $ignore)) {
            if (is_dir("$path/$file")) {
                $directories[$file] = $file;
            }
        }
    }
    closedir($dh);

    ksort($directories);

    foreach ($directories as $directory) {
        echo '<div class="notifysuccess">Checking ZiLink Core Plugin (' . $directory . ')';
        if (file_exists($path . '/' . $directory . '/db/upgrade.php')) {
            include_once($path . '/' . $directory . '/db/upgrade.php');
        } else {
            echo '<br>Plugin does not required any database updates';
        }
        echo '</div>';
    }
    return true;
}
