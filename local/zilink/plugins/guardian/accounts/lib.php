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

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))).'/core/data.php');
require_once(dirname(dirname(dirname(__FILE__))).'/core/person.php');
require_once(dirname(dirname(dirname(__FILE__))).'/core/base.php');
 
class ZiLinkGuardianAccounts extends ZiLinkBase {

    function __construct($offset = 0){
        global $CFG,$DB;
        
        $this->data = new ZiLinkData();
        $this->httpswwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
        $this->person = new ZiLinkPerson();

    }
    
    public function PopulateFilters()
    {
        $this->filters = $this->data->GetFilterData();
    }
    
    public function GetRegistrationGroups()
    {
        return $this->filters['registration'];
    }
    
    public function GetHouseGroups()
    {
        return $this->filters['house'];
    }
    
    public function GetYesrGroups()
    {
        return $this->filters['year'];
    }
    
    public function MacthingList($args) 
    {
        return $this->data->GetMatchingData($args);

    }
    
    public function LinkAccounts($data)
    {
        global $CFG,$DB;
        
        $students = array();
    
        foreach($data->link as $studentidnumber => $guardians ) {
        
            $student = $DB->get_record('user', array('idnumber' => $studentidnumber));
            
            if($student) {
                
                $s = new stdClass();
                $s->fullname = fullname($student);
                $s->guardians = array();
            
                $context = context_user::instance($student->id);
                
                foreach($guardians as $guardianidnumber => $value)
                {
                    if($value == '1') 
                    {
                        $guardian = $DB->get_record('user', array('idnumber' => $guardianidnumber));
                        
                        if(!$guardian)
                        {
                             
                            $record = $DB->get_record('zilink_user_data', array('user_idnumber' => $guardianidnumber));
                            
                            $guardiandetails = @simplexml_load_string(base64_decode($record->extended_details));
                            
                            if (is_object($guardiandetails)) {
                                
                                $details = $this->data->ValidateDetails($guardiandetails);

                                if (empty($details)) {
                                    continue;
                                }
                                extract($details);
                                
                                $username = '';
                                $firstname = !empty($name->PreferredGivenName) ? $this->FormatName((string)$name->PreferredGivenName) : $this->FormatName((string)$name->GivenName);
                                $lastname = !empty($name->PreferredFamilyName) ? $this->FormatName((string)$name->PreferredFamilyName) : $this->FormatName((string)$name->FamilyName);
                                     
                                $guardian = (object) array( 'id' => null,
                                                        'auth' => 'zilink_guardian',
                                                        'confirmed' => 1,
                                                        'deleted' => 0,
                                                        'username' => $this->data->Security()->GenerateUsername($firstname, $lastname),
                                                        'idnumber' => $guardianidnumber,
                                                        'timecreated' => time(),
                                                        'timemodified' => time(),
                                                        'firstname' => $firstname,
                                                        'lastname' => $lastname,
                                                        'mnethostid' => $CFG->mnet_localhost_id,
                                                        'deleted' => 0,
                                                        'maildisplay' => 0,
                                                        'email' => $email,
                                                        'city' => $CFG->zilink_guardian_accounts_default_city,
                                                        'country' => $CFG->zilink_guardian_accounts_default_country,
                                                        'lang' => $CFG->zilink_guardian_accounts_default_lang
                                );
                                $guardian->id = $DB->insert_record('user', $guardian);
                                $guardian = get_complete_user_data('id', $guardian->id);
                                set_user_preference('auth_forcepasswordchange', 1, $guardian);
                                $password = generate_password(10);
                                update_internal_user_password($guardian, $password);
                                $userpassword = new stdClass();
                                $userpassword->user_idnumber = $guardianidnumber;
                                $userpassword->password = $password;
                                $userpassword->id = $DB->insert_record('zilink_guardian_passwords', $userpassword);
                                
                                $event = \core\event\user_created::create(
                                array(
                                    'objectid' => $guardian->id,
                                    'relateduserid' => $guardian->id,
                                    'context' => context_user::instance($guardian->id)
                                    )
                                );
                                $event->trigger();
                            }
                        }
                            
                        $g = new stdClass();
                        $g->fullname = fullname($guardian);
                        $g->linked = 1;
                        
                        role_assign($data->role, $guardian->id , $context->id, 'auth_zilink_guardian');
                        
                        $s->guardians[] = $g;   
                    }
                }
                if(count($s->guardians) > 0) {
                    $students[] = $s;
                }
            }
    
        }
 
        return $students;
    }
    
    
    public function UnlinkAccounts($data)
    {
        global $CFG,$DB;
        
        $guardianrole = $DB->get_record('role', array('shortname' => 'zilink_guardians'));
        $guardianrolerestricted = $DB->get_record('role', array('shortname' => 'zilink_guardians_restricted'));
    
    
        $students = array();
    
        foreach($data->unlink as $studentidnumber => $guardians ) {
            
            $student = $DB->get_record('user', array('idnumber' => $studentidnumber));
        
            if($student) {
                
                $s = new stdClass();
                $s->fullname = fullname($student);
                $s->guardians = array();
            
                $context = context_user::instance($student->id);
                
                foreach($guardians as $guardianidnumber => $value)
                {
                    if($value == '1') 
                    {
                        $guardian = $DB->get_record('user', array('idnumber' => $guardianidnumber));

                        if($guardian)
                        {
                            $g = new stdClass();
                            $g->fullname = fullname($guardian);
                            $g->linked = 0;
                            $s->guardians[] = $g;
                            
                            try {
                                role_unassign($guardianrole->id, $guardian->id , $context->id, 'auth_zilink_guardian');
                                role_unassign($guardianrolerestricted->id, $guardian->id , $context->id, 'auth_zilink_guardian');
                            } catch (Exception $e){
                                
                            }
                            
                        }
                    }
                }
                if(count($s->guardians) > 0) {
                    $students[] = $s;
                }
            }
        }

        return $students;
    }
    
