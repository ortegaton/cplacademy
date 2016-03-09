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
require_once($CFG->dirroot.'/local/zilink/plugins/core/data.php');
require_once(dirname(__FILE__) .'/forms/sessions.php');

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey());
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/data_manager/admin/sessions.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($CFG->httpswwwroot.'/local/zilink/plugins/data_manager/admin/sessions.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('data_manager_page_title', 'local_zilink');

admin_externalpage_setup('zilink_data_manager_sessions_settings',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/data_manager/admin/sessions.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->set_pagelayout('report');

$jsdata = array(sesskey());
        
$jsmodule = array(
                'name'  =>  'local_zilink_data_manager_sessions',
                'fullpath'  =>  '/local/zilink/plugins/data_manager/module.js',
                'requires'  =>  array('base', 'node', 'io')
            );

$PAGE->requires->js_init_call('M.local_zilink_data_manager_sessions.init', $jsdata, false, $jsmodule);

$form = new zilink_data_manager_sessions_settings_form(null,array('sessions_allowed' => $CFG->zilink_data_manager_sessions_allowed,
                                                                  'sessions_order' => $CFG->zilink_data_manager_sessions_order));
$fromform = $form->get_data();

if (!empty($fromform) and confirm_sesskey()) {


    if(isset($fromform->data_manager_sessions_allowed))
    {
        $CFG->zilink_data_manager_sessions_allowed = implode(",",$fromform->data_manager_sessions_allowed);
        set_config('zilink_data_manager_sessions_allowed',implode(",",$fromform->data_manager_sessions_allowed));
    } 
    
    if(isset($fromform->sessions_order))
    {
        $CFG->zilink_data_manager_sessions_allowed = $fromform->sessions_order;
        set_config('zilink_data_manager_sessions_order',$fromform->sessions_order);
    } 
    
    //$form->set_data($fromform); 
} else {
    //$data = new stdClass();
    //$data->sessions_order = $CFG->zilink_data_manager_sessions_allowed;
    //$form->set_data($data); 
}

//OUTPUT
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('data_manager_sessions_title', 'local_zilink'));
echo $OUTPUT->box(get_string('data_manager_sessions_title_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('data_manager_support_desc', 'local_zilink').html_writer::link('https://schoolsict.zendesk.com/entries/66162107',get_string('support_site','local_zilink'),array('target'=> '_blank')));
echo $form->display();
echo $OUTPUT->footer();

