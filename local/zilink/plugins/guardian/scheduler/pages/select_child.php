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
 * Display the appointment booking form
 *
 * Displays a page containing fields for the student's and parent's name,
 * along with a button to add a new appointment. The button uses an AJAX call to {@see book_ss.php}
 * to display a list of teachers and times for each requested appointment.
 *
 * @package block_parentseve
 * @author Mark Johnson <johnsom@tauntons.ac.uk>, Mike Worth <mike@mike-worth.com>
 * @copyright Copyright &copy; 2009 Taunton's College, Southampton, UK
 * @param int id The ID of the parents' evening
 */
 
 
 
require_once(dirname(__FILE__) . '/../../../../../../config.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(dirname(dirname(__FILE__))).'/view/interfaces/default/lib.php');
require_once(dirname(dirname(__FILE__)).'/renderer.php');
require_once(dirname(__FILE__) .'/forms/book.php');

require_login();
$session = required_param('session', PARAM_INT);
$sesskey = required_param('sesskey',PARAM_RAW);

confirm_sesskey($sesskey);

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey(),'session' => $session);
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/guardian/scheduler/pages/book.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($url, $urlparams);
$PAGE->verify_https_required();

$strmanage = get_string('guardian_scheduler_page_title', 'local_zilink');
$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);
$PAGE->set_pagelayout('report');

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
    redirect($CFG->httpswwwroot.'/course/view.php?id='.SITEID,get_string('requiredpermissionmissing','local_zilink'),1);
} else {
    $PAGE->navbar->add(get_string('edit'));
    $PAGE->navbar->add(date('l jS M Y', $session->timestart));
}
$PAGE->navbar->add(get_string('guardian_scheduler_book', 'local_zilink'));

if ($session->timeend < time()) {
    redirect($CFG->httpswwwroot.'/course/view.php?id='.SITEID,get_string('oldparentseve','local_zilink'),1);
}

try {
    $guardian_view = new ZiLinkGuardianView();
    if(count($guardian_view->people['children']) > 0)
    {
        $children = array();
        $count = 0;
        foreach($guardian_view->people['children'] as $idnumber => $child)
        {
            $children[$count] = fullname($child->user);
            $count++;
        }
        
        $mform = new guardian_scheduler_select_child_form(null,array('children' => $children));
        
        if($data = $mform->get_data())
        {
            $params = array('session' => $data->session, 'sesskey' => sesskey(), 'offset' => $data->child);
            $url = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/book.php',$params);
            redirect($url,'',0);
        }
        else {

            $formdata = new stdClass;
            if ($session) {
                $formdata->session = $session->id;
            } 
            $mform->set_data($formdata);

            echo $OUTPUT->header();
            echo $mform->display();
            echo $OUTPUT->footer();
            die();
        }
    }     
} catch (Exception $e) {
    redirect($CFG->httpswwwroot.'/course/view.php?id='.SITEID,$e->getMessage(),1);
}
    
   