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

 
$homework_capabilities = array(
        
        'local/zilink:homework_report_addinstance' => array(
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
        'local/zilink:homework_report_view' => array(
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
        'local/zilink:homework_report_subject_teacher' => array(
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
        'local/zilink:homework_report_subject_leader' => array(
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
        'local/zilink:homework_report_senior_management_team' => array(
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