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

function local_zilink_cron() {
    global $CFG;

    $deprecatedplugins = array();

    $schedule = array('local_zilink_account_synchronisation_cron' => 60, );

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

    $lastcron = get_config(null, 'zilink_cron_last_completed');

    foreach ($directories as $directory) {
        if (file_exists($CFG->dirroot . '/local/zilink/plugins/' . $directory . '/lib.php')) {
            require_once($CFG->dirroot . '/local/zilink/plugins/' . $directory . '/lib.php');
            if (!empty($lastcron)) {
                $interval = (time() - $lastcron) / 60;
                if (isset($schedule['local_zilink_' . $directory . '_cron'])) {
                    if ($interval < $schedule['local_zilink_' . $directory . '_cron']) {
                        if ((defined('CLI_SCRIPT') && CLI_SCRIPT) && $CFG->debug == DEBUG_DEVELOPER) {
                            mtrace('ZiLink Cron Job local_zilink_' . $directory . '_cron last ran ' . $interval . ' minutes ago');
                            continue;
                        }
                    }
                }
            }
            if (function_exists('local_zilink_' . $directory . '_cron')) {
                call_user_func('local_zilink_' . $directory . '_cron');
            }
        }
    }
    set_config('zilink_cron_last_completed', time());

    return true;
}

function ZiLinkLoadDefaults() {
    global $CFG, $DB;

    $path = $CFG->dirroot . '/local/zilink/plugins';
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
    ksort($directories);
    closedir($dh);

    foreach ($directories as $directory) {
        if (file_exists($CFG->dirroot . '/local/zilink/plugins/' . $directory . '/admin/defaults.php')) {
            include($CFG->dirroot . '/local/zilink/plugins/' . $directory . '/admin/defaults.php');
        }
    }
}

function ZiLinkPluginMaturity($level) {
    switch($level) {
        case MATURITY_STABLE :
            return 'Stable';
        case MATURITY_RC :
            return '<p class="error">Release Candidate</p>';
        default :
            return '<p class="error">Beta</p>';
    }
}

function zilinkdeserialise($value) {

    $json = json_decode($value,true);

    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return $json;
        break;
        default:
            echo 'error';
            return unserialize($value);
        break;
    }
    return unserialize($value);
}
