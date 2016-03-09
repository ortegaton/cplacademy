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

 
$guardian_capabilities = array(
        
        'local/zilink:guardian_view_addinstance' => array(
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
        'local/zilink:guardian_view' => array(
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
        'local/zilink:guardian_view_student_details_photo' => array(
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
        'local/zilink:guardian_view_student_details_attendance' => array(
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
        'local/zilink:guardian_view_student_details_behaviour' => array(
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
        'local/zilink:guardian_view_student_details_achievement' => array(
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
        'local/zilink:guardian_view_attendance_recent' => array(
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
        'local/zilink:guardian_view_attendance_overview' => array(
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
        'local/zilink:guardian_view_homework' => array(
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
        'local/zilink:guardian_view_icons' => array(
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
        'local/zilink:guardian_view_subjects' => array(
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
        'local/zilink:guardian_view_subjects_teacher_details' => array(
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
        'local/zilink:guardian_view_subjects_teacher_details_email' => array(
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
        'local/zilink:guardian_view_subjects_assessment' => array(
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
        'local/zilink:guardian_view_subjects_homework' => array(
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
        'local/zilink:guardian_view_subjects_reports' => array(
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
        'local/zilink:guardian_view_subjects_submitted_work' => array(
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
        'local/zilink:guardian_view_reports' => array(
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
        'local/zilink:guardian_view_timetable' => array(
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
        'local/zilink:guardian_view_information' => array(
            'riskbitmask' => RISK_PERSONAL,
            'captype' => 'write',
            'contextlevel' => CONTEXT_USER,
            'legacy' => array(
                'student' => CAP_PREVENT,
                'teacher' => CAP_INHERIT,
                'editingteacher' => CAP_INHERIT,
                'manager' => CAP_PREVENT,
            )
        )
        ,
        'local/zilink:guardian_view_information_student_address' => array(
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
        'local/zilink:guardian_view_subjects_overview_assessment' => array(
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
        'local/zilink:guardian_scheduler_manage' => array(
        // Manage edit, create and delete parents schedulers
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'local/zilink:guardian_scheduler_book' => array(
        // Create a booking (if not set to allow anon bookings)
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_ALLOW
        )
    ),

    'local/zilink:guardian_scheduler_cancel' => array(
        // Cancel bookings
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        )
    ),

    'local/zilink:guardian_scheduler_viewall' => array(
        // View all bookings
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW
        )
    )
    );
/*    
$sub_path = $CFG->dirroot.'/local/zilink/plugins/guardian/';

$sub_directories = array();
$sub_ignore = array( '.', '..','core','admin','db','lang');
$dh2 = @opendir( $sub_path );

while( false !== ( $file2 = readdir( $dh2 ) ) )
{
        if( !in_array( $file2, $sub_ignore ) )
        {
            if(is_dir( "$sub_path/$file2" ) )
            {
                $sub_directories[$file2] = $file2;
            }
    }
}
closedir( $dh2 );

foreach($sub_directories as $sub_directory)
{
    if(file_exists($CFG->dirroot.'/local/zilink/plugins/guardian/'.$sub_directory.'/db/access.php'))
    {
        include($CFG->dirroot.'/local/zilink/plugins/guardian/'.$sub_directory.'/db/access.php');
        $guardian_capabilities = array_merge($guardian_capabilities,${'guardian_'.$directory.'_capabilities'});
    }
}
*/