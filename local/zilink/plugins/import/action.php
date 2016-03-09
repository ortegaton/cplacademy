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
 * Defines the settings for the ZiLink local
 *
 * @package     local_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot.'/local/zilink/plugins/core/data.php');
require_once($CFG->libdir.'/filelib.php');

$type = required_param('action', PARAM_TEXT);

$context = context_system::instance();
$PAGE->set_context($context);

if (isloggedin() && has_capability('moodle/site:config', $context) && confirm_sesskey()) {

    $output = array();

    if($type == 'updatelog')
    {
        
        $data = new ZiLinkData();
        $activationkey = $data->GetGlobalData('activationkey');
        
        if(!empty($activationkey)) 
        {
            
            $curl = new \curl(array('proxy' => true));
            $curl->setHeader('Authorization: Basic '.$activationkey);
            $response = $curl->get('https://api.zinetdatasolutions.com/api/v3/Instances/'.$activationkey.'/Logs');
            $curlerrno = $curl->get_errno();
            $error = false;
            
            $curlinfo= $curl->get_info();

            if ($curlinfo['http_code'] == '200') 
            {
            
                $table              = new html_table();
                $table->cellpadding = '10px';    
                $table->width       = '68%';
                $table->head        = array('Date','Level','Message');
                $table->align       = array('left', 'left','left');
                $table->border      = '2px'; 
                $table->tablealign  = 'center';
                
                $table->attributes['class'] = 'generaltable boxaligncenter zilink_table_width';
                
                $items = simplexml_load_string(preg_replace('/(<\?xml[^?]+?)utf-16/i', '$1utf-8',$response));
                
                $calls = array();
                foreach($items as $item)
                {
                    
                    $cells[] = userdate(strtotime((string)$item['DateTime']));
                    
                    switch ((string)$item['Level'])
                    {
                        case "40000": $cells[] = "Info"; break;
                        case "50000": $cells[] = "Notice";break;
                        case "50001": $cells[] = "Import Successful";break;
                        case "90000": $cells[] = "Error";break;
                        case "90001": $cells[] = "Import Failed";break;
                    }
                    $cells[] = (string)$item->Message;
                }
                
                $table->data = array_chunk($cells, 3);
                
                echo '<div id="consolelog">';
                echo html_writer::table($table);
                echo '<p><i>The log will update every 30 seconds<i></p>';
                echo '</div>';
            }
            else {
                echo '<p>'.$response.'</p>';
            }
        }  
        else 
        {
            echo $type;
        }      
    }
    else
    {
        header('HTTP/1.1 404 Not Found');
        echo 'Action not found';
    }

} else {
    header('HTTP/1.1 401 Not Authorised');
    echo 'User Not Logged In';
}