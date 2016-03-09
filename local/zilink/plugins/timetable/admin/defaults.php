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

    if(!isset($CFG->zilink_timetable_week_format))
    {
        $default = get_config(null,'zilink_timetable_week_format');
        if($default)
        {
            $CFG->zilink_timetable_week_format = $default;
        }
        else 
        {
            $CFG->zilink_timetable_week_format  = 0;
            set_config('zilink_timetable_week_format','0');
        }
    }
    
    if(!isset($CFG->zilink_timetable_offset))
    {
        $default = get_config(null,'zilink_timetable_offset');
        if($default)
        {
            $CFG->zilink_timetable_offset = $default;
        }
        else 
        {
            $CFG->zilink_timetable_offset  = 0;
            set_config('zilink_timetable_offset','0');
        }
    }
    
    if(!isset($CFG->zilink_timetable_startday))
    {
        $default = get_config(null,'zilink_timetable_startday');
        if($default)
        {
            $CFG->zilink_timetable_startday = $default;
        }
        else 
        {
            $CFG->zilink_timetable_startday  = 'Monday';
            set_config('zilink_timetable_startday','Monday');
        }
    }
    
    if(!isset($CFG->zilink_timetable_display_time))
    {
        $default = get_config(null,'zilink_timetable_display_time');
        if($default)
        {
            $CFG->zilink_timetable_display_time = $default;
        }
        else 
        {
            $CFG->zilink_timetable_display_time  = 1;
            set_config('zilink_timetable_display_time','1');
        }
    }
    
    if(!isset($CFG->zilink_timetable_time_offset))
    {
        $default = get_config(null,'zilink_timetable_time_offset');
        if($default)
        {
            $CFG->zilink_timetable_time_offset = $default;
        }
        else 
        {
            $CFG->zilink_timetable_time_offset  = 0;
            set_config('zilink_timetable_time_offset','0');
        }
    }
    
    if(!isset($CFG->zilink_timetable_first_week))
    {
        $default = get_config(null,'zilink_timetable_first_week');
        if($default)
        {
            $CFG->zilink_timetable_first_week = $default;
        }
        else 
        {
            $CFG->zilink_timetable_first_week  = 1;
            set_config('zilink_timetable_first_week','1');
        }
    }
    
    if(!isset($CFG->zilink_timetable_room_label))
    {
        $default = get_config(null,'zilink_timetable_room_label');
        if($default)
        {
            $CFG->zilink_timetable_first_week = $default;
        }
        else 
        {
            $CFG->zilink_timetable_room_label  = 'code';
            set_config('zilink_timetable_room_label','code');
        }
    }