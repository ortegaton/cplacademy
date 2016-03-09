<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class enrol_zilink_account_synchronisation_matching_form extends moodleform {
    
    var $data;
    var $matcheduser;
    
    function definition() {

        global $CFG,$DB;
 
        $mform =& $this->_form;
        
        $hidden = $mform->addElement('hidden', 'tid',$this->_customdata['tid']);
        $hidden->setType(PARAM_INTEGER);
        
        $mform->addElement('html','<table class="generaltable tableleft boxaligncenter" width="100%" cellpadding="10px">
                                    <thead>
                                    <tr>');

        if($this->_customdata['userrole'] == 'student')
        {
            $mform->addElement('html',' <th class="header c0" style="text-align:left; vertical-align: middle;" scope="col">Name</th>
                                        <th class="header c1" style="text-align:left; vertical-align: middle;" scope="col">House</th>
                                        <th class="header c2" style="text-align:left; vertical-align: middle;" scope="col">Year</th>
                                        <th class="header c3" style="text-align:left; vertical-align: middle;" scope="col">Registration Group</th>
                                        <th class="header c4 lastcol" style="text-align:left; vertical-align: middle;" scope="col">Account Matched Against</th>');
        }
        else 
        {
            $mform->addElement('html',' <th class="header c0" style="text-align:left;" scope="col">Name</th>
                                        <th class="header c4 lastcol" style="text-align:left; vertical-align: middle;" scope="col">Account Matched Against</th>');
        }
        
        $mform->addElement('html','</tr>
                                    </thead>
                                    <tbody>');
        
        if($this->_customdata['matched'])
        {
            $sql = "SELECT zd.id,user_idnumber,details,roles FROM {zilink_user_data} zd, {user} u WHERE zd.user_idnumber = u.idnumber";
            $sifusers = $DB->get_recordset_sql($sql);
        } else {

            $sql = "SELECT {zilink_user_data}.id,user_idnumber,details,roles FROM {zilink_user_data} WHERE {zilink_user_data}.id NOT IN (SELECT zd.id FROM {zilink_user_data} zd, {user} u WHERE zd.user_idnumber = u.idnumber)";
            $sifusers = $DB->get_recordset_sql($sql);
        }
        
        $sql = "SELECT * FROM {user} WHERE deleted = 0 AND mnethostid = $CFG->mnet_localhost_id";
        $all_mdl_users = $DB->get_records_sql($sql);
        
        
        $sql = "SELECT * FROM {user} WHERE deleted = 0 AND mnethostid = $CFG->mnet_localhost_id AND (idnumber IS NULL or idnumber ='')";
        $unmatched_mdl_users = $DB->get_records_sql($sql);
        
        $flag = false;

        $transaction = $DB->start_delegated_transaction(); 
        $count = 0;
        $colcount = 0;    
        
        foreach($sifusers as $sifuser)
        {
            $this->loadData($sifuser);
            $roles = $this->GetRoles();
            if(in_array($this->_customdata['userrole'], $roles))
            {
                if($count == 0 || !($count % 2))
                    $mform->addElement('html','<tr class="r0">');
                else
                    $mform->addElement('html','​<tr class="r1">');
            
                $colcount = 0;
                $mform->addElement('html','<td class="cell c'.$colcount.'" style="text-align:left; vertical-align: middle;">'.$this->getPersonName().'</td>');
                $colcount++;                
                if($this->_customdata['userrole'] == 'student')
                {
                    $house = ($this->data->details->person->schoolregistration->Attribute('house') <> 'UNKNOWN') ? $this->data->details->person->schoolregistration->Attribute('house') : '-';
                    $year = ($this->data->details->person->schoolregistration->Attribute('year') <> 'UNKNOWN') ? $this->data->details->person->schoolregistration->Attribute('year') : '-';
                    $registration = ($this->data->details->person->schoolregistration->Attribute('registration')  <> 'UNKNOWN') ? $this->data->details->person->schoolregistration->Attribute('registration') : '-';
                        
                    $mform->addElement('html','<td class="cell c'.$colcount.'" style="text-align:left; vertical-align: middle;">'.$house.'</td>');
                    $colcount++;
                    $mform->addElement('html','<td class="cell c'.$colcount.'" style="text-align:left; vertical-align: middle;">'.$year.'</td>');
                    $colcount++;
                    $mform->addElement('html','<td class="cell c'.$colcount.'" style="text-align:left; vertical-align: middle;">'.$registration.'</td>');
                    $colcount++;
                } 

                $text = '';
                if($this->_customdata['matched'])
                {
                    foreach ($all_mdl_users as $user)
                    {
                        if(($user->idnumber == $sifuser->user_idnumber))
                        {
                            $text = fullname($user). ' ( '.$user->username.' )';
                            break;
                        }
                    }
                }
                else
                {
                    $nearmatch = false;
                    $matchedusers = array();
                    
                    $givenname = $this->data->details->person->name->Attribute('givenname');
                    $familyname = $this->data->details->person->name->Attribute('familyname');
                    
                    $preferredgivenname = $this->data->details->person->name->Attribute('preferredgivenname');
                    $preferredfamilyname = $this->data->details->person->name->Attribute('preferredfamilyname');
                    
                    $matchedusers = array_merge($matchedusers,$this->NameSearch($unmatched_mdl_users,"beginswith",$givenname,$familyname));
                    
                    
                    if((!empty($preferredgivenname) && !empty($preferredfamilyname)) && (($givenname <> $preferredgivenname) || ($familyname <> $preferredfamilyname)) )
                    {
                        $matchedusers = array_merge($matchedusers,$this->NameSearch($unmatched_mdl_users,"beginswith",$preferredgivenname,$preferredfamilyname));
                    }
                    
                    if(empty($matchedusers)) {
                        
                        $nearmatch = true;
                        
                        $matchedusers = array_merge($this->NameSearch($unmatched_mdl_users,"in",$givenname,$familyname));
                        $matchedusers = array_merge($matchedusers,$this->NameSearch($unmatched_mdl_users,"inwithinital",$givenname,$familyname));
                        
                        if(!empty($preferredgivenname) && !empty($preferredfamilyname))
                        {
                            $matchedusers = array_merge($matchedusers,$this->NameSearch($unmatched_mdl_users,"in",$preferredgivenname,$preferredfamilyname));
                            $matchedusers = array_merge($matchedusers,$this->NameSearch($unmatched_mdl_users,"inwithinital",$preferredgivenname,$preferredfamilyname));
                        }
                        
                        if(empty($matchedusers)) {
                            $nearmatch = false;
                        }
                        
                        $matchedusers = array_merge($matchedusers,$this->NameSearch($unmatched_mdl_users,"inwithtitle",$givenname,$familyname));
                        $matchedusers = array_merge($matchedusers,$this->NameSearch($unmatched_mdl_users,"inwithtitle",$preferredgivenname,$preferredfamilyname));
                        
                    }
                    
                    $options = array();
                    foreach( $matchedusers as $matcheduser)
                    {
                        $options[$matcheduser->id] = fullname($matcheduser) .' ( '.$matcheduser->username.' )';
                    }
                    
                    if(empty($matchedusers)){
                        $text= 'No Matches Available';
                    }
                    elseif(count($options) == 1 && $nearmatch == false)
                    {
                        $matcheduser = array_shift($matchedusers);
                        $text= 'This record will be synced with<br>'. fullname($matcheduser) .' ( '.$matcheduser->username.' )';
                        $matcheduser->idnumber = $sifuser->user_idnumber;
                        $DB->update_record('user',$matcheduser);
                    }
                    elseif(count($options) > 1 || $nearmatch == true)
                    {
                        $options = array();
                        $options[] = 'Choose...';
                        foreach( $matchedusers as $matcheduser)
                        {
                            $options[$matcheduser->id] = fullname($matcheduser) .' ( '.$matcheduser->username.' )';
                        }
                        $attributes = array( 'style' => 'width: 100%');
                        $mform->addElement('html','<td class="cell c'.$colcount.' lastcol" style="text-align:left; vertical-align: middle;">');
                        $mform->addElement('select', 'matchedusers['.$sifuser->user_idnumber.']', null, $options, $attributes);
                        $mform->addElement('html','</div></fieldset></td></tr>');
                        $flag = true;
                    }
                }
                if(!empty($text))
                    $mform->addElement('html','<td class="cell c'.$colcount.' lastcol" style="text-align:left;">'.$text.'</td></tr>');
                
                $count++;
                
                if($count % 50 === 0)
                {
                    $transaction->allow_commit();
                    $transaction = $DB->start_delegated_transaction(); 
                }
            }
        }
        if($count == 0)
        {
            $mform->addElement('html','<td class="cell c'.$colcount.'" style="text-align:left; vertical-align: middle;">'.get_string('nosyncrequired','enrol_zilink').'</td>');
            if($this->_customdata['userrole'] == 'student')
            {
                $colcount++; 
                $mform->addElement('html','<td class="cell c'.$colcount.'" style="text-align:left; vertical-align: middle;"></td>');
                $colcount++;
                $mform->addElement('html','<td class="cell c'.$colcount.'" style="text-align:left; vertical-align: middle;"></td>');
                $colcount++;
                $mform->addElement('html','<td class="cell c'.$colcount.'" style="text-align:left; vertical-align: middle;"></td>');
                $colcount++;
                $mform->addElement('html','<td class="cell c'.$colcount.'" style="text-align:left; vertical-align: middle;"></td>');
                $colcount++;
            }
            else {
                $colcount++; 
                $mform->addElement('html','<td class="cell c'.$colcount.'" style="text-align:left; vertical-align: middle;"></td>');
            }
        }
        $transaction->allow_commit();
        if($this->_customdata['matched'] == 0 && $flag)
        {
            $mform->addElement('html','<tr><td colspan="'.($colcount+1).'" style="text-align:center; border: 0; ">');
            $mform->addElement('html','<input name="submitbutton" value="Save changes" type="submit" id="id_submitbutton">');
            $mform->addElement('html','</td></tr>');
        }
        $mform->addElement('html','</tbody>
                                    </table>');
        
        if($flag)
            $mform->addElement('html','<div><fieldset>'); 
        
       $sifusers->close();
    }
    
    function Display()
    {
        return $this->_form->toHtml();
    }
    
    private function GetPersonName()
    {
        
        $firstname = $this->data->details->person->name->Attribute('givenname');
        $lastname = $this->data->details->person->name->Attribute('familyname');
                
        $preferredfirstname = $this->data->details->person->name->Attribute('preferredgivenname');
        $preferredlastname = $this->data->details->person->name->Attribute('preferredfamilyname');
                    
        if(empty($preferredfirstname) || empty($preferredlastname))
            return  $this->data->details->person->name->Attribute('givenname') . ' ' . $this->data->details->person->name->Attribute('familyname');
        else
        {
            if($this->data->details->person->name->Attribute('givenname') == $this->data->details->person->name->Attribute('preferredgivenname') && $this->data->details->person->name->Attribute('familyname') == $this->data->details->person->name->Attribute('preferredfamilyname')) 
                return $this->data->details->person->name->Attribute('givenname') . ' ' . $this->data->details->person->name->Attribute('familyname');
            else 
                return $this->data->details->person->name->Attribute('givenname') . ' ' . $this->data->details->person->name->Attribute('familyname') .' ( '.$this->data->details->person->name->Attribute('preferredgivenname') . ' ' . $this->data->details->person->name->Attribute('preferredfamilyname').' )';
        }
    }
    
    private function loadData($sifuser)
    {
        $fields = array('details','roles');
        if(!isset($this->data))
         $this->data = new stdClass();
        
        foreach($fields as $field)
        {
            if(!empty($sifuser->{$field}))
            {
                $xml = base64_decode($sifuser->{$field});
                $xml = str_replace('xmlns:ns2="http://zilinkplatform.net/moodle2/person"', '',$xml);
                $xml = str_replace('ns2:', '',$xml);
                $xml = str_replace('xmlns="http://zilinkplatform.net/roles"', '',$xml);
                $this->data->{$field} = new stdClass();
                try {
                $this->data->{$field} = @simplexml_load_string($xml,'simple_xml_extended');
                } catch (Exception $e) {
                    $this->data->{$field} = null;
                }
            }
            else 
                $this->data->{$field} = null;
        }
    }
    
    private function AllowProgress(&$users,$idnumber)
    {
        global $DB;
        
        foreach ($users as $id => $user)
        {
            if(($user->idnumber == $idnumber))
            {
                $this->matcheduser = $user;
                return $this->_customdata['matched'];
            }
        }
        return !$this->_customdata['matched'];
        
    }
    
    private function GetRoles()
    {
        $rolelist = array();
        if(!empty($this->data->roles))
        {
            foreach($this->data->roles->roles->role as $role)
            {
                    if($role->Attribute('value') == 'true')
                        $rolelist[] = $role->Attribute('type');
            }
            return $rolelist;
        }
        else
        {
            if(!empty($this->data->details))
            {
                $registration = $this->data->details->xpath('//schoolregistration');
                if(empty($registration))
                    return array('teacher');
                else
                    return array('student');
            }
            else 
                return array();
        }   
    }
    
    private function NameSearch($users,$type,$firstname,$lastname)
    {
        global $CFG;
        
        $matches = array();
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
    
}