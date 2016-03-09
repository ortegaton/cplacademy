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
 * @package     block_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once( dirname(__FILE__).'/security.php');
require_once( dirname(__FILE__).'/interfaces.php');

class ZiLinkData implements iZiLinkData {

    
    function __construct($courseid = null, $instanceid = null){
    
        global $DB;
        
        $this->security = new ZiLinkSecurity();
        $this->personalData = null;
        $this->globalData = new stdClass();
    }
    
    public function Security()
    {
        return $this->security;
    }
    
    public function GetPersonData($data_type = 'none', $idnumber,$required = false)
    {

        global $CFG,$DB,$USER;
       
        $data = new stdClass();
        
        if(!is_array($data_type))
        {
            if ($data_type == 'none')
            {
                return null;
            }
            
            $data_type = array($data_type);
        }
        
        foreach($data_type as $type)
        {
            $data->{$type} = null;
        }

        
        if($this->personalData == null)
        {
            
            $cache = cache::make('local_zilink', 'alluserdata');
            $cacheddata = $cache->get('all');

            if (empty($cacheddata)) {
                
                $cacheddata = $DB->get_records('zilink_user_data');
                $cache->set('all', $cacheddata);
            }
            $this->personalData = $cacheddata;
        }

        if(count($this->personalData) > 0)
        {
            $matched = false;
            foreach($this->personalData as $record)
            {
                if($record->user_idnumber <> $idnumber)
                {
                    continue;
                }
                
                $matched = true;
                
                foreach($record as $field => $record_data)
                {
                   
                    if(in_array($field,$data_type) || in_array('all',$data_type)) 
                    {
                        
                        if($field == 'id')
                            continue;
                        if($field == 'user_idnumber' || $record_data == null)
                        {
                            $data->{$field} = new stdClass();
                            $data->{$field} = $record_data;
                        }
                        else 
                        {
                            //    $data = new stdClass();
                            $xml = str_replace('xmlns="http://zilinkplatform.net/timetable"', '',base64_decode($record_data));
                            $xml = str_replace('xmlns="http://zilinkplatform.net/assessment/gradesets"', '',$xml);
                            $xml = str_replace('xmlns="http://zilinkplatform.net/pictures"', '',$xml);
                            $xml = str_replace('xmlns="http://zilinkplatform.net/assessment/history"', '',$xml);
                            $xml = str_replace('xmlns:ns2="http://zilinkplatform.net/moodle2/person"', '',$xml);
                            $xml = str_replace('ns2:', '',$xml);
                            $data->{$field} = simplexml_load_string($xml,'simple_xml_extended');
                             
                            if(count($data->{$field}->count()) == 0)
                            {
                                $tmp = null;
                            }
                            
                            if($field == 'timetable' )
                            {
                                
                                try{
                                    if(isset($data->{$field}) || $data->{$field} <> null )
                                    {
                                        $tmp3 = $data->{$field}->timetable[(int)$CFG->zilink_timetable_offset];
                                        $data->{$field} =  @simplexml_load_string($tmp3->asXML(),'simple_xml_extended');
                                    }
                                    else 
                                    {
                                        $data->{$field} = null;
                                    }
                                } catch (Eception $e){
                                    
                                    $data->{$field} = null;
                                }
                                
                            } 
                            else if ($field == 'extended_details' )
                            {
                                if(isset($data->extended_details->LearnerPersonal))
                                    $data->extended_details = $data->extended_details->LearnerPersonal;
                                
                                if(isset($data->extended_details->WorkForcePersonal))
                                    $data->extended_details =  $data->extended_details->WorkForcePersonal;
                                
                                if(isset($data->extended_details->ContactPersonal))
                                    $data->extended_details =  $data->extended_details->ContactPersonal;
                            }
                        }
    
                        if($required && $data->{$field} == null)
                        {
                            throw new Exception("Missing Required Data");
                        }
                    }   
                }
            }
            if($required && $matched == false)
            {
                throw new Exception("Missing Required Data");
            }
        }
        else
        {
            if($required)
            {
                throw new Exception("Missing Required Data");
            }
        }   
        if(!empty($idnumber)) {
            $data->user = $DB->get_record('user',array('idnumber' => $idnumber));
        }
        return $data;
    }

