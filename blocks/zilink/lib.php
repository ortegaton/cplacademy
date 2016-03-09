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

function block_zilink_pluginfile($course, $block, $context, $filearea, $args, $forcedownload) {
    global $CFG;

    require_once($CFG->dirroot . '/lib/filelib.php');
    $fs = get_file_storage();

    $filename = urldecode(array_pop($args));
    $itemid = array_pop($args);

    if (!$file = $fs->get_file($context->id, 'block_zilink', 'content', $itemid, '/', $filename) or $file->is_directory()) {
        send_file_not_found();
    }

    $lifetime = isset($CFG->filelifetime) ? $CFG->filelifetime : 86400;

    send_stored_file($file, $lifetime, 0);
}
