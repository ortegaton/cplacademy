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
 
define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../../../../../../../config.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);

$action = required_param('action',PARAM_RAW);
$cohortid = required_param('cid',PARAM_INTEGER);

$homework_report = new ZiLinkHomeworkReport($courseid);
$security = new ZiLinkSecurity();
/*
if(!$security->IsAllowed('local/zilink:report_writer_subject_teacher_edit') &&
    !$security->IsAllowed('local/zilink:report_writer_subject_leader_edit') &&
    !$security->IsAllowed('local/zilink:report_writer_senior_management_team_edit'))
{
    die();
}
*/
switch ($action)
{

    case "view_homeworks":
      
        $uid = required_param('uid',PARAM_INTEGER);
        $start = required_param('start',PARAM_INTEGER);
        $end = required_param('end',PARAM_INTEGER);

        echo $homework_report->ViewHomework(array('cohort' => $cohortid,'homeworksetperiodstart' => $start, 'homeworksetperiodend' => $end, 'uid' => $uid));
        break;
    default:
        echo 'default';
          break;
}