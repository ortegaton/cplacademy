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
 * Deletes a parents' evening
 *
 * Much like {@see cancel.php}, this displays a confirmation form which, once submitted,
 * will delete and entire parents' evening and associated appointments.
 *
 * @package block_parenteseve
 * @author Mark Johnson <johnsom@tauntons.ac.uk>
 * @copyright Copyright &copy; 2009, Taunton's College, Southampton, UK
 * @param int id The ID of the parents' evening record to delete
 * @param int confirm Whether the deletion has been confirmed
 */

require_once(dirname(__FILE__) . '/../../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/renderer.php');
require_once(dirname(__FILE__) .'/forms/edit.php');

require_login();
$session = optional_param('session',0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
//$add = optional_param('add', '', PARAM_TEXT);
//$remove = optional_param('remove', '', PARAM_TEXT);

$sesskey = required_param('sesskey',PARAM_RAW);
confirm_sesskey($sesskey);

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey(),'session' => $session);
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/guardian/scheduler/page/delete.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($url, $urlparams);
$PAGE->verify_https_required();

$strmanage = get_string('guardian_scheduler_page_title', 'local_zilink');
$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);
$PAGE->set_pagelayout('base');

$security = new ZiLinkSecurity();

$PAGE->requires->css('/local/zilink/plugins/guardian/scheduler/styles.css');
$PAGE->navbar->add(get_string('pluginname_short','local_zilink'));
$PAGE->navbar->add(get_string('guardian_scheduler', 'local_zilink'));

if ($security->IsAllowed('local/zilink:guardian_scheduler_manage')) {
    $PAGE->navbar->add(get_string('guardian_scheduler_session', 'local_zilink'), new moodle_url('/local/zilink/plugins/guardian/scheduler/admin/manage.php'));
} else {
    $PAGE->navbar->add(get_string('guardian_scheduler_session', 'local_zilink'));
}

$session = $DB->get_record('zilink_guardian_sched', array('id' => $session));
if (!$session) {
    $PAGE->navbar->add(get_string('create'), new moodle_url('/local/zilink/plugins/guardian/scheduler/admin/manage.php'));
} else {
    $PAGE->navbar->add(get_string('edit'));
    $PAGE->navbar->add(date('l jS M Y', $session->timestart));
}

if ($confirm) {
    $DB->delete_records('zilink_guardian_sched_app', array('sessionid' => $session->id));
    $DB->delete_records('zilink_guardian_sched_tch', array('sessionid' => $session->id));
    $DB->delete_records('zilink_guardian_sched', array('id' => $session->id));
    redirect($CFG->wwwroot.'/local/zilink/plugins/guardian/scheduler/admin/manage.php');
}
    
$url_no = new moodle_url('/local/zilink/plugins/guardian/scheduler/admin/manage.php');

$url_yes = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/delete.php');
$url_yes->params(array('confirm' => '1', 'session' => $session->id));

$confrim = new single_button($url_yes, get_string('yes'), 'post');
$cancel = new single_button($url_no, get_string('no'), 'post');

$a = new stdClass;
$a->date = date('d/M/Y', $session->timestart);
$a->time = date('H:i', $session->timestart);

echo $OUTPUT->header();
echo $OUTPUT->confirm(get_string('guardian_scheduler_delete', 'local_zilink',$a), $confrim, $cancel);
echo $OUTPUT->footer();