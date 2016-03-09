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
require_once(dirname(__FILE__) .'/forms/config.php');

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey());
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/zimbra/admin/index.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($CFG->httpswwwroot.'/local/zilink/plugins/zimbra/admin/index.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('zimbra', 'local_zilink');

admin_externalpage_setup('zilink_zimbra_settings',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/zimbra/admin/index.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->set_pagelayout('report');

$form = new zilink_zimbra_config_form();
              
$fromform = $form->get_data(true);

$toform = new stdClass();

$toform->zimbra_link = $CFG->zilink_zimbra_link;
$toform->zimbra_preauth_key = $CFG->zilink_zimbra_preauth_key;
$toform->zimbra_url = $CFG->zilink_zimbra_url;

$form->set_data($toform);

if (!empty($fromform) and confirm_sesskey()) {

    if(isset($fromform->zimbra_link))
    {
        $CFG->zilink_zimbra_link = $fromform->zimbra_link;
        set_config('zilink_zimbra_link',$fromform->zimbra_link);
    }

    if(isset($fromform->zimbra_preauth_key))
    {
        $CFG->zilink_zimbra_preauth_key = $fromform->zimbra_preauth_key;
        set_config('zilink_zimbra_preauth_key',$fromform->zimbra_preauth_key);
    }
    if(isset($fromform->zimbra_url))
    {
        $CFG->zilink_zimbra_url = $fromform->zimbra_url;
        set_config('zilink_zimbra_url',$fromform->zimbra_url);
    }
    
    $form->set_data($fromform);
}

//OUTPUT
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('zimbra_settings', 'local_zilink'));
echo $OUTPUT->box(get_string('plugin_zimbra_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('zimbra_support_desc', 'local_zilink').html_writer::link('https://schoolsict.zendesk.com/entries/66701416',get_string('support_site','local_zilink'),array('target'=> '_blank')));
echo $form->display();
echo $OUTPUT->footer();