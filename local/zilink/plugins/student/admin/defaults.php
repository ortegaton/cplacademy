<?php

    defined('MOODLE_INTERNAL') || die();

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
                
        if(!isset($CFG->zilink_student_view_default_attendance_overview_delay))
    {
        $default = get_config(null,'zilink_student_view_default_attendance_overview_delay');
        if($default)
        {
            $CFG->zilink_student_view_default_attendance_overview_delay = $default;
        }
        else 
        {
            $CFG->zilink_student_view_default_attendance_overview_delay  = '14';
            set_config('zilink_student_view_default_attendance_overview_delay','14');
        }
    }
    
    
    $settings = array('zilink_student_view_default_assessment_overview_general_comment',
                      'zilink_student_view_default_assessment_overview_below_comment',
                      'zilink_student_view_default_assessment_overview_level_comment',
                      'zilink_student_view_default_assessment_overview_above_comment');
    
    foreach ($settings as $setting)
    {
        if(!isset($CFG->{$setting}))
        {
            $default = get_config(null,$setting);
            if($default)
            {
                $CFG->{$setting} = $default;
            }
            else 
            {
                $CFG->{$setting}  = '';
                set_config($setting,'');
            }
        }
    }
        
    $settings = array('zilink_student_view_default_assessment_overview_below_trigger',
                      'zilink_student_view_default_assessment_overview_level_trigger',
                      'zilink_student_view_default_assessment_overview_above_trigger');
    
    foreach ($settings as $setting)
    {
        if(!isset($CFG->{$setting}))
        {
            $default = get_config(null,$setting);
            if($default)
            {
                $CFG->{$setting} = $default;
            }
            else 
            {
                $CFG->{$setting}  = '-1';
                set_config($setting,'-1');
            }
        }
    }
    
    $settings = array('zilink_student_view_default_assessment_subjects_general_comment',
                      'zilink_student_view_default_assessment_subjects_below_comment',
                      'zilink_student_view_default_assessment_subjects_level_comment',
                      'zilink_student_view_default_assessment_subjects_above_comment');
    
    foreach ($settings as $setting)
    {
        if(!isset($CFG->{$setting}))
        {
            $default = get_config(null,$setting);
            if($default)
            {
                $CFG->{$setting} = $default;
            }
            else 
            {
                $CFG->{$setting}  = '';
                set_config($setting,'');
            }
        }
    }
        
    $settings = array('zilink_student_view_default_assessment_subjects_below_trigger',
                      'zilink_student_view_default_assessment_subjects_level_trigger',
                      'zilink_student_view_default_assessment_subjects_above_trigger');
    
    foreach ($settings as $setting)
    {
        if(!isset($CFG->{$setting}))
        {
            $default = get_config(null,$setting);
            if($default)
            {
                $CFG->{$setting} = $default;
            }
            else 
            {
                $CFG->{$setting}  = '-1';
                set_config($setting,'-1');
            }
        }
    }
    
    $settings = array('zilink_student_view_default_attendance_overview_general_comment',
                      'zilink_student_view_default_attendance_overview_present_below_comment',
                      'zilink_student_view_default_attendance_overview_present_above_comment',
                      'zilink_student_view_default_attendance_overview_late_below_comment',
                      'zilink_student_view_default_attendance_overview_late_above_comment',
                      'zilink_student_view_default_attendance_overview_authorised_absence_below_comment',
                      'zilink_student_view_default_attendance_overview_authorised_absence_above_comment',
                      'zilink_student_view_default_attendance_overview_unauthorised_absence_below_comment',
                      'zilink_student_view_default_attendance_overview_unauthorised_absence_above_comment');
    
    foreach ($settings as $setting)
    {
        if(!isset($CFG->{$setting}))
        {
            $default = get_config(null,$setting);
            if($default)
            {
                $CFG->{$setting} = $default;
            }
            else 
            {
                $CFG->{$setting}  = '';
                set_config($setting,'');
            }
        }
    }
        
    $settings = array('zilink_student_view_default_attendance_overview_present_below_trigger',
                      'zilink_student_view_default_attendance_overview_present_above_trigger',
                      'zilink_student_view_default_attendance_overview_late_below_trigger',
                      'zilink_student_view_default_attendance_overview_late_above_trigger',
                      'zilink_student_view_default_attendance_overview_authorised_absence_below_trigger',
                      'zilink_student_view_default_attendance_overview_authorised_absence_above_trigger',
                      'zilink_student_view_default_attendance_overview_unauthorised_absence_below_trigger',
                      'zilink_student_view_default_attendance_overview_unauthorised_absence_above_trigger');
    
    foreach ($settings as $setting)
    {
        if(!isset($CFG->{$setting}))
        {
            $default = get_config(null,$setting);
            if($default)
            {
                $CFG->{$setting} = $default;
            }
            else 
            {
                $CFG->{$setting}  = '-1';
                set_config($setting,'-1');
            }
        }
    }
    
    
?>