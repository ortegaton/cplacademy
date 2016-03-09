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
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/icon_navigation/admin/index.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($CFG->httpswwwroot.'/local/zilink/plugins/icon_navigation/admin/index.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('icon_navigation_page_title', 'local_zilink');

admin_externalpage_setup('zilink_icon_navigation_settings',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/icon_navigation/admin/index.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->set_pagelayout('report');

$params = array('icon_navigation_iconset' => $CFG->zilink_icon_navigation_iconset,
                'icon_navigation_size' => $CFG->zilink_icon_navigation_size);
                
foreach($CFG as $item => $value)
{
    if(substr($item,0,37) == 'zilink_icon_navigation_category_icon_')
    {
        $params[substr($item,7)] = $value;
    }
}

$form = new zilink_icon_navigation_config_form(null, $params);              
$fromform = $form->get_data(true);


if (!empty($fromform) and confirm_sesskey()) {

    if(isset($fromform->icon_navigation_iconset))
    {
        $CFG->zilink_icon_navigation_iconset = $fromform->icon_navigation_iconset;
        set_config('zilink_icon_navigation_iconset',$fromform->icon_navigation_iconset);
    }

    if(isset($fromform->icon_navigation_size))
    {
        $CFG->zilink_icon_navigation_size = $fromform->icon_navigation_size;
        set_config('zilink_icon_navigation_size',$fromform->icon_navigation_size);
    }

    foreach($fromform as $item => $value)
    {
        if(substr($item,0,30) == 'icon_navigation_category_icon_')
        {
            $CFG->{'zilink_'.$item} = $value;
            set_config('zilink_'.$item,$value);
        }
    }
    
    $form->set_data($fromform);
}

//OUTPUT
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('icon_navigation', 'local_zilink'));
echo $OUTPUT->box(get_string('icon_navigation_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('icon_navigation_support_desc', 'local_zilink').html_writer::link('https://schoolsict.zendesk.com/entries/66178868',get_string('support_site','local_zilink'),array('target'=> '_blank')));
echo $form->display();
echo $OUTPUT->footer();