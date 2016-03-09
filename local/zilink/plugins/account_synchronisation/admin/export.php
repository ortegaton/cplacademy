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
 * Defines the settings for the ZiLink block
 *
 * @package     block_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once(dirname(__FILE__) . '/../../../../../config.php');
    require_once($CFG->libdir.'/csvlib.class.php');  
    require_once(dirname(dirname(__FILE__)).'/lib.php'); 

    require_login();
    
    if(has_capability('moodle/site:config',context_system::instance()))
    {
        
        $users = $DB->get_records('zilink_user_data');
            
        $intake = array(    "7" => 0,
                    "8" => 1,
                    "9" => 2,
                    "10" => 3,
                    "11" => 4,
                    "12" => 5,
                    "13" => 6,
                    "14" => 7);
    
        $students = array();
        $students[] = array('Legal First Name','Middle Name','Legal Last Name','Prefered Firt Name','Prefered Last Name','Year Group','Registration Group','Intake Year','UPN','Unique ID','Account Matched With');
        
        $staff = array();
        $staff[] = array('Legal First Name','Middle Name','Legal Last Name','Prefered Firt Name','Prefered Last Name','Unique ID','Account Matched With');
        
        
        if(is_array($users))
        {
            foreach ($users as $user)
            {
                if(!$user->details == null)
                {
                    $user->details = base64_decode($user->details);
                    $user->details = str_replace('ns2:', '',$user->details);
                    $user->details = str_replace(':ns2', '',$user->details);
                    
                    $record = simplexml_load_string($user->details,'simple_xml_extended');

                    if(!isset($record->person->schoolregistration))
                    {
                        if(is_object($record->person->name))
                        {
                            $givenname = $record->person->name->Attribute('givenname');
                            $middlenames = $record->person->name->Attribute('middlenames');
                            $familynames = $record->person->name->Attribute('familyname');
                            $preferredgivenname = $record->person->name->Attribute('preferredgivenname');
                            $preferredfamilyname = $record->person->name->Attribute('preferredfamilyname');
                            
                            $mdl_user = $DB->get_record('user',array('idnumber' => $user->user_idnumber));
                            if(is_object($mdl_user)) {
                                $staff[] = array($givenname,$middlenames,$familynames,$preferredgivenname,$preferredfamilyname,$user->user_idnumber,$mdl_user->username);
                            } else {
                                $staff[] = array($givenname,$middlenames,$familynames,$preferredgivenname,$preferredfamilyname,$user->user_idnumber,'');
                            }
                            
                            
                        }
                    }
                    else
                    {
                        if(is_numeric($record->person->schoolregistration->Attribute('year')))
                            $intake_year = date('y')-$intake[(int)$record->person->schoolregistration->Attribute('year')];
                        else 
                            $intake_year = '';
                            
                        $upn = $record->person->schoolregistration->Attribute('upn');
                        $mdl_user = $DB->get_record('user',array('idnumber' => $user->user_idnumber));
                        if(is_object($mdl_user)) {
                              $students[] = array($record->person->name->Attribute('givenname'),$record->person->name->Attribute('middlenames'),$record->person->name->Attribute('familyname'),$record->person->name->Attribute('preferredgivenname'),$record->person->name->Attribute('preferredfamilyname'),$record->person->schoolregistration->Attribute('year'),$record->person->schoolregistration->Attribute('registration'),$intake_year,$upn,$user->user_idnumber,$mdl_user->username);
                        } else {
                              $students[] = array($record->person->name->Attribute('givenname'),$record->person->name->Attribute('middlenames'),$record->person->name->Attribute('familyname'),$record->person->name->Attribute('preferredgivenname'),$record->person->name->Attribute('preferredfamilyname'),$record->person->schoolregistration->Attribute('year'),$record->person->schoolregistration->Attribute('registration'),$intake_year,$upn,$user->user_idnumber,'');
                        } 
                    }
                }
            }
            unset($record);
        }
        
        $account_sync = new local_zilink_account_synchronisation();
        $account_sync->ExportXLS($students,$staff,'Manual Export',1);
    }
 