    public function GetGlobalData($data_type = 'none',$required = false)
    {
        global $CFG,$DB,$USER;
        
        $data = null;
        $all = false;
        /*
        if(!is_array($data_type))
        {
            if ($data_type == 'none')
            {
                return null;
            }
            
            $data_type = array($data_type);
        }
        */
        
        if(strstr($data_type, '-all'))
        {
            $data_type = str_replace('-all','',$data_type);
            $all = true;
        }
        
        //$cache = cache::make('local_zilink', 'global');
        //$cache->delete(0);
        
        if(isset($this->globalData->{$data_type}) && is_object($this->globalData->{$data_type}))
        {
            return $this->globalData->{$data_type};
        }
        
        $cache = cache::make('local_zilink', 'global');
        $cacheddata = $cache->get($data_type);
        $cacheddata = null;
        if (empty($cacheddata)) {
            
            $sql = "select * from {zilink_global_data} where " . $DB->sql_compare_text('setting') . " = '".$data_type."'";
            if($record = $DB->get_record_sql($sql,null)) {
                $cacheddata = $record->value;
                $cache->set($data_type, $cacheddata);
            } 
        }
            
            if(!empty($cacheddata))
            {
                $xml = str_replace('xmlns="http://zilinkplatform.net/timetable"', '',$cacheddata);
                $xml = str_replace('xmlns="http://zilinkplatform.net/assessment/gradesets"', '',$xml);
                $xml = str_replace('xmlns="http://zilinkplatform.net/assessment/history"', '',$xml);
                
                if($data_type == 'activationkey')
                {
                    $this->globalData->{$data_type} = $xml;
                    $data = $xml;
                }
                else if($data_type == 'timetable' ) 
                {
                    
                    $tmp = new stdClass();
                    $tmp = simplexml_load_string($xml,'simple_xml_extended');
                    
                    if($tmp <> null && $tmp->count() == 0)
                    {
                        $tmp = null;
                    }             
                    
                    if($all)
                    {
                        $data = $tmp;
                        
                    } else {
                        try{
                            if(($tmp <> null || isset($tmp->timetable)) && (int)$CFG->zilink_timetable_offset < $tmp->count())
                            {
                                $tmp3 = $tmp->timetable[(int)$CFG->zilink_timetable_offset];                
                                $data =  @simplexml_load_string($tmp3->asXML(),'simple_xml_extended');
                                $this->globalData->{$data_type} = $data;
                            }
                            else
                            {
                                $data = null;
                            }
    
                        } catch (Exception $e){
                            $data = null;
                        }
                    }
                }
                else {
                    $data = @simplexml_load_string($xml,'simple_xml_extended');
                    $this->globalData->{$data_type} = $data;
                }
                
                if(is_object($data) && $data->count() == 0)
                {
                    $tmp = null;
                }
                
            }   
        if($data == null && $required)
        {
            throw new Exception("Missing Global Timetable Data", 1);
        }
            //$cache->set($data_type, json_encode($data));
        
        return $data;
    }
    
    public function GetLinkedPeopleData($person_type,$data_types,$context = null)
    {
        global $DB;
        
        if(!is_array($data_types) && $data_types <> null)
        {
            $data_types = array($data_types);
        }
        
        $people = $this->Security()->GetLinkedPeople($person_type,$context);
        $data = array($person_type => null);
        
        foreach ($people as $person)
        {
            
            if($data_types == null)
            {
                $data[$person_type][$person->idnumber] = null;
            }
            else {
                foreach($data_types as $data_type)
                {
                    if(!isset($data[$person_type][$person->idnumber]))
                    {
                        $data[$person_type][$person->idnumber] = $this->GetPersonData($data_type,$person->idnumber);
                        $data[$person_type][$person->idnumber]->user = $DB->get_record('user', array('idnumber' => $person->idnumber));
                    }
                    else 
                    {
                        $data[$person_type][$person->idnumber]->{$data_type} = $this->GetPersonData($data_type,$person->idnumber)->{$data_type};
                    }
                }
            }
        }
        
        return $data;
    }
    
    public function GetFilterData()
    {
        global $DB;
        
        $sql = '   SELECT  zud.id, zud.user_idnumber, zud.details, zud.guardians '.
               '    FROM    {zilink_user_data} zud,  '.
               '            {user} u '.
               '    WHERE   zud.user_idnumber = u.idnumber '.
               '    AND     zud.guardians IS NOT NULL '.
               '    ORDER BY u.lastname ';

        $records = $DB->get_recordset_sql($sql, null);
        
        $filters = array();
        $filters['year'] = array();
        $filters['house'] = array();
        $filters['registration'] = array();
        
        
        
        if($records->valid()) {
            $xml = '';
            foreach($records as $record)
            {
                $xml .= $this->CleanXml(base64_decode($record->details));
            }
            $xml = '<?xml version="1.0"?><content>'.$xml.'</content>';

            try {
                $xml = @simplexml_load_string($xml,'simple_xml_extended');
                $schoolrecords = $xml->xpath('//schoolregistration');
            } catch (Exception $ex) {
                return $filters;
            }
            
            foreach ($schoolrecords as $record) {
                if (!in_array($record->Attribute('year'), $filters['year']) && $record->Attribute('year') <> 'UNKNOWN') {
                    $filters['year'][(string)$record->Attribute('year')] = (string)$record->Attribute('year');
                }
                if (!in_array($record->Attribute('house'), $filters['house']) && $record->Attribute('house') <> 'UNKNOWN') {
                    $filters['house'][(string)$record->Attribute('house')] = (string)$record->Attribute('house');
                }
                if (!in_array($record->Attribute('registration'), $filters['registration']) && $record->Attribute('registration') <> 'UNKNOWN') {
                    $filters['registration'][(string)$record->Attribute('registration')] = (string)$record->Attribute('registration');
                }
            }
        }
        natsort($filters['registration']);
        natsort($filters['house']);
        natsort($filters['year']);
        
        $filters['year'] = $this->array_unshift_assoc($filters['year'], 'all' , get_string('all'));
        $filters['house'] = $this->array_unshift_assoc($filters['house'], 'all' , get_string('all'));
        $filters['registration'] = $this->array_unshift_assoc($filters['registration'], 'all' , get_string('all'));
        
        return $filters;
    }
    
