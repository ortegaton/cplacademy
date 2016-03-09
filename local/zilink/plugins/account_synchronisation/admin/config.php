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

require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');

@set_time_limit(0);
raise_memory_limit(MEMORY_HUGE);

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey());
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/account_synchronisation/admin/config.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($CFG->httpswwwroot.'/local/zilink/plugins/account_synchronisation/admin/config.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('account_synchronisation_page_title', 'local_zilink');

admin_externalpage_setup('zilink_account_synchronisation_settings_config',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/account_synchronisation/admin/config.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->requires->css('/enrol/zilink/styles.css');

//$PAGE->navbar->add(get_string('administrationsite'));
//$PAGE->navbar->add(get_string('plugins','admin'));
//$PAGE->navbar->add(get_string('enrolments','enrol'));
//$PAGE->navbar->add(get_string('zilinkaccountsync', 'enrol_zilink'), $url);
$PAGE->set_pagelayout('report');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('account_synchronisation', 'local_zilink'). ' - '.get_string('account_synchronisation_config', 'local_zilink'));
echo $OUTPUT->box(get_string('account_synchronisation_config_desc', 'local_zilink'));

$config = new local_zilink_account_synchronisation();
echo $config->Configuration();
echo $OUTPUT->footer();

