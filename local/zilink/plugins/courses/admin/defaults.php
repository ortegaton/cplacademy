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

    if(!isset($CFG->zilink_course_sorting))
    {
        $default = get_config(null,'zilink_course_sorting');
        if($default)
        {
            $CFG->zilink_course_sorting = $default;
        }
        else 
        {
            $CFG->zilink_course_sorting  = 1;
            set_config('zilink_course_sorting','1');
        }
    }
    
    if(!isset($CFG->zilink_course_template))
    {
        $default = get_config(null,'zilink_course_template');
        if($default)
        {
            $CFG->zilink_course_template = $default;
        }
        else 
        {
            $CFG->zilink_course_template  = 0;
            set_config('zilink_course_template','0');
        }
    }
    
    if(!isset($CFG->zilink_course_auto_create_classes))
    {
        $default = get_config(null,'zilink_course_auto_create_classes');
        if($default)
        {
            $CFG->zilink_course_auto_create_classes = $default;
        }
        else 
        {
            $CFG->zilink_course_auto_create_classes  = 0;
            set_config('zilink_course_auto_create_classes','0');
        }
    }
    
    if(!isset($CFG->zilink_course_auto_create_years))
    {
        $default = get_config(null,'zilink_course_auto_create_years');
        if($default)
        {
            $CFG->zilink_course_auto_create_years = $default;
        }
        else 
        {
            $CFG->zilink_course_auto_create_years  = 0;
            set_config('zilink_course_auto_create_years','0');
        }
    }
    