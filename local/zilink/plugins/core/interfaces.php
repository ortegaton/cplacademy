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

interface iZiLinkPerson
{
    public function Security();
    public function GetPersonData($data_type,$idnumber);
    public function GetLinkedPeopleData($person_type,$data_type,$context);
    public function ProcessPersonData($data_types,$user_data);
    public function ProcessPersonRoles($user_data);
    
}

interface iZiLinkData
{
    public function Security();
    public function GetLinkedPeopleData($person_type,$data_type,$context);
    public function ValidateDetails($details);
    
}

interface iZiLinkPanel
{
    public function SetTitle($title);
    public function SetContent($contents);
    public function Display();
    public function SetWidth($width);
    public function SetCSS($css);
    
}