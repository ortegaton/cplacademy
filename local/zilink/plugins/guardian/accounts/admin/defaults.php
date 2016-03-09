<?php

    defined('MOODLE_INTERNAL') || die();

    if(!isset($CFG->zilink_guardian_accounts_username_prefix))
    {
        $default = get_config(null,'zilink_guardian_accounts_username_prefix');
        if($default)
        {
            $CFG->zilink_guardian_accounts_username_prefix = $default;
        }
        else 
        {
            $config = get_config('auth/zilink_guardian');
            
            if(empty($config->username_prefix))
            {
                $CFG->zilink_guardian_accounts_username_prefix  = 'par_';
                set_config('zilink_guardian_accounts_username_prefix','par_');
            } else {
                $CFG->zilink_guardian_accounts_username_prefix  = $config->username_prefix;
                set_config('zilink_guardian_accounts_username_prefix',$config->username_prefix);
            }
        }
    }
                   
    if(!isset($CFG->zilink_guardian_accounts_email_required))
    {
        $default = get_config(null,'zilink_guardian_accounts_email_required');
        if($default)
        {
            $CFG->zilink_guardian_accounts_email_required = $default;
        }
        else 
        {
            $config = get_config('auth/zilink_guardian');
            
            if(empty($config->email_required))
            {
                $CFG->zilink_guardian_accounts_email_required  = '1';
                set_config('zilink_guardian_accounts_email_required','1');
            } else {
                $CFG->zilink_guardian_accounts_email_required  = $config->email_required;
                set_config('zilink_guardian_accounts_email_required',$config->email_required);
            }
        }
    }
    
    
    if(!isset($CFG->zilink_guardian_accounts_default_city))
    {
        $default = get_config(null,'zilink_guardian_accounts_default_city');
        if($default)
        {
            $CFG->zilink_guardian_accounts_default_city = $default;
        }
        else 
        {
            $config = get_config('auth/zilink_guardian');
            
            if(empty($config->default_city))
            {
                $CFG->zilink_guardian_accounts_default_city  = '';
                set_config('zilink_guardian_accounts_default_city','');
            } else {
                $CFG->zilink_guardian_accounts_default_city  = $config->default_city;
                set_config('zilink_guardian_accounts_default_city',$config->default_city);
            }
        }
    }
    
    if(!isset($CFG->zilink_guardian_accounts_default_country))
    {
        $default = get_config(null,'zilink_guardian_accounts_default_country');
        if($default)
        {
            $CFG->zilink_guardian_accounts_default_country = $default;
        }
        else 
        {
            $config = get_config('auth/zilink_guardian');
            
            if(empty($config->default_country))
            {
                $CFG->zilink_guardian_accounts_default_country  = 'GB';
                set_config('zilink_guardian_accounts_default_country','GB');
            } else {
                $CFG->zilink_guardian_accounts_default_country  = $config->default_country;
                set_config('zilink_guardian_accounts_default_country',$config->default_country);
            }
        }
    }
    
    if(!isset($CFG->zilink_guardian_accounts_default_lang))
    {
        $default = get_config(null,'zilink_guardian_accounts_default_lang');
        if($default)
        {
            $CFG->zilink_guardian_accounts_default_lang = $default;
        }
        else 
        {
            $config = get_config('auth/zilink_guardian');
            
            if(empty($config->default_lang))
            {
                $CFG->zilink_guardian_accounts_default_lang  = 'en';
                set_config('zilink_guardian_accounts_default_lang','en');
            } else {
                $CFG->zilink_guardian_accounts_default_lang  = $config->default_lang;
                set_config('zilink_guardian_accounts_default_lang',$config->default_lang);
            }
        }
    }
    
    
    
?>