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

require_once(dirname(dirname(__FILE__)).'/core/data.php');
require_once(dirname(dirname(__FILE__)).'/core/security.php');

class ZiLinkPerson implements iZiLinkPerson
{
    private $user;
    private $data;
    private $security;
    private $DataHandler;
    
    function __construct($courseid = null, $instanceid = null){
    
        global $USER;
        $this->user = $USER;
        $this->DataHandler = new ZiLinkData();
        $this->data = new stdClass();
    }

    public function Security()
    {
        return $this->DataHandler->Security();
    }
    
    public function GetPersonalData($data_type = 'none',$required = false)
    {
        global $USER;
        
        return $this->DataHandler->GetPersonData($data_type,$USER->idnumber,$required);
    }
    
    public function GetPersonData($data_type,$idnumber,$required = false)
    {
        
        $rolelist = array();
        
        return $this->DataHandler->GetPersonData($data_type,$idnumber,$required);
        /*
        if(!empty($this->data->myself->roles))
        {
            foreach($this->data->myself->roles->roles->role as $role)
            {
                    if($role->Attribute('value') == 'true')
                        $rolelist[] = $role->Attribute('type');
            }
            return $rolelist;
        }
        else
        {
            if(!empty($this->data->myself->details))
            {
                $registration = $this->data->myself->details->xpath('//schoolregistration');
                if(empty($registration))
                    return array('teacher');
                else
                    return array('student');
            }
            else 
                return array();
        }
         */
    }
    
    public function GetLinkedPeopleData($person_type,$data_type = 'none',$context = null)
    {
        return $this->DataHandler->GetLinkedPeopleData($person_type, $data_type, $context);
    }
    
    public function ProcessPersonData($data_types,$user_data)
    {
        $data = new stdClass();
        foreach($data_types as $field)
        {
            if(!empty($user_data->{$field}))
            {
                $xml = base64_decode($user_data->{$field});
                $xml = str_replace('xmlns:ns2="http://zilinkplatform.net/moodle2/person"', '',$xml);
                $xml = str_replace('ns2:', '',$xml);
                $xml = str_replace('xmlns="http://zilinkplatform.net/roles"', '',$xml);
            
                $data->{$field} = new stdClass();
                $data->{$field} = simplexml_load_string($xml,'zilink_local_simple_xml_extended');
            }
            else 
                $data->{$field} = null;
        }
        
        return $data;
    }

    public function ProcessPersonRoles($user_data)
    {
        $rolelist = array();
        if(!empty($user_data->roles))
        {
            foreach($user_data->roles->roles->role as $role)
            {
                    if($role->Attribute('value') == 'true')
                        $rolelist[] = $role->Attribute('type');
            }
            return $rolelist;
        }
        else
        {
            if(!empty($user_data->details))
            {
                $registration = $user_data->details->xpath('//schoolregistration');
                if(empty($registration))
                    return array('teacher');
                else
                    return array('student');
            }
            else 
                return array();
        }
    }
}

if(!class_exists("zilink_local_simple_xml_extended"))
{
    class zilink_local_simple_xml_extended extends SimpleXMLElement
    {
        public    function    Attribute($name)
        {
            foreach($this->Attributes() as $key => $val)
            {
                if($key == $name)
                    return (string)$val;
            }
            return '';
        }
    }
}