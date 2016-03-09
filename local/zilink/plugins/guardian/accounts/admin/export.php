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

require_once(dirname(__FILE__) . '/../../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot.'/local/zilink/lib.php');

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey());
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/bookings/rooms/admin/export.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($CFG->httpswwwroot.'/local/zilink/plugins/bookings/rooms/admin/export.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('guardian_accounts_page_title', 'local_zilink');

admin_externalpage_setup('zilink_guardian_accounts_export_settings',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/guardian/accounts/admin/export.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->set_pagelayout('admin');
    

//OUTPUT
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('guardian_accounts', 'local_zilink'));
echo $OUTPUT->box(get_string('guardian_accounts_title_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('guardian_accounts_support_desc', 'local_zilink').html_writer::link('http://support.zilink.co.uk/hc/',get_string('support_site','local_zilink'),array('target'=> '_blank')));
echo $OUTPUT->spacer(array('height' => '50px'));
echo $OUTPUT->single_button($CFG->httpswwwroot.'/local/zilink/plugins/guardian/accounts/admin/file.php','Download Mail Merge Document');
echo $OUTPUT->footer();