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
 * @package     block_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
 
$class_view_capabilities = array(
        
        'local/zilink:class_view_addinstance' => array(
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
        'local/zilink:class_view' => array(
            'riskbitmask' => RISK_PERSONAL,
            'captype' => 'write',
            'contextlevel' => CONTEXT_USER,
            'legacy' => array(
                'student' => CAP_PREVENT,
                'teacher' => CAP_INHERIT,
                'editingteacher' => CAP_INHERIT,
                'manager' => CAP_PREVENT,
            )
        ),
        'local/zilink:class_view_assessment' => array(
            'riskbitmask' => RISK_PERSONAL,
            'captype' => 'write',
            'contextlevel' => CONTEXT_USER,
            'legacy' => array(
                'student' => CAP_PREVENT,
                'teacher' => CAP_INHERIT,
                'editingteacher' => CAP_INHERIT,
                'manager' => CAP_PREVENT,
            )
        ),
        'local/zilink:class_view_attendance' => array(
            'riskbitmask' => RISK_PERSONAL,
            'captype' => 'write',
            'contextlevel' => CONTEXT_USER,
            'legacy' => array(
                'student' => CAP_PREVENT,
                'teacher' => CAP_INHERIT,
                'editingteacher' => CAP_INHERIT,
                'manager' => CAP_PREVENT,
            )
        ),
    );