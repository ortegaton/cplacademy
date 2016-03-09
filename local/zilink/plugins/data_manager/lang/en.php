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
 * Defines the capabilities for the ZiLink local
 *
 * @package     local_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
==============================================
    Moodle Required Plugin Text
==============================================
*/

/* 
=============================================
    Moodle Permission Text
=============================================
*/

/*
==============================================
    ZiLink Block Text
==============================================
*/

$string['data_manager'] = 'Data Manager';
$string['data_manager_page_title'] =  $string['zilink']. ' ' .$string['data_manager'];
$string['data_manager_settings'] = $string['data_manager'];

$string['data_manager_components_title'] = $string['zilink']. ' '.$string['data_manager'].': Components';

$string['data_manager_components'] = 'Components';
$string['data_manager_components_allow'] = 'Components Allowed';

$string['data_manager_sessions'] = 'Sessions';
$string['data_manager_sessions_title_desc'] = 'To display assessment data in Guardian View or Class View or to work with data in Report Writer you will need to identify the assessment sessions you want to include. '.
                                              'NB. SIMS.Net calls an Assessment Session a Result Set.';
$string['data_manager_sessions_page_title'] = $string['zilink']. ' '.$string['data_manager'].': Assessment Sessions';
$string['data_manager_sessions_title'] = 'Assessment Sessions';
$string['data_manager_sessions_allowed'] = 'Sessions Allowed';
$string['data_manager_sessions_allowed_help'] = 'To display assessment data you need to select the Assessment Sessions you want to include. NB. SIMS.Net calls an Assessment Session, a Result Set. <br>
The available Assessment Sessions are listed below. You can re-order sessions by selecting a session and clicking the ‘Up’ or ‘Down’ buttons. To include an assessment session select the session you want and click ‘Save changes’. You can multi-select sessions using ‘Ctrl + left mouse button’';
$string['data_manager_sessions_order'] = 'Sessions Order';
$string['data_manager_sessions_order_help'] = 'TODO';

$string['data_manager_components_title_desc'] = 'To display assessment data in Guardian View or Class View or to work with data in Report Writer you will need to identify the components you wish to use you want to include. '.
                                              'N.B SIMS.Net calls a Component an Aspect';
$string['data_manager_components_page_title'] = $string['zilink']. ' '.$string['data_manager'].': Assessment Components';
$string['data_manager_components_title'] = 'Assessment Components';
$string['data_manager_components_allowed'] = 'Components Allowed';

$string['data_manager_component_groups'] = 'Component Groups';
$string['data_manager_component_groups_title_desc'] = 'To display assessment data in Guardian View or Class View or to work with data in Report Writer you will need to identify the compoenent groups you want to include. '.
                                                        'N.B. SIMS.Net calls a Component Group an Assessment Template';
$string['data_manager_component_groups_page_title'] = $string['zilink']. ' '.$string['data_manager'].': Assessment Component Groups';
$string['data_manager_component_groups_title'] = 'Assessment Component Groups';
$string['data_manager_component_groups_allowed'] = 'Component Groups Allowed';

$string['data_manager_resultsets'] = 'Resultsets';
$string['data_manager_gradesets'] = 'Gradesets';
$string['data_manager_results'] = 'Results';

$string['data_manager_support_desc'] = 'For more information about configuring the ZiLink Data Manager please see our ';

$string['data_manager_awaiting_data'] = 'No Component Groups have been exported. '; 
