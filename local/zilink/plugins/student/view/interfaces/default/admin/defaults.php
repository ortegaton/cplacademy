<?php

    defined('MOODLE_INTERNAL') || die();

    if(!isset($CFG->zilink_student_view_pages_display_notification))
    {
        $default = get_config(null,'zilink_student_view_pages_display_notification');
        if($default)
        {
            $CFG->zilink_student_view_pages_display_notification = $default;
        }
        else 
        {
            $CFG->zilink_student_view_pages_display_notification  = '';
            set_config('zilink_student_view_pages_display_notification','');
        }
    }
    
    defined('MOODLE_INTERNAL') || die();

    if(!isset($CFG->zilink_student_view_subjects_allowed))
    {
        $default = get_config(null,'zilink_student_view_subjects_allowed');
        if($default)
        {
            $CFG->zilink_student_view_subjects_allowed = $default;
        }
        else 
        {
            $CFG->zilink_student_view_subjects_allowed  = '';
            set_config('zilink_student_view_subjects_allowed','');
        }
    }

    if(!isset($CFG->zilink_student_view_pages_notification))
    {
        $default = get_config(null,'zilink_student_view_pages_notification');
        if($default)
        {
            $CFG->zilink_student_view_pages_notification = $default;
        }
        else 
        {
            $CFG->zilink_student_view_pages_notification  = '';
            set_config('zilink_student_view_pages_notification','');
        }
    }

    if(!isset($CFG->zilink_student_view_interface))
    {
        $default = get_config(null,'zilink_student_view_interface');
        if($default)
        {
            $CFG->zilink_student_view_interface = $default;
        }
        else 
        {
            $CFG->zilink_student_view_interface  = 'default';
            set_config('zilink_student_view_interface','default');
        }
    }
    
    if(!isset($CFG->zilink_student_view_attendance_delay))
    {
        $default = get_config(null,'zilink_student_view_attendance_delay');
        if($default)
        {
            $CFG->zilink_student_view_attendance_delay = $default;
        }
        else 
        {
            $CFG->zilink_student_view_attendance_delay  = '14';
            set_config('zilink_student_view_attendance_delay','14');
        }
    }
    
    
    
    
?>