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

require_once(dirname(dirname(__FILE__)).'/core/person.php');
require_once(dirname(__FILE__).'/admin/forms/config.php');
require_once(dirname(__FILE__).'/admin/forms/matching.php');

class local_zilink_account_synchronisation {
    
    private $person;
    
    public function __construct()
    {
        $this->person = new ZiLinkPerson();
    }
    
    public function Configuration()
    {
        global $CFG;
        
        $form = new enrol_zilink_account_synchronisation_config_form('',array('sesskey' => sesskey() )); 
        if ($data = $form->get_data())
        {
                            
            if(isset($data->account_synchronisation_cron))
            {
                $CFG->zilink_account_synchronisation_cron = $data->account_synchronisation_cron;
                set_config('zilink_account_synchronisation_cron', $data->account_synchronisation_cron);
            }    

            if(isset($data->account_synchronisation_exclude_usernames))
            {
                $CFG->zilink_account_synchronisation_exclude_usernames = $data->account_synchronisation_exclude_usernames;
                set_config('zilink_account_synchronisation_exclude_usernames', $data->account_synchronisation_exclude_usernames);
            } 
             
        }
        $form->set_data(array('account_synchronisation_exclude_usernames' => $CFG->zilink_account_synchronisation_exclude_usernames));
        
        return $form->Display();
    }
    
    public function Matched()
    {
        global $CFG;
        
        $tab = optional_param('tid',1,PARAM_INTEGER);
        switch($tab)
        {
            case 1:
                $userrole = 'student';
                break;
            case 2:
                $userrole = 'teacher';
                break;
            case 3:
                return 'guardian';
                break;
        }  
        
        $form = new enrol_zilink_account_synchronisation_matching_form('',array('sesskey' => sesskey(),'tid'=>  $tab, 'userrole' => $userrole, 'matched' => true ));
        if ($data = $form->get_data())
        {
            if(isset($data->matchedusers))
            {
                foreach($data->matchedusers as $sifid => $userid)
                {
                    if(!$userid == 0)
                    {
                        $DB->set_field('user', 'idnumber', $sifid, array('id' => $userid));
                    }
                } 
            }  
        }
        return $this->GetTabs($CFG->httpswwwroot.'/local/zilink/plugins/account_synchronisation/admin/matched.php').$form->Display();
    }
    
    public function Unmatched()
    {
        global $CFG,$DB;
        
        $tab = optional_param('tid',1,PARAM_INTEGER);
        switch($tab)
        {
            case 1:
                $userrole = 'student';
                break;
            case 2:
                $userrole = 'teacher';
                break;
            case 3:
                return 'guardian';
                break;
        }  
        
        $form = new enrol_zilink_account_synchronisation_matching_form('',array('sesskey' => sesskey(),'tid'=>  $tab, 'userrole' => $userrole, 'matched' => false )); 
        if ($data = $form->get_data())
        {
            if(isset($data->matchedusers))
            {
                foreach($data->matchedusers as $sifid => $userid)
                {
                    if(!$userid == 0)
                    {
                        $DB->set_field('user', 'idnumber', $sifid, array('id' => $userid));
                    }
                } 
            }      
        }
        return $this->GetTabs($CFG->httpswwwroot.'/local/zilink/plugins/account_synchronisation/admin/unmatched.php').$form->Display();
    }
    
    public function Export()
    {
        global $OUTPUT;  
        return $OUTPUT->single_button(new moodle_url('/local/zilink/plugins/account_synchronisation/admin/export.php'),get_scring('account_synchronisation_export','local_export'));
    }
    
    private function GetRole()
    {
        switch(optional_param('tid',1,PARAM_INTEGER))
        {
            case 1:
                return 'student';
                break;
            case 2:
                return 'teacher';
                break;
            case 3:
                return 'guardian';
                break;
        }
    }
    
    private function GetTabs($url)
    {
        global $CFG;
        
        $tab = optional_param('tid',1,PARAM_INTEGER);
        
        $urlparams = array( 'sesskey' => sesskey(),
                            'tid' => $tab  );
                            
        $url = new moodle_url($url, array( 'sesskey' => sesskey(), 'tid' => 1  ));
        
        $row = array();

        $row[] = new tabobject(1, new moodle_url($url, array( 'sesskey' => sesskey(), 'tid' => 1 )),get_string('students'));
        $row[] = new tabobject(2, new moodle_url($url, array( 'sesskey' => sesskey(), 'tid' => 2 )),get_string('staff','local_zilink'));
        
        $tabs = array($row);
        
        return print_tabs($tabs, optional_param('tid',1,PARAM_INTEGER), null, null, true);
        
    }

