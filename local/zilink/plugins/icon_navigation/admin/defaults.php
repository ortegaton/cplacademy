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

    if(!isset($CFG->zilink_icon_navigation_iconset))
    {

        $default = get_config(null,'zilink_icon_navigation_iconset');
        if($default)
        {
            $CFG->zilink_icon_navigation_iconset = $default;
        }
        else 
        {
            $CFG->zilink_icon_navigation_iconset  = 'default';
            set_config('zilink_icon_navigation_iconset','default');
        }
    }
    
    if(!isset($CFG->zilink_icon_navigation_size))
    {

        $default = get_config(null,'zilink_icon_navigation_size');
        if($default)
        {
            $CFG->zilink_icon_navigation_size = $default;
        }
        else 
        {
            $CFG->zilink_icon_navigation_size  = '50';
            set_config('zilink_icon_navigation_size','50');
        }
    }
    
    $categories = $DB->get_records('course_categories',array('parent' => $CFG->zilink_category_root),'name ASC');
    
    foreach ($categories as $index => $category)
    {
        if(!isset($CFG->{'zilink_icon_navigation_category_icon_'.$category->id}))
        {
            $default = get_config(null,'zilink_icon_navigation_category_icon_'.$category->id);
            if($default)
            {
                $CFG->{'zilink_icon_navigation_category_icon_'.$category->id} = $default;
            }
            else 
            {
                $CFG->{'zilink_icon_navigation_category_icon_'.$category->id} = '0';
            }
                
        } 
    }
    