    public function GetMatchingData($selected_filters)
    {
        global $DB;
        
        $filters = array();
        $unmatched = array();
        
        if ($selected_filters['year'] <> 'all') {
            $filters['year'] = $selected_filters['year'];
        }
        if ($selected_filters['house'] <> 'all') {
            $filters['house'] = $selected_filters['house'];
        }
        if ($selected_filters['registration'] <> 'all') {
            $filters['registration'] = $selected_filters['registration'];
        }
    
        $string = '';
        $count = 0;
        foreach ($filters as $item => $value) {
            if (!empty($string) && $count < (count($filters))) {
                $string .= ' and ';
            }
    
            $string .= '@' . $item . '=\'' . $value . '\'';
            $count++;
        }
        if (!empty($string)) {
            $string = ' [' . $string . ']';
        }
    
        $xpath = '/*//*[*' . $string . ']';
        
        $sql = '   SELECT  zud.id, zud.user_idnumber, zud.details, zud.guardians '.
               '    FROM    {zilink_user_data} zud,  '.
               '            {user} u '.
               '    WHERE   zud.user_idnumber = u.idnumber '.
               '    AND     zud.guardians IS NOT NULL '.
               '    ORDER BY u.lastname ';

        $records = $DB->get_recordset_sql($sql, null);
        
        if($records->valid()) {
            $xml = '';
            foreach($records as $record)
            {
                $xml .= $this->CleanXml(base64_decode($record->details));
            }
            $xml = '<?xml version="1.0"?><content>'.$xml.'</content>';

            try {
                $xml = @simplexml_load_string($xml,'simple_xml_extended');
                $schoolrecords = $xml->xpath($xpath);
            } catch (Exception $ex) {
                return $filters;
            }
            
            $list = array();
            foreach ($schoolrecords as $record) {
                $list[] = (string)$record->id;
            }
            
            $records = $DB->get_recordset_sql($sql, null);
            foreach($records as $record)
            {
                if(in_array($record->user_idnumber,$list))
                {
                    $xml = $this->CleanXml(base64_decode($record->guardians));
                    $xml = '<?xml version="1.0"?>'.$xml;
                    
                    $student = new stdClass();
                    $student->idnumber = $record->user_idnumber;
                    $student->guardians = array();
                    
                    $guardians = @simplexml_load_string($xml,'simple_xml_extended');
                    
                    if(empty($guardians))
                    {
                        continue;
                    }
                    
                    foreach($guardians->guardians->guardian as $guardian )
                    {
                        
                            
                            $g = new stdClass();
                            $g->idnumber = $guardian->Attribute('refid');
    
                            $g->linked = $this->security->Connected($student->idnumber, $g->idnumber);
                            $g->relationship = $guardian->Attribute('relationship');
                            
                            switch ($guardian->Attribute('relationship'))
                            {
                                case "FAFN":
                                    //Father
                                    $g->relationship = "Father";
                                    break;
                                case "FAFF":
                                    //Foster Father
                                    $g->relationship = "Foster Father";
                                    break;
                                case "FAFS":
                                    //Step Father
                                    $g->relationship = "Step Father";
                                    break;
                                case "FAMN":
                                    //Mother
                                    $g->relationship = "Mother";
                                    break;
                                case "FAMF":
                                    //Foster Mother
                                    $g->relationship = "Foster Mother";
                                    break;
                                case "FAMS":
                                    //Step Mother
                                    $g->relationship = "Step Mother";
                                    break;
                                case "OREL":
                                    $g->relationship = "Grand Parent";
                                    break;
                                case "CHMR":
                                    //Childminder
                                    $g->relationship = "Childminder";
                                    break;
                                case "CARE":
                                    //Carer
                                    $g->relationship = "Carer";
                                    break;
                                case "SWKR":
                                    //Social Worker
                                    $g->relationship = "Social Worker";
                                    break;
                                case "RELG":
                                    //Religious Leader
                                    $g->relationship = "Religious Leader";
                                    break;
                                case "HTCR":
                                    //Head teacher
                                    $g->relationship = "Head Teacher";
                                    break;
                                case "OTHR":
                                    $g->relationship = "Othert";
                                    break;
                                case "DOCT":
                                    $g->relationship = "Doctor";
                                    break;
                                default:
                                    $g->relationship = "Other";
                                    break;
                            }
                            
                            $g->priority = $guardian->Attribute('priority');
                            
                            $rec = $DB->get_record('zilink_user_data', array('user_idnumber' => $g->idnumber));
                            
                            if($rec) {
                                
                                $xml = $this->CleanXml(base64_decode($rec->extended_details));
                                $xml = '<?xml version="1.0"?>'.$xml;
                                    
                                $details = @simplexml_load_string($xml,'simple_xml_extended');
                                
                                if($this->ValidateDetails($details)) {
                                    
                                    $xml = $this->CleanXml(base64_decode($rec->details));
                                    $xml = '<?xml version="1.0"?>'.$xml;
                                    
                                    $details = @simplexml_load_string($xml,'simple_xml_extended');
                                    
                                    if(!is_object($details)) {
                                        continue;
                                    }
                                    
                                    $name = $details->xpath('//name');
                                    
                                    $name = $name[0];
        
                                    $firstname = $name->Attribute('givenname');
                                    $lastname = $name->Attribute('familyname');
                    
                                    $preferredfirstname = $name->Attribute('preferredgivenname');
                                    $preferredlastname = $name->Attribute('preferredfamilyname');
                    
                                    if (empty($preferredfirstname) || empty($preferredlastname)) {
                                        $fullname = $name->Attribute('givenname') . ' ' . $name->Attribute('familyname');
                                    } else {
                                        if ($name->Attribute('givenname') == $name->Attribute('preferredgivenname') &&
                                            $name->Attribute('familyname') == $name->Attribute('preferredfamilyname')) {
                    
                                            $fullname = $name->Attribute('givenname') . ' ' . $name->Attribute('familyname');
                                        } else {
                                            $fullname = $name->Attribute('givenname') . ' ' .
                                                        $name->Attribute('familyname') .
                                                        ' ( ' . $name->Attribute('preferredgivenname') . ' ' .
                                                        $name->Attribute('preferredfamilyname') . ' )';
                                        }
                                    }
                                       
                                    $g->fullname = $fullname;
                                    
                                    $student->guardians[] = $g;
                                }
                            } else {
                                continue;
                            }
                        
                        
                    }
        
                    $student->user = $DB->get_record('user', array('idnumber' => $record->user_idnumber));
                    
                    if(!empty($student->guardians)) {
                        $unmatched[] = $student;
                    }
                }
            }
        }
        
        return $unmatched;
        
    }
    