    public function Export() {
        
        global $CFG,$DB;
         
        $guardiansusername = array();
        $guardianspassword = array();
    
        $guardiansusername[] = array('First Name', 'Last Name', 'Salutation', 'Address', 'Username', 'Email','Child','Reg Group', 'Year','House');
        $guardianspassword[] = array('First Name', 'Last Name', 'Salutation', 'Address', 'Password', 'Email','Child','Reg Group', 'Year','House');
      
        $records = $DB->get_records('zilink_guardian_passwords');
        
        foreach( $records as $record)
        {
            $guardiandetails = $this->person->GetPersonData(array('extended_details','user'), $record->user_idnumber);
            
            if(empty($guardiandetails) || !is_object($guardiandetails) ||!is_object($guardiandetails->extended_details))
            {
                continue;
            }
            
            if (!is_object($guardiandetails->extended_details->PersonalInformation->Name) && !is_object($guardiandetails->extended_details->PersonalInformation->Address)) {
                continue;
            }
            
            $givenname = (string)$guardiandetails->extended_details->PersonalInformation->Name->GivenName;
            if(empty($givenname)) {
                $givenname = (string)$guardiandetails->extended_details->PersonalInformation->Name->PreferredGivenName;
            }
            
            $familyname = $guardiandetails->extended_details->PersonalInformation->Name->FamilyName;

            $salutation = substr($guardiandetails->extended_details->PersonalInformation->Name->FullName,
                                 0,
                                strpos($guardiandetails->extended_details->PersonalInformation->Name->FullName, ' ')) . ' ';
            $salutation .= substr($givenname, 0, 1) . ' ';
            $salutation .= $guardiandetails->extended_details->PersonalInformation->Name->FamilyName;

            $address = '';

            if (isset($guardiandetails->extended_details->PersonalInformation->Address->PAON->Description)) {
                $address .= $guardiandetails->extended_details->PersonalInformation->Address->PAON->Description  . chr(13) . chr(10);
            } else {
                $address .= '';
            }

            if (isset($guardiandetails->extended_details->PersonalInformation->Address->PAON)) {
                if (isset($guardiandetails->extended_details->PersonalInformation->Address->PAON->StartNumber)) {
                    $address .= $guardiandetails->extended_details->PersonalInformation->Address->PAON->StartNumber;
                }
                if (isset($guardiandetails->extended_details->PersonalInformation->Address->PAON->EndNumber)) {
                    $address .= '-' . $guardiandetails->extended_details->PersonalInformation->Address->PAON->EndNumber;
                }
            }

            if (isset($guardiandetails->extended_details->PersonalInformation->Address->SAON->Description)) {
                $address .= ' '.$guardiandetails->extended_details->PersonalInformation->Address->SAON->Description;
            } else {
                $address .= '';
            }

            if (isset($guardiandetails->extended_details->PersonalInformation->Address->SAON)) {
                if (isset($guardiandetails->extended_details->PersonalInformation->Address->SAON->StartNumber)) {
                    $address .= $guardiandetails->extended_details->PersonalInformation->Address->SAON->StartNumber;
                }
                if (isset($guardiandetails->extended_details->PersonalInformation->Address->SAON->EndNumber)) {
                    $address .= '-' . $guardiandetails->extended_details->PersonalInformation->Address->SAON->EndNumber;
                }
            }

            if (isset($guardiandetails->extended_details->PersonalInformation->Address->Street)) {
                $address .= ' '.$guardiandetails->extended_details->PersonalInformation->Address->Street . chr(13) . chr(10);
            }

            if (isset($guardiandetails->extended_details->PersonalInformation->Address->Locality)) {
                $address .= $guardiandetails->extended_details->PersonalInformation->Address->Locality . chr(13) . chr(10);
            }

            if (isset($guardiandetails->extended_details->PersonalInformation->Address->Town)) {
                $address .= $guardiandetails->extended_details->PersonalInformation->Address->Town . chr(13) . chr(10);
            }

            if (isset($guardiandetails->extended_details->PersonalInformation->Address->AdministrativeArea)) {
                $address .= $guardiandetails->extended_details->PersonalInformation->Address->AdministrativeArea . chr(13) . chr(10);
            }

            if (isset($guardiandetails->extended_details->PersonalInformation->Address->PostTown)) {
                if (isset($guardiandetails->extended_details->PersonalInformation->Address->Town) && $guardiandetails->extended_details->PersonalInformation->Address->PostTown <> $guardiandetails->extended_details->PersonalInformation->Address->Town) {
                    $address .= $guardiandetails->extended_details->PersonalInformation->Address->PostTown . chr(13) . chr(10);
                }
            }

            if (isset($guardiandetails->extended_details->PersonalInformation->Address->County)) {
                $address .= $guardiandetails->extended_details->PersonalInformation->Address->County . chr(13) . chr(10);
            }

            if (isset($guardiandetails->extended_details->PersonalInformation->Address->PostCode)) {
                $address .= $guardiandetails->extended_details->PersonalInformation->Address->PostCode . chr(13) . chr(10);
            }
            
            
            $children = $this->person->Security()->GetLinkedChildren($record->user_idnumber);
            
            if(empty($children))
            {
                continue;
            }
            
            foreach($children as $child) 
            {
                $childdetails = $this->person->GetPersonData(array('details'), $child->idnumber);
                
                if($childdetails->details == null) {
                    continue;
                }
                
                try {
                    $rs = $childdetails->details->xpath('//schoolregistration');
                } catch (Exception $e ){
                    continue;
                }
                
                $year ='';
                $house = '';
                $registration ='';
                
                foreach ($rs as $r) {
                    if ($r->Attribute('year') <> 'UNKNOWN') {
                        $year = (string)$r->Attribute('year');
                    }
                    if ( $r->Attribute('house') <> 'UNKNOWN') {
                        $house = (string)$r->Attribute('house');
                    }
                    if ( $r->Attribute('registration') <> 'UNKNOWN') {
                        $registration = (string)$r->Attribute('registration');
                    }
                }
                
                $guardiansusername[] = array($givenname, $familyname, $salutation, $address, $guardiandetails->user->username, $guardiandetails->user->email,fullname($child), $registration, $year, $house);
                $guardianspassword[] = array($givenname, $familyname, $salutation, $address, $record->password, $guardiandetails->user->email,fullname($child), $registration, $year, $house);
            }
            
            
        }

        $this->auth_zilink_guardian_data_export_xls($guardiansusername, $guardianspassword, 'ZiLink Guardian Export', 1);
        
    }
    
