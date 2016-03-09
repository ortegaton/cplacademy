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

    $table = new xmldb_table('zilink_bookings_rooms');
    if(!$dbman->table_exists($table))
    {
        echo '<div class="notifysuccess">Installing ZiLink Core Plugin ('.$directory.') Database Tables'; 
        $dbman->install_from_xmldb_file($path.'/'.$directory.'/db/install.xml');
    }
    
    //version ot the version of the global plugin
    $result = false;
    
    if($oldversion < 2013091906)
    {
        echo '<div class="notifysuccess">Starting to Update Database Tables to version 2013091900'; 
        
        $table = new xmldb_table('zilink_bookings_rooms');
        
        $field = new xmldb_field('subject');
        $field->set_attributes(XMLDB_TYPE_CHAR, '100');
        $dbman->change_field_type($table, $field);
        
        $field = new xmldb_field('classcode');
        $field->set_attributes(XMLDB_TYPE_CHAR, '100');
        $dbman->change_field_type($table, $field);
        
        $field = new xmldb_field('room');
        $field->set_attributes(XMLDB_TYPE_CHAR, '100');
        $dbman->change_field_type($table, $field);
        
        //plugin version number
        echo '<div class="notifysuccess">Finished Updating Database Tables to version 2013091900'; 
        $result = true;
    }

    if(!$result)
    {
        echo '<br>Plugin does not required any database updates';
    }
    

?>