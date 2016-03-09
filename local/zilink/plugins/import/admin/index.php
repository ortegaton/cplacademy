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

require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/filelib.php');
require_once(dirname(__FILE__) . '/forms/buttons.php');
require_once(dirname(__FILE__) . '/forms/log.php');
require_once($CFG->dirroot.'/local/zilink/plugins/core/data.php');

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey());
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/import/admin/index.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($CFG->httpswwwroot.'/local/zilink/plugins/import/admin/index.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('import_page_title', 'local_zilink');

admin_externalpage_setup('zilink_import_settings',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/import/admin/index.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->set_pagelayout('report');

$jsdata = array(sesskey(),$CFG->httpswwwroot);

/*
$jsmodule = array(
                        'name'  =>  'local_zilink_cohort_view',
                        'fullpath'  =>  '/local/zilink/plugins/import/module.js',
                        'requires'  =>  array('base', 'node', 'io')
                    );

$PAGE->requires->js_init_call('M.local_zilink_import_view.init', $jsdata, false, $jsmodule);
*/
$output = '';
$data = new ZiLinkData();

$activationkey = $data->GetGlobalData('activationkey');

$form = new zilink_import_tasks_form(null, array('activationkey' => $activationkey));
$fromform = $form->get_data();

if (!empty($fromform) and confirm_sesskey()) {
    
    if(!empty($activationkey) && !empty($fromform->command)) {
        
        $curl = new \curl(array('proxy' => true));
        $curl->setHeader('Authorization: Basic '.$data->GetGlobalData('activationkey'));
        $response = $curl->get('https://api.zinetdatasolutions.com/api/v3/Instances/'.$data->GetGlobalData('activationkey').'/Run/'.$fromform->command);
        $curlerrno = $curl->get_errno();
        
        if (!empty($curlerrno)) 
        {
            if($curlerrno == '409') 
            {
                $output = 'Task current pending. Please try later.';
            } 
            else if($curlerrno == '405') 
            {
                $output = 'Task not allowed. Please contact support.';
            }
            echo $OUTPUT->error_text($output);
        } 
            
        $curlinfo= $curl->get_info();
        
        if ($curlinfo['http_code'] == 200) 
        {
             $output = 'Task accepted. Logs will update shortly';
        } else if ($curlinfo['http_code'] == 409)  {
            $output = 'Task current pending. Please try later.';
            echo $OUTPUT->error_text($output);
        } else if ($curlinfo['http_code'] == 405)  {
            $output = 'Task not allowed. Please contact support.';
            echo $OUTPUT->error_text($output);
        }
    } else {
        $output .= $form->display();
    }
}
else {
    $output .= $form->display();
}


if(!empty($activationkey)) {
    
    $jsdata = array($CFG->httpswwwroot,sesskey()); 
    
    $jsmodule = array(
                            'name'  =>  'local_zilink_cohort_view',
                            'fullpath'  =>  '/local/zilink/plugins/import/module.js',
                            'requires'  =>  array('base', 'node', 'io')
                        );
    
    $PAGE->requires->js_init_call('M.local_zilink_import.init', $jsdata, false, $jsmodule);
    
    
    $curl = new \curl(array('proxy' => true));
    $curl->setHeader('Authorization: Basic '.$activationkey);
    $response = $curl->get('http://api.zinetdatasolutions.com:443/api/v3/Instances/'.$activationkey.'/Logs');
    $curlerrno = $curl->get_errno();
    $error = false;

    $curlinfo= $curl->get_info();
    if ($curlinfo['http_code'] != 200) 
    {
         $error = true;
    }
    $form2 = new zilink_import_log_form(null,array('response' => $response, 'error' => $error));
    $output .= $form2->display();
        
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('import_view_title', 'local_zilink'));
echo $OUTPUT->box(get_string('import_view_title_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('import_support_desc', 'local_zilink').html_writer::link('https://schoolsict.zendesk.com/hc',get_string('support_site','local_zilink'),array('target'=> '_blank')));
echo $output;
echo $OUTPUT->footer();