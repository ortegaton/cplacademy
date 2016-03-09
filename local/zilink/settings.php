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

if ($hassiteconfig) {

    $ADMIN->add('root', new admin_category('local_zilink', get_string('zilink', 'local_zilink')));

    require_once($CFG->dirroot . '/local/zilink/lib.php');
    ZiLinkLoadDefaults();

    $path = $CFG->dirroot . '/local/zilink/plugins';
    $directories = array();
    $ignore = array('.', '..', 'core', 'report_writer');
    $dh = @opendir($path);

    while (false !== ($file = readdir($dh))) {
        if (!in_array($file, $ignore)) {
            if (is_dir("$path/$file")) {
                $directories[$file] = $file;
            }
        }
    }
    ksort($directories);
    closedir($dh);

    foreach ($directories as $directory) {
        if (file_exists($CFG->dirroot . '/local/zilink/plugins/' . $directory . '/admin/index.php')) {
            $ADMIN->add('local_zilink',
                        new admin_externalpage('zilink_' . $directory . '_settings',
                        get_string($directory . '_settings', 'local_zilink'),
                        $CFG->httpswwwroot . "/local/zilink/plugins/" . $directory . "/admin/index.php", 'moodle/site:config'));
        } else {
            if (file_exists($CFG->dirroot . '/local/zilink/plugins/' . $directory . '/settings.php')) {
                $ADMIN->add('local_zilink', new admin_category('zilink_' . $directory . '_settings',
                                                                get_string($directory . '_settings', 'local_zilink')));
                include_once($CFG->dirroot . '/local/zilink/plugins/' . $directory . '/settings.php');
            }
        }
    }
}