    public function ExportXLS($export1,$export2, $dataname, $count) 
    {
        global $CFG;
        require_once("$CFG->libdir/excellib.class.php");
        $filename = clean_filename($dataname);
        if ($count > 1) 
        {
            $filename .= 's';
        }
        $filename .= clean_filename('-' . gmdate("Ymd_Hi"));
        $filename .= '.xls';
        $workbook = new MoodleExcelWorkbook('-');        
        $workbook->send($filename);
        $worksheet = array();
        $worksheet[0] = $workbook->add_worksheet('Students');
        $rowno = 0;
        foreach ($export1 as $row) 
        {
            $colno = 0;
            foreach($row as $col) 
            {
                $worksheet[0]->write_string($rowno, $colno, $col);
                $colno++;
            }
            $rowno++;
        }
        $worksheet[0] = $workbook->add_worksheet('Staff');
        $rowno = 0;
        foreach ($export2 as $row) 
        {
            $colno = 0;
            foreach($row as $col) 
            {
                $worksheet[0]->write_string($rowno, $colno, $col);
                $colno++;
            }
            $rowno++;
        }
        $workbook->close();
        die;
    }

    //FIXME change logice to check for false;
    private function NameSearch($users,$type,$firstname,$lastname)
    {
        $firstname = trim($firstname);
        $lastname = trim($lastname);
        
        $matches = array();
        
        if(empty($firstname) || empty($lastname))
        {
            return $matches;
        }
        
        foreach($users as $user)
        {
            if(!empty($CFG->zilink_account_synchronisation_exclude_usernames))
            {
                $excludes = explode(",", $CFG->zilink_account_synchronisation_exclude_usernames);
                foreach($excludes as $exclude)
                {
                    if(stripos($user->username, strtolower($exclude)) !== false )
                    {
                        continue 2;
                    }
                }
            }
            
            $user->firstname = trim($user->firstname);
            $user->lastname = trim($user->lastname);
            switch ($type) 
            {
                case "beginswith":
                    if (stripos($user->firstname, $firstname) === 0 && stripos($user->lastname, $lastname) === 0)
                    {
                        $matches[] = $user;
                    }  
                    break;
                case "beginswithinital":
                    if (stripos($user->firstname, substr($firstname, 0 ,1)) === 0 && stripos($user->lastname, $lastname) === 0)
                    {
                        $matches[] = $user;
                    }  
                    break;
                case "in":
                    if(stristr($user->firstname, $firstname) && stristr($user->lastname, $lastname) )
                    {
                        $matches[] = $user;
                    }
                    break;
                case "inwithinital":
                    if(stristr($user->firstname, substr($firstname, 0 ,1)) && stristr($user->lastname, $lastname) )
                    {
                        $matches[] = $user;
                    }
                    break;
                case "inwithtitle":
                    $titles = array('Mr','Mrs','Miss','Ms');
                
                    foreach($titles as $title) {
                        
                        if(stristr($user->firstname, $title) && stristr($user->lastname, $lastname) )
                        {
                            if(stristr($user->firstname, $title. ' '.substr($firstname, 0 ,1)) && stristr($user->lastname, $lastname) )
                            {
                                $matches[] = $user;
                            }else{
                                $matches[] = $user;
                            }
                        }
                        
                    }
                    break;
                default:
                    break; 
                
            }
        }
        
        return $matches;
    }

