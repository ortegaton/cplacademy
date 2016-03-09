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
 * Manage parents' schedulers
 *
 * Displays a list of parents' schedulers with dates and time,
 * along with links to edit, delete, and create them.
 *
 * @package local_zilink
 * @author Mark Johnson <johnsom@tauntons.ac.uk>
 * @copyright Copyright &copy; 2009, Taunton's College, Southampton, UK
 */

require_once(dirname(__FILE__) . '/../../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');


$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey());
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/guardian/scheduler/admin/manage.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($url, $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('guardian_scheduler_page_title', 'local_zilink');

admin_externalpage_setup('zilink_guardian_scheduler_manage_settings',null,null,$url);

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);
$PAGE->set_pagelayout('report');

$PAGE->requires->css('/local/zilink/plugins/guardian/scheduler/styles.css');

$sessions = $DB->get_records('zilink_guardian_sched', null, 'timestart DESC');
/*
$navlinks = array();
$navlinks[] = array('name' => get_string('guardian_scheduler_manage', 'local_zilink'), 'type' => 'activity');
$navigation = build_navigation($navlinks);
*/
$table = new html_table('parentseves');

$table->head = array(   get_string('guardian_scheduler_date', 'local_zilink'),
                                get_string('guardian_scheduler_timestart', 'local_zilink'),
                                get_string('guardian_scheduler_timeend', 'local_zilink'),
                                get_string('teachers'),
                                get_string('action'));

$table->attributes = array('id' => 'parentseves','class' => 'generaltable generalbox');
$table->align = array('center','center','center','center','center');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('guardian_scheduler_manage_sessions', 'local_zilink'), 2);
echo $OUTPUT->box(get_string('guardian_scheduler_manage_title_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('guardian_scheduler_support_desc', 'local_zilink').html_writer::link('https://schoolsict.zendesk.com/entries/66700116',get_string('support_site','local_zilink'),array('target'=> '_blank')));
echo $OUTPUT->box(get_string('zilink_plugin_beta', 'local_zilink').get_string('zilink_plugin_support_desc', 'local_zilink') .html_writer::link('http://support.zilink.co.uk/hc/',get_string('support_site','local_zilink'),array('target'=> '_blank')),array('generalbox','error'));
echo '<br /><br />';
foreach ($sessions as $session) {
    $row = array();
    $params = array('session' => $session->id, 'sesskey' => sesskey());
    $url = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/schedule.php', $params);
    $row[] = html_writer::link($url, date('d/m/Y', $session->timestart));
    $row[] = date('H:i', $session->timestart);
    $row[] = date('H:i', $session->timeend);
    
    $row[] = html_writer::link(new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/teachers.php', $params), $OUTPUT->pix_icon("t/preview", get_string('guardian_scheduler_manage', 'local_zilink')));
    //$url = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/teachers.php', $params);
    //$row[] = html_writer::link($url, get_string('guardian_scheduler_manage', 'local_zilink'));
    
    $action = html_writer::link(new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/edit.php', $params), $OUTPUT->pix_icon("t/edit", get_string('edit')));
    $action .= html_writer::link(new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/delete.php', $params), $OUTPUT->pix_icon("t/delete", get_string('delete')));
    $row[] = $action;

    $table->data[] = $row;
}

echo html_writer::table($table);

$url = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/edit.php',array('sesskey' => sesskey()));
//echo $OUTPUT->container(html_writer::link($url, get_string('guardian_scheduler_createnew', 'local_zilink')));

echo $OUTPUT->single_button($url,get_string('guardian_scheduler_createnew', 'local_zilink'));
echo $OUTPUT->footer();