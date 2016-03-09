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

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('block_zilink_settings', '', get_string('pluginname_desc', 'block_zilink')));


    $path = $CFG->dirroot . '/blocks/zilink/plugins';
    $directories = array();
    $ignore = array('.', '..', 'core');
    $dh = @opendir($path);

    while (false !== ($file = readdir($dh))) {
        if (!in_array($file, $ignore)) {
            if (is_dir("$path/$file")) {
                $directories[$file] = $file;
            }
        }
    }
    closedir($dh);

    $list = array();
    $list['1'] = 'Enabled';
    $list['0'] = 'Disabled';

    foreach ($directories as $directory) {
        if (file_exists($CFG->dirroot . '/blocks/zilink/plugins/' . $directory . '/settings.php')) {
            $settings->add(new admin_setting_configselect(  'zilink_plugin_' . $directory . '_enabled',
                                                            get_string('plugin_' . $directory . '_title', 'block_zilink'),
                                                            get_string('plugin_' . $directory . '_desc', 'block_zilink'),
                                                            '0',
                                                            $list));
            include($CFG->dirroot . '/blocks/zilink/plugins/' . $directory . '/settings.php');
        }
    }
}