    private function CleanXml($xml)
    {
        $xml = str_replace('xmlns:ns2="http://zilinkplatform.net/moodle2/person"', '', $xml);
        $xml = str_replace('xmlns="http://zilinkplatform.net/guardian"', '', $xml);
        $xml = str_replace('ns2:', '', $xml);
        $xml = str_replace('<?xml version="1.0"?>', '', $xml);
        
        return $xml;
    }
    
    public function ValidateDetails($details) {
        
        global $CFG;
        
        $record = array();
        
        $details = $this->GetDetails($details);
        
        if (!isset($details->PersonalInformation->Name->GivenName) && !isset($details->PersonalInformation->Name->FamilyName)) {
            return false;
        }
        if (!isset($CFG->zilink_guardian_accounts_email_required)) {
            return false;
        }
        
        if ($CFG->zilink_guardian_accounts_email_required && !isset($details->PersonalInformation->Email)) {
            return false;
        }
        $record['name'] = $details->PersonalInformation->Name;
        $record['email'] = (string)$details->PersonalInformation->Email;
        return $record;
    }
    
    private function GetDetails($details) {
        if (isset($details->ContactPersonal)) {
            return $details->ContactPersonal;
        } else if (isset($details->LearnerPersonal)) {
            return $details->LearnerPersonal;
        } else if (isset($details->WorkforcePersonal)) {
            return $details->WorkforcePersonal;
        }
    }
    
    private function array_unshift_assoc(&$arr, $key, $val) 
    { 
        $arr = array_reverse($arr, true); 
        $arr[$key] = $val; 
        return array_reverse($arr, true); 
    }
}

class simple_xml_extended extends SimpleXMLElement
{
    public    function    Attribute($name)
    {
        //if(count($this) == 0) {
        //    return (String)'';
        //}
        
                
        foreach($this->Attributes() as $key=>$val)
        {
            if($key == $name)
                return (string)$val;
        }
    }
}