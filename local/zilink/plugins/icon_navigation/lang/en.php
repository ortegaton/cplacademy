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

/*
==============================================
    Moodle Required Plugin Text
==============================================
*/

$string['icon_navigation'] = 'Icon Navigation';
$string['icon_navigation_settings'] = 'Icon Navigation';
/* 
=============================================
    Moodle Permission Text
=============================================
*/
$string['zilink:icon_navigation_addinstance'] = 'Icon Navigation - Add Instance';

/*
==============================================
    ZiLink Block Text
==============================================
*/
$string['icon_navigation_page_title'] = $string['zilink']  .' '.$string['icon_navigation'] ;
$string['icon_navigation_title'] = 'Icon Navigation';
$string['icon_navigation_desc'] = 'Use this page to configure the Icon Navigation block within ZiLink for Moodle.';

$string['plugin_icon_navigation_title'] = $string['icon_navigation_title'];
$string['plugin_icon_navigation_desc'] = $string['icon_navigation_desc'];

$string['icon_navigation_size'] = 'Icon Size';
$string['icon_navigation_size_desc'] = '';

$string['icon_navigation_size_small'] = 'Small (50px)';
$string['icon_navigation_size_large'] = 'Large (70px)';
$string['icon_navigation_size_xlarge'] = ' XLarge (100px)';

$string['icon_navigation_defaultimages'] = 'Default Images';
$string['icon_navigation_customimages'] = 'Custom Images';

$string['icon_navigation_iconset'] = 'Icon Set';
$string['icon_navigation_iconset_desc'] = 'Select the icon set you wish to use.';

$string['icon_navigation_support_desc'] = 'More information about ZiLink Icon Navigation its avaliable on our ';
