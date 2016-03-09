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

$string['zimbra'] = 'Zimbra';
$string['zimbra_settings'] = 'Zimbra';
$string['plugin_zimbra_desc'] = 'If your school use Zimbra as their e-mail system you can use this page to configure access to Zimbra so that your Moodle Users can easily link to their Zimbra e-mail account.';

/* 
=============================================
    Moodle Permission Text
=============================================
*/
$string['zilink:zimbra_addinstance'] = 'Zimbra - Add Instance';
$string['zilink:zimbra_view'] = 'Zimbra - View';


/*
==============================================
    ZiLink Block Text
==============================================
*/

$string['zimbra_linktext'] = 'Link Text';
$string['zimbra_clientlinktext'] = 'The text to be displayed as the html link to zimbra';
$string['zimbra_preauthkey'] = 'Zimbra PreAuth Key';
$string['zimbra_clientpreauthkey'] = 'Please enter Zimbra PreAuth Key';
$string['zimbra_title'] = 'Block Title';
$string['zimbra_url'] = 'Zimbra URL';
$string['zimbra_clienturl'] = 'Enter Zimbras PreAuth URL (e.g https://zimbra.server.com/service/preauth)';
$string['zimbra_clienttitle'] = 'Text to be displayed as the block title';

$string['zimbra_support_desc'] = 'More information about ZiLink Zimbra integration its avaliable on our ';