    function auth_zilink_guardian_data_export_xls($export1, $export2, $dataname, $count) {
        global $CFG;
        require_once("$CFG->libdir/excellib.class.php");
        $filename = clean_filename($dataname);
        if ($count > 1) {
            $filename .= 's';
        }
        $filename .= clean_filename('-' . gmdate("Ymd_Hi"));
        $filename .= '.xls';
        $workbook = new MoodleExcelWorkbook('-');
        $workbook->send($filename);
        $worksheet = array();
        $worksheet[0] = $workbook->add_worksheet('Usernames');
        $rowno = 0;
        foreach ($export1 as $row) {
            $colno = 0;
            foreach ($row as $col) {
                $worksheet[0]->write_string($rowno, $colno, $col);
                $colno++;
            }
            $rowno++;
        }
        $worksheet[0] = $workbook->add_worksheet('Passwords');
        $rowno = 0;
        foreach ($export2 as $row) {
            $colno = 0;
            foreach ($row as $col) {
                $worksheet[0]->write_string($rowno, $colno, $col);
                $colno++;
            }
            $rowno++;
        }
        $workbook->close();
        die;
    }
    
    public function FormatName($str, $achar = array("'", "-", " ")) {
        $string = strtolower($str);
        foreach ($achar as $temp) {
            $pos = strpos($string, $temp);
            if ($pos) {
                $mend = '';
                $asplit = explode($temp, $string);
                foreach ($asplit as $temp2) {
                    $mend .= ucfirst($temp2).$temp;
                }
                $string = substr($mend, 0, -1);
            }
        }
        return ucfirst($string);
    }
}
