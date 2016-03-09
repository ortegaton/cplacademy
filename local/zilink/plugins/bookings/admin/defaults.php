<?php
   
    if(!isset($CFG->zilink_bookings_rooms_system))
    {
        $default = get_config(null,'zilink_bookings_rooms_system');
        if($default)
        {
            $CFG->zilink_bookings_rooms_system = $default;
        }
        else 
        {
            $CFG->zilink_bookings_rooms_system  = 'internal';
            set_config('zilink_bookings_rooms_system','internal');
        }
    }
    
     
    if(!isset($CFG->zilink_bookings_rooms_alternative_link))
    {
        $default = get_config(null,'zilink_bookings_rooms_alternative_link');
        if($default)
        {
            $CFG->zilink_bookings_rooms_alternative_link = $default;
        }
        else 
        {
            $CFG->zilink_bookings_rooms_alternative_link  = '';
            set_config('zilink_bookings_rooms_alternative_link','');
        }
    }
    
    if(!isset($CFG->zilink_bookings_rooms_schoolbooking_link))
    {
        $default = get_config(null,'zilink_bookings_rooms_schoolbooking_link');
        if($default)
        {
            $CFG->zilink_bookings_rooms_schoolbooking_link = $default;
        }
        else 
        {
            $CFG->zilink_bookings_rooms_schoolbooking_link  = '';
            set_config('zilink_bookings_rooms_schoolbooking_link','');
        }
    }
    
    if(!isset($CFG->zilink_bookings_rooms_allowed_rooms))
    {
        $default = get_config(null,'zilink_bookings_rooms_allowed_rooms');
        if($default)
        {
            $CFG->zilink_bookings_rooms_allowed_rooms = $default;
        }
        else 
        {
            $CFG->zilink_bookings_rooms_allowed_rooms  = '';
            set_config('zilink_bookings_rooms_allowed_rooms','');
        }
    }
    
    
    if(!isset($CFG->zilink_bookings_rooms_weeks_in_advance))
    {
        $default = get_config(null,'zilink_bookings_rooms_weeks_in_advance');
        if($default)
        {
            $CFG->zilink_bookings_rooms_weeks_in_advance = $default;
        }
        else 
        {
            $CFG->zilink_bookings_rooms_weeks_in_advance  = 0;
            set_config('zilink_bookings_rooms_weeks_in_advance','0');
        }
    }
    
    if(!isset($CFG->zilink_bookings_rooms_email_notifications))
    {
        $default = get_config(null,'zilink_bookings_rooms_email_notifications');
        if($default)
        {
            $CFG->zilink_bookings_rooms_email_notifications = $default;
        }
        else 
        {
            $CFG->zilink_bookings_rooms_email_notifications  = 0;
            set_config('zilink_bookings_rooms_email_notifications','0');
        }
    }
    
    
    
    
    