    public function Cron()
    {
        global $CFG,$DB;
        
        if((defined('CLI_SCRIPT') && CLI_SCRIPT)  && $CFG->debug == DEBUG_DEVELOPER) {
            mtrace('Starting ZiLink User Data Pruning');
        }
        if($records = $DB->get_records('zilink_user_data', array('timemodified' => 0))) {
            
            foreach($records as $record){
                $record->timemodified = strtotime('now');
                $DB->update_record('zilink_user_data', $record);
            }
        }
        
        $DB->delete_records_select("zilink_user_data", "timemodified < ?", array(strtotime('-30 days')));
        //$DB->delete_records('zilink_user_data', array('timemodified' => strtotime('-30 days')));
        
        if((defined('CLI_SCRIPT') && CLI_SCRIPT)  && $CFG->debug == DEBUG_DEVELOPER) {
            mtrace('Finished ZiLink User Data Pruning');
            mtrace('Starting ZiLink account synchronisation');
        }
        
        $sql = "SELECT zud.id, zud.user_idnumber,zud.details,zud.roles FROM {zilink_user_data} zud LEFT JOIN {user} u ON u.idnumber =  zud.user_idnumber WHERE u.idnumber IS NULL OR u.idnumber = ''";
        //sql = "SELECT {zilink_user_data}.id,user_idnumber,details,roles FROM {zilink_user_data} LEFT JOIN {user} ON {zilink_user_data}.user_idnumber = {user}.idnumber AND ({user}.idnumber = '' OR {user}.idnumber = NULL)";
         
        $unmatchedusers = $DB->get_recordset_sql($sql);
        
        $sql = "SELECT * FROM {user} WHERE deleted = 0 AND mnethostid = $CFG->mnet_localhost_id and (idnumber = '' OR idnumber IS NULL)";
        $mdl_users = $DB->get_records_sql($sql);
        
        $transaction = $DB->start_delegated_transaction(); 
        
        $person = new ZiLinkPerson();
        
        foreach ($unmatchedusers as $unmatcheduser)
        {
            $data = $person->ProcessPersonData(array('role','details'),$unmatcheduser);
            $roles = $person->ProcessPersonRoles($data);
            
            $allowedroles = array('student','teacher');
            $allowedrole = false;
            foreach($roles as $role)
            {
                if(in_array($role,$allowedroles))
                    $allowedrole = true;
            }
            if($allowedrole)
            {
                $nearmatch = false;
                $matchedusers = array();
                
                $givenname = $data->details->person->name->Attribute('givenname');
                $familyname = $data->details->person->name->Attribute('familyname');
                
                $preferredgivenname = $data->details->person->name->Attribute('preferredgivenname');
                $preferredfamilyname = $data->details->person->name->Attribute('preferredfamilyname');
                
                $matchedusers = array_merge($matchedusers,$this->NameSearch($mdl_users,"beginswith",$givenname,$familyname));
                
                if((!empty($preferredgivenname) && !empty($preferredfamilyname)) && (($givenname <> $preferredgivenname) || ($familyname <> $preferredfamilyname)) )
                {
                    $matchedusers = array_merge($matchedusers,$this->NameSearch($mdl_users,"beginswith",$preferredgivenname,$preferredfamilyname));
                }
                
                if(empty($matchedusers)) {
                    
                    $nearmatch = true;
                    
                    $matchedusers = array_merge($this->NameSearch($mdl_users,"in",$givenname,$familyname));
                    $matchedusers = array_merge($matchedusers,$this->NameSearch($mdl_users,"inwithinital",$givenname,$familyname));
                    
                    if(!empty($preferredgivenname) && !empty($preferredfamilyname))
                    {
                        $matchedusers = array_merge($matchedusers,$this->NameSearch($mdl_users,"in",$preferredgivenname,$preferredfamilyname));
                        $matchedusers = array_merge($matchedusers,$this->NameSearch($mdl_users,"inwithinital",$preferredgivenname,$preferredfamilyname));
                    }
                    
                        if(empty($matchedusers)) {
                        $nearmatch = false;
                    }
                    
                    $matchedusers = array_merge($matchedusers,$this->NameSearch($mdl_users,"inwithtitle",$givenname,$familyname));
                    $matchedusers = array_merge($matchedusers,$this->NameSearch($mdl_users,"inwithtitle",$preferredgivenname,$preferredfamilyname));
                    
                }
                
                $options = array();
                foreach( $matchedusers as $matcheduser)
                {
                    $options[$matcheduser->id] = fullname($matcheduser) .' ( '.$matcheduser->username.' )';
                }
                
                if(count($options) == 1 && $nearmatch == false)
                {
                        
                    $user = array_shift($matchedusers);
                    $user->idnumber = $unmatcheduser->user_idnumber;
                    $DB->update_record('user',$user);
                    
                }
            } 
        }
        
        $transaction->allow_commit();
        $unmatchedusers->close();
        
        if((defined('CLI_SCRIPT') && CLI_SCRIPT)  && $CFG->debug == DEBUG_DEVELOPER) {
            mtrace('Finished ZiLink account synchronisation');  
        }
    }
}


function local_zilink_account_synchronisation_cron()
{
              
    $sync = new  local_zilink_account_synchronisation();
    $sync->Cron();
    return true;
}

