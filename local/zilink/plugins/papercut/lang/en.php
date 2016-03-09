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

$string['papercut'] = 'PaperCut';
$string['papercut_desc'] = 'Use this page to configure PaperCut for the PaperCut block within ZiLink for Moodle.';
$string['papercut_settings'] = 'PaperCut';

$string['papercut_url'] = 'PaperCut Server URL';
$string['papercut_url_help'] = 'Please enter the URL for the PaperCut Server. <br> eg. http://printers.school.lan:9191';
$string['papercut_url_error'] = 'Please enter thes PaperCut Server FQDN URL or IP address. NETBIOS name not allowed';

$string['papercut_external'] = 'Available Externally';
$string['papercut_external_help'] = 'Is the PaperCut server accessible externally?';

$string['papercut_widget'] = 'Widget';
$string['papercut_widget_help'] = 'Select which type of PaperCut Widget you wish to display';

$string['papercut_support_desc'] = 'More information about ZiLink PaperCut integration its avaliable on our ';
?>