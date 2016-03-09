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
        require_once($CFG->dirroot.'/local/zilink/plugins/core/adminsettings.php');
        
        $settings = new admin_settingpage('local_zilink_icon_navigation_settings', get_string('icon_navigation', 'local_zilink'));
        $ADMIN->add('local_zilink', $settings);
        
        $settings->add(new admin_setting_heading('local_zilink_icon_navigation_settings', null ,get_string('icon_navigation_desc', 'local_zilink')));
        $settings->add(new admin_setting_heading('local_zilink_icon_navigation_support_settings', null ,get_string('icon_navigation_support_desc', 'local_zilink').html_writer::link('http://support.zilink.co.uk/hc/',get_string('support_site','local_zilink'),array('target'=> '_blank'))));
        
        $list = array();
        $list['default'] = 'deafult';
        
        $path = $CFG->dirroot.'/blocks/zilink/pix/icon_navigation/';
    
        $ignore = array( '.', '..');
        $dh = @opendir( $path );
        $default = array();
        
        while( false !== ( $file = readdir( $dh ) ) )
        {
            if( !in_array( $file, $ignore ) )
            {
                    if(is_dir( "$path/$file" ) )
                    {
                        $default[$file] = $file;
                    }
            }
        }
        $list = array_merge($list,$default);
        
        $settings->add(new admin_setting_configselect('zilink_icon_navigation_iconset',get_string('icon_navigation_iconset','block_zilink') , '','default', $list));
        
        $sql = 'SELECT id, name FROM {course_categories} WHERE parent = ? ORDER BY name ASC';
        
        if(!isset($CFG->enrol_zilink_root_category))   
            $CFG->enrol_zilink_root_category = 0;
        
        $categories = $DB->get_records_sql($sql,array($CFG->enrol_zilink_root_category));
    
        if(!isset($CFG->zilink_icon_navigation_iconset))
        {
            $CFG->zilink_icon_navigation_iconset = 'default';
        }
        
        $list = array();
        $default = array();
        $custom = array();
        
        $list[0] = 'None';
        
        $path = $CFG->dirroot.'/blocks/zilink/pix/icon_navigation/'.$CFG->zilink_icon_navigation_iconset.'/subjects';
    
        $ignore = array( '.', '..','core');
        $dh = @opendir( $path );
        
        while( false !== ( $file = readdir( $dh ) ) )
        {
            if( !in_array( $file, $ignore ) )
            {
                      if(!is_dir( "$path/$file" ) )
                      {
                          $name = explode('.',$file);
                          $default[$name[0]] = $name[0];
                      }
            }
        }
        
        closedir( $dh );
        
        ksort($default);
        
        $list = array_merge($list,$default);
        
        foreach ($categories as $index => $category)
        {
            $settings->add(new admin_setting_configselect_withoptiongroup('zilink_icon_navigation_'.$category->id, $category->name, '',0 ,$list));
        }
        
        $list = array();
        $list[50] = get_string('icon_navigation_size_small','block_zilink');
        $list[70] = get_string('icon_navigation_size_large','block_zilink');
        $list[100] = get_string('icon_navigation_size_xlarge','block_zilink');
        $settings->add(new admin_setting_configselect('zilink_icon_navigation_size', get_string('icon_navigation_size','block_zilink'), get_string('icon_navigation_size_desc','block_zilink'),50 ,$list));
     *
     */