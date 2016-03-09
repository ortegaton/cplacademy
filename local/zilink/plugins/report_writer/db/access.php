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

defined('MOODLE_INTERNAL') || die(); 
 
$report_writer_capabilities = array(
        
        'local/zilink:report_writer_addinstance' => array(
            'riskbitmask' => RISK_PERSONAL,
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'legacy' => array(
                'student' => CAP_PREVENT,
                'teacher' => CAP_INHERIT,
                'editingteacher' => CAP_INHERIT,
                'manager' => CAP_INHERIT,
            )
        ),
        'local/zilink:report_writer_configure' => array(
            'riskbitmask' => RISK_PERSONAL,
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'legacy' => array(
                'student' => CAP_PREVENT,
                'teacher' => CAP_PREVENT,
                'editingteacher' => CAP_INHERIT,
                'manager' => CAP_INHERIT,
            )
        ),
        'local/zilink:report_writer_subject_teacher_edit' => array(
            'riskbitmask' => RISK_PERSONAL,
            'captype' => 'write',
            'contextlevel' => CONTEXT_COURSE,
            'legacy' => array(
                'student' => CAP_PREVENT,
                'teacher' => CAP_INHERIT,
                'editingteacher' => CAP_INHERIT,
                'manager' => CAP_INHERIT,
            )
        ),
        'local/zilink:report_writer_subject_teacher_edit' => array(
            'riskbitmask' => RISK_PERSONAL,
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'legacy' => array(
                'student' => CAP_PREVENT,
                'teacher' => CAP_INHERIT,
                'editingteacher' => CAP_INHERIT,
                'manager' => CAP_INHERIT,
            )
        ),
        'local/zilink:report_writer_subject_leader_edit' => array(
            'riskbitmask' => RISK_PERSONAL,
            'captype' => 'write',
            'contextlevel' => CONTEXT_COURSE,
            'legacy' => array(
                'student' => CAP_PREVENT,
                'teacher' => CAP_INHERIT,
                'editingteacher' => CAP_INHERIT,
                'manager' => CAP_INHERIT,
            )
        ),
        'local/zilink:report_writer_subject_leader_edit' => array(
            'riskbitmask' => RISK_PERSONAL,
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'legacy' => array(
                'student' => CAP_PREVENT,
                'teacher' => CAP_INHERIT,
                'editingteacher' => CAP_INHERIT,
                'manager' => CAP_INHERIT,
            )
        ),
        'local/zilink:report_writer_senior_management_team_edit' => array(
            'riskbitmask' => RISK_PERSONAL,
            'captype' => 'write',
            'contextlevel' => CONTEXT_COURSE,
            'legacy' => array(
                'student' => CAP_PREVENT,
                'teacher' => CAP_INHERIT,
                'editingteacher' => CAP_INHERIT,
                'manager' => CAP_INHERIT,
            )
        ),
        'local/zilink:report_writer_senior_management_team_edit' => array(
            'riskbitmask' => RISK_PERSONAL,
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'legacy' => array(
                'student' => CAP_PREVENT,
                'teacher' => CAP_INHERIT,
                'editingteacher' => CAP_INHERIT,
                'manager' => CAP_INHERIT,
            )
        ),
    );