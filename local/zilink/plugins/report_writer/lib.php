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

defined('MOODLE_INTERNAL') || die();

define('ZILINK_STUDENT_REPORTING_OPEN', 1);
define('ZILINK_STUDENT_REPORTING_PUBLISHED', 2);
define('ZILINK_STUDENT_REPORTING_NOTCOLLECTED', '1');
define('ZILINK_STUDENT_REPORTING_INHERITED', '0');
define('ZILINK_STUDENT_REPORTING_FREETEXT', 'freetext');

require_once(dirname(dirname(__FILE__)) . '/core/data.php');
require_once(dirname(dirname(__FILE__)) . '/core/person.php');
require_once(dirname(dirname(__FILE__)) . '/core/base.php');

class ZiLinkReportWriterManager extends ZiLinkBase {

    function __construct($courseid = null) {
        global $CFG, $DB;

        $this->course = new stdClass();
        $this->course->id = $courseid;
        $this->data = new ZiLinkData();
        $this->httpswwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
        $this->person = new ZiLinkPerson();
    }

    private function LoadJavaScript() {
        global $CFG, $PAGE;

        $jsdata = array($this->httpswwwroot, $this->course->id, sesskey());

        $jsmodule = array('name' => 'local_zilink_timetable', 'fullpath' => '/local/zilink/plugins/report_writer/interfaces/' . $CFG->zilink_report_writer_interface . '/module.js', 'requires' => array('base', 'node', 'io'));

        $PAGE->requires->js_init_call('M.local_zilink_report_writer.init', $jsdata, false, $jsmodule);
    }

    public function Manage($args) {
        global $CFG, $DB, $USER, $PAGE;
        $args = $this->DefaultArguments(array('tid' => 0, 'rid' => 0, 'step' => 'first', 'action' => 'view', 'user_idnumber' => $USER->idnumber), $args);

        $content = $this->PrintTabs($args);

        if ($args['tid'] == -1) {
            $content .= $this->GetTemplateList($args);
        }
        if ($args['tid'] == -2) {
            $content .= $this->GetReportTemplateList($args);
        }
        if ($args['tid'] == 0 || $args['tid'] == 3) {
            $content .= $this->GetReportList($args);
        }
        if ($args['tid'] == 1) {
            $content .= $this->CreateReport($args);
        }
        if ($args['tid'] == 2) {
            $content .= $this->EditReport($args);
        }
        if ($args['tid'] == 4) {
            $content .= $this->GetOpenedReportList($args);
        }
        if ($args['tid'] == 5) {
            $content .= $this->GetPublishedReportList($args);
        }
        if ($args['tid'] == 6) {
            $content .= $this->GetArchiveReportList($args);
        }
        return $content;
    }

    public function PrintTabs($args) {
        global $CFG;

        $urlparams = array('sesskey' => sesskey(), 'cid' => $this->course->id);
        $url = new moodle_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams);

        $row = array();
        $row[] = new tabobject(-1, $url . '&tid=-1', get_string('report_writer_tab_template', 'local_zilink'));
        $row[] = new tabobject(-2, $url . '&tid=-2', get_string('report_writer_tab_report_template', 'local_zilink'));
        $row[] = new tabobject(1, $url . '&tid=1', get_string('report_writer_tab_create_report', 'local_zilink'));
        $row[] = new tabobject(0, $url . '&tid=0', get_string('report_writer_tab_manage_reports', 'local_zilink'));

        $row2 = array();
        if ($args['tid'] == 0 || $args['tid'] > 2) {
            if ($args['tid'] == 0) {
                $args['tid'] = 3;
            }
            $row2[] = new tabobject(3, $url . '&tid=3', get_string('report_writer_tab_manage_reports', 'local_zilink'));
            $row2[] = new tabobject(4, $url . '&tid=4', get_string('report_writer_tab_manage_reports_opened', 'local_zilink'));
            $row2[] = new tabobject(5, $url . '&tid=5', get_string('report_writer_tab_manage_reports_published', 'local_zilink'));
            $row2[] = new tabobject(6, $url . '&tid=6', get_string('report_writer_tab_manage_reports_archive', 'local_zilink'));
            $tabs = array($row, $row2);
            return print_tabs($tabs, 0, array($args['tid']), array($args['tid']), true);
        }

        $tabs = array($row);

        if ($args['tid'] == 2) {
            return print_tabs($tabs, 0, null, null, true);
        }
        return print_tabs($tabs, $args['tid'], null, null, true);
        //array($this->GetTopLevelTabs(),$row),ZILINK_SUBJECTS,array($tab),array($tab),true
    }

    public function GetTemplateList($args) {
        global $CFG, $DB, $PAGE, $COURSE, $OUTPUT;

        require_once ($CFG->libdir . '/coursecatlib.php');
        require_once (dirname(__FILE__) . '/admin/forms/create_template.php');

        $componentGroups = $this->data->GetGlobalData('assessment_result_component_groups', true);

        $allowedComponentGroups = explode(',', $CFG->zilink_data_manager_component_groups_allowed);
        $allowedComponents = explode(',', $CFG->zilink_data_manager_components_allowed);
        $allowedSubjects = array();

        $components = array();
        foreach ($componentGroups->componentgroups->AssessmentResultComponentGroup as $group) {
            if (in_array((string)$group->Attribute('RefId'), $allowedComponentGroups)) {
                $parts = explode('::', (string)$group->Name);
                if (count($parts) > 1) {
                    $allowedSubjects[] = trim($parts[1]);
                }

                foreach ($group->ComponentList->AssessmentResultComponentRefId as $refid) {
                    if (in_array((string)$refid, $allowedComponents)) {
                        $cats = $DB->get_records('course_categories', array('name' => trim($parts[1])));
                        foreach ($cats as $cat) {
                            if (is_object($cat) && strlen($cat->idnumber) == 32) {
                                if (!isset($components[(string)$group->Attribute('RefId')]))// || !in_array((string)$refid,$components[(string)$group->Attribute('RefId')]))
                                {
                                    $components[(string)$group->Attribute('RefId')] = (string)$refid;
                                }
                            }
                        }
                    }
                }
            }
        }

        $mform = new zilink_student_reporting_create_template_form(null, array('cid' => $this->course->id, 'sesskey' => sesskey(), 'rid' => $args['rid'], 'tid' => $args['tid'], 'allowed_subjects' => $allowedSubjects));
        $mform->set_data(array('cid' => $this->course->id, 'sesskey' => sesskey(), 'rid' => $args['rid'], 'tid' => $args['tid']));
        if ($data = $mform->get_data()) {
            

            $learnersets = $this->data->GetGlobalData('assessment_learner_sets', true);

            $learnerset = array();
            foreach ($learnersets->learnersets->AssessmentLearnerSet as $set) {
                $learnerset[(string)$set->SchoolGroupRefId][] = (string)$set->Attribute('AssessmentResultComponentGroupRefId');
            }

             $enrol = enrol_get_plugin('zilink_cohort');
             
            if (isset($data->report_writer_template) && $data->report_writer_template <> null) {
                foreach ($data->report_writer_template as $subject => $years) {
                    foreach ($years as $year => $value) {

                        $cat = coursecat::get($year);
                        $courses = $cat->get_courses(array('recursive' => 1));

                        foreach ($courses as $course) {
                            if (strlen($course->idnumber) == 32) {
                                $cohort = $DB->get_record('cohort', array('idnumber' => $course->idnumber));
                                if ($cohort && isset($learnerset[$course->idnumber])) {

                                    $template = new Object();
                                    $template->subjectid = $subject;
                                    $template->yearid = $year;
                                    $template->cohortid = $cohort->id;

                                    $i = 1;

                                    if (isset($learnerset[$course->idnumber])) {

                                        foreach ($learnerset[$course->idnumber] as $componentGroup) {

                                            if (isset($components[$componentGroup])) {

                                                $template->{'component' . $i} = $components[$componentGroup];
                                                $i++;
                                            }
                                        }
                                    }

                                    for ($a = $i; $a <= 6; $a++) {
                                        $template->{'component' . $a} = 'NR';
                                    }
                                    $template->id = $DB->insert_record('zilink_report_writer_tmplts', $template, true);
                                }
                            } else {

                                $records = $DB->get_records('enrol', array('courseid' => $course->id, 'roleid' => 5, 'enrol' => 'zilink_cohort'));
                                
                                foreach ($records as $record) {
                                    
                                    $cohort = $DB->get_record('cohort',array('id' => $record->customint1));
                                    if (strlen($cohort->idnumber) == 32 && isset($learnerset[$cohort->idnumber])) {
                                     
                                        $template = new Object();
                                        $template->subjectid = $subject;
                                        $template->yearid = $year;
                                        $template->cohortid = $cohort->id;
    
                                        $i = 1;
    
                                        if (isset($learnerset[$course->idnumber])) {
    
                                            foreach ($learnerset[$course->idnumber] as $componentGroup) {
    
                                                if (isset($components[$componentGroup])) {
    
                                                    $template->{'component' . $i} = $components[$componentGroup];
                                                    $i++;
                                                }
                                            }
                                        }
    
                                        for ($a = $i; $a <= 6; $a++) {
                                            $template->{'component' . $a} = 'NR';
                                        }
                                        $template->id = $DB->insert_record('zilink_report_writer_tmplts', $template, true);   
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $urlparams = array('sesskey' => sesskey(), 'cid' => $this->course->id, 'tid' => -2);
            redirect(new moodle_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams), get_string('report_writer_templates_created', 'local_zilink'), 1);
        }
        return $mform->display();

    }

    public function GetReportTemplateList($args, $refresh = false) {
        global $CFG, $DB, $PAGE, $COURSE, $OUTPUT;

        $urlparams = array('sesskey' => sesskey(), 'cid' => $this->course->id);
        $url = new moodle_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams);

        if ($args['action'] == 'edit') {
            require_once (dirname(__FILE__) . '/admin/forms/edit_template.php');

            $components = $this->data->GetGlobalData('assessment_result_components', true);

            $allowedComponents = explode(',', $CFG->zilink_data_manager_components_allowed);

            $cs = array();
            foreach ($components->components->AssessmentResultComponent as $component) {

                if (in_array((string)$component->Attribute('RefId'), $allowedComponents)) {
                    $cs[(string)$component->Attribute('RefId')] = (string)$component->Name;
                }
            }

            asort($cs);

            $mform = new zilink_student_reporting_edit_template_form(null, array('cid' => $this->course->id, 'sesskey' => sesskey(), 'rid' => $args['rid'], 'tid' => $args['tid'], 'components' => $cs));
            $mform->set_data(array('cid' => $this->course->id, 'sesskey' => sesskey(), 'rid' => $args['rid'], 'tid' => $args['tid']));
            if ($data = $mform->get_data()) {

                $template = $DB->get_record('zilink_report_writer_tmplts', array('id' => $args['rid']));

                $template->component1 = $data->component1;
                $template->component2 = $data->component2;
                $template->component3 = $data->component3;
                $template->component4 = $data->component4;
                $template->component5 = $data->component5;
                $template->component6 = $data->component6;

                $DB->update_record('zilink_report_writer_tmplts', $template);
                $urlparams = array('sesskey' => sesskey(), 'cid' => $this->course->id, 'tid' => -2, 'action' => 'update', 'rid' => $args['rid']);
                redirect(new moodle_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams), get_string('report_writer_template_updated', 'local_zilink'), 1);
            }

            return $mform->display();
        } else if ($args['action'] == 'update') {
            $url_no = clone $url;
            $url_no->params(array('tid' => '-2'));

            $url_yes = clone $url;
            $url_yes->params(array('tid' => '-2', 'action' => 'update', 'step' => 'confirm', 'rid' => $args['rid']));

            if ($args['step'] == 'confirm') {
                $template = $DB->get_record('zilink_report_writer_tmplts', array('id' => $args['rid']));
                $reports = $DB->get_records('zilink_report_writer_reports', array('templateid' => $args['rid']));

                foreach ($reports as $report) {
                    $report->component1 = $template->component1;
                    $report->component2 = $template->component2;
                    $report->component3 = $template->component3;
                    $report->component4 = $template->component4;
                    $report->component5 = $template->component5;
                    $report->component6 = $template->component6;

                    $DB->update_record('zilink_report_writer_reports', $report);
                }

                $urlparams = array('sesskey' => sesskey(), 'cid' => $this->course->id, 'tid' => 0);
                redirect(new moodle_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams), get_string('report_writer_report_updated', 'local_zilink'), 1);
            } else {
                $report = $DB->get_record('zilink_report_writer_reports', array('id' => $args['rid']));

                $confrim = new single_button($url_yes, get_string('continue'), 'post');
                $cancel = new single_button($url_no, get_string('cancel'), 'post');

                return $OUTPUT->confirm(get_string('report_writer_report_update_derived_reports', 'local_zilink'), $confrim, $cancel);
            }
        }

        $templates = $DB->get_records('zilink_report_writer_tmplts');

        $table = new html_table();
        $table->head = array(get_string('report_writer_subject', 'local_zilink'), get_string('report_writer_year', 'local_zilink'), get_string('report_writer_cohort', 'local_zilink'), get_string('action'));

        $table->align = array('left', 'center', 'center', 'center');
        $table->tablealign = 'center';
        $table->width = '66%';

        $ids = array();
        if (!empty($templates)) {
            foreach ($templates as $template) {
                $url_edit = clone $url;
                $url_edit->params(array('tid' => '-2', 'rid' => $template->id, 'action' => 'edit'));
                $action = html_writer::link($url_edit, $OUTPUT->pix_icon("t/edit", get_string('edit')));

                $table->data[] = array($DB->get_record('course_categories', array('id' => $template->subjectid))->name, $DB->get_record('course_categories', array('id' => $template->yearid))->name, $DB->get_record('cohort', array('id' => $template->cohortid))->name, $action);
            }
        } else {
            $url_preview = clone $url;
            $url_preview->params(array('tid' => '1'));
            $action = html_writer::link($url_preview, $OUTPUT->pix_icon("t/edit", get_string('create')));

            $table->data[] = array('No Templates Available', '', '', $action);
        }

        return html_writer::tag('div', html_writer::table($table, true), array('id' => 'zilinkreportlist', 'name' => 'zilinkreportlist'));

    }

    public function CreateReport($args) {
        global $CFG, $DB;

        require_once (dirname(__FILE__) . '/admin/forms/create_report_from_template.php');

        $allowedSessions = explode(',', $CFG->zilink_data_manager_sessions_allowed);
        $assessmentSessions = $this->data->GetGlobalData('assessment_sessions', true);
        $sessions = array('0' => get_string('report_writer_not_required','local_zilink'));
        foreach ($assessmentSessions->sessions->AssessmentSession as $session) {
            if (in_array($session->Attribute('RefId'), $allowedSessions)) {
                foreach ($session->SIF_ExtendedElements->SIF_ExtendedElement as $sifextendedelement) {

                    if ($sifextendedelement->Attribute('Name') == 'ResultSetId' || $sifextendedelement->Attribute('Name') == 'LocalId') {
                        $rsid = $sifextendedelement;
                    }
                    if ($sifextendedelement->Attribute('Name') == 'Name') {
                        $sessions[$session->Attribute('RefId')] = strval($sifextendedelement);
                    }
                }
            }
        }
        asort($sessions);

        $mform = new zilink_student_reporting_create_report_from_template_form(null, array('cid' => $this->course->id, 'tid' => $args['tid'], 'sessions' => $sessions));
        //$mform->set_data(array('cid' => $this->course->id, 'sesskey' => sesskey(), 'rid' => $args['rid'], 'tid' => $args['tid']));
        if ($data = $mform->get_data()) {

            foreach ($data->template as $template => $value) {
                if ($value == 1) {
                    $report = $DB->get_record('zilink_report_writer_tmplts', array('id' => $template));
                    unset($report->id);
                    $report->templateid = $template;
                    $report->assessmentsessionrefid = $data->assessmentsessionrefid;
                    $report->status = 0;
                    $report->open = 0;
                    $report->published = 0;
                    $report->id = $DB->insert_record('zilink_report_writer_reports', $report, true);
                }
            }
            $urlparams = array('sesskey' => sesskey(), 'cid' => $this->course->id, 'tid' => 0);
            redirect(new moodle_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams), get_string('report_writer_reports_created', 'local_zilink'), 1);
        }

        return $mform->display();
    }

    public function GetOpenedReportList($args, $refresh = false) {
        global $CFG, $DB, $PAGE, $COURSE, $OUTPUT;

        require_once (dirname(__FILE__) . '/admin/forms/opened_reports.php');
        
        $urlparams = array('sesskey' => sesskey(), 'cid' => $this->course->id);
        $url = new moodle_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams);

        $reports = $DB->get_records('zilink_report_writer_reports',array('published' => 0));

        $rows = array();
        if (!empty($reports)) {
            foreach ($reports as $report) {
                $sql = "SELECT DISTINCT userid 
                         FROM {zilink_report_writer_data} 
                         WHERE reportid = :reportid
                         GROUP BY userid";

                $reportsdata = $DB->get_records_sql($sql, array('reportid' => $report->id));

                $sql = "SELECT userid FROM (
                            SELECT DISTINCT userid,setting
                                                     FROM {zilink_report_writer_data}
                                                     WHERE reportid = :reportid1
                                         AND setting = 'teachersignoff' 
                                        AND value = '1'
                            UNION 
                            SELECT DISTINCT userid,setting
                                                     FROM {zilink_report_writer_data}
                                                     WHERE reportid = :reportid2
                                         AND setting = 'subjectsignoff' 
                                        AND value = '1'
                            UNION 
                            SELECT DISTINCT userid,setting
                                                     FROM {zilink_report_writer_data}
                                                     WHERE reportid = :reportid3
                                         AND setting = 'smtsignoff' 
                                        AND value = '1'
                                                             ) c
                            GROUP BY userid
                            HAVING count(*) = 3";

                $completedreports = $DB->get_records_sql($sql, array('reportid1' => $report->id, 'reportid2' => $report->id, 'reportid3' => $report->id));

                $students = $DB->get_records('cohort_members', array('cohortid' => $report->cohortid));


                $name = $DB->get_record('course_categories', array('id' => $report->subjectid))->name;
                $name .= ' - ' . $DB->get_record('course_categories', array('id' => $report->yearid))->name;
                $name .= ' - ' . $DB->get_record('cohort', array('id' => $report->cohortid))->name;

                $assessmentSessions = $this->data->GetGlobalData('assessment_sessions', true);
                $sessions = array();
                foreach ($assessmentSessions->sessions->AssessmentSession as $session) {
                    if ($session->Attribute('RefId') == $report->assessmentsessionrefid) {

                        foreach ($session->SIF_ExtendedElements->SIF_ExtendedElement as $sifextendedelement) {

                            if ($sifextendedelement->Attribute('Name') == 'Name') {

                                $name .= ' - ' . strval($sifextendedelement);
                            }
                        }
                    }
                }
                $rows[] = array($name, count($students), (!$reportsdata ? '0' : count($reportsdata)), count($completedreports), $report->open, $report->id);
            }
        } else {
            $url_preview = clone $url;
            $url_preview->params(array('tid' => '1'));
            $action = html_writer::link($url_preview, $OUTPUT->pix_icon("t/add", get_string('create')));

            $rows[] = array('No Reports Available', '', '', '', -1,0);
        }
        
        $mform = new zilink_student_reporting_opened_reports_form('', array('rows' => $rows,'cid' => $this->course->id, 'sesskey' => sesskey(), 'tid' => $args['tid']));

        if ($edit_data = $mform->get_data()) {
            
            foreach($edit_data->opened as $report => $value) {
                $DB->set_field('zilink_report_writer_reports', 'open', $value, array('id' => $report));
            }
        }
        return $mform->display();
    }

    public function GetArchiveReportList($args, $refresh = false) {
        global $CFG, $DB, $PAGE, $COURSE, $OUTPUT;

        require_once (dirname(__FILE__) . '/admin/forms/archive_reports.php');
        
        $urlparams = array('sesskey' => sesskey(), 'cid' => $this->course->id);
        $url = new moodle_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams);

        $reports = $DB->get_records('zilink_report_writer_reports',array('open' => 0, 'published' => 1));

        $rows = array();
        if (!empty($reports)) {
            foreach ($reports as $report) {
                $sql = "SELECT DISTINCT userid 
                         FROM {zilink_report_writer_data} 
                         WHERE reportid = :reportid
                         GROUP BY userid";

                $reportsdata = $DB->get_records_sql($sql, array('reportid' => $report->id));

                $sql = "SELECT userid FROM (
                            SELECT DISTINCT userid,setting
                                                     FROM {zilink_report_writer_data}
                                                     WHERE reportid = :reportid1
                                         AND setting = 'teachersignoff' 
                                        AND value = '1'
                                        AND status = 0
                            UNION 
                            SELECT DISTINCT userid,setting
                                                     FROM {zilink_report_writer_data}
                                                     WHERE reportid = :reportid2
                                         AND setting = 'subjectsignoff' 
                                        AND value = '1'
                                        AND status = 0
                            UNION 
                            SELECT DISTINCT userid,setting
                                                     FROM {zilink_report_writer_data}
                                                     WHERE reportid = :reportid3
                                         AND setting = 'smtsignoff' 
                                        AND value = '1'
                                        AND status = 0
                                                             ) c
                            GROUP BY userid
                            HAVING count(*) = 3";

                $completedreports = $DB->get_records_sql($sql, array('reportid1' => $report->id, 'reportid2' => $report->id, 'reportid3' => $report->id));

                $students = $DB->get_records('cohort_members', array('cohortid' => $report->cohortid));


                $name = $DB->get_record('course_categories', array('id' => $report->subjectid))->name;
                $name .= ' - ' . $DB->get_record('course_categories', array('id' => $report->yearid))->name;
                $name .= ' - ' . $DB->get_record('cohort', array('id' => $report->cohortid))->name;

                $assessmentSessions = $this->data->GetGlobalData('assessment_sessions', true);
                $sessions = array();
                foreach ($assessmentSessions->sessions->AssessmentSession as $session) {
                    if ($session->Attribute('RefId') == $report->assessmentsessionrefid) {

                        foreach ($session->SIF_ExtendedElements->SIF_ExtendedElement as $sifextendedelement) {

                            if ($sifextendedelement->Attribute('Name') == 'Name') {

                                $name .= ' - ' . strval($sifextendedelement);
                            }
                        }
                    }
                }
                $rows[] = array($name, count($students), (!$reportsdata ? '0' : count($reportsdata)), count($completedreports), $report->published, $report->id);
            }
        } else {
            $url_preview = clone $url;
            $url_preview->params(array('tid' => '1'));
            $action = html_writer::link($url_preview, $OUTPUT->pix_icon("t/add", get_string('create')));

            $rows[] = array('No Reports Available To Be Archived', '', '', '', -1,0);
        }
        
        $mform = new zilink_student_reporting_archive_reports_form('', array('rows' => $rows,'cid' => $this->course->id, 'sesskey' => sesskey(), 'tid' => $args['tid']));

        if ($edit_data = $mform->get_data()) {
            
            foreach($edit_data->opened as $report => $value) {
                $results = $DB->get_records('zilink_report_writer_data',array('id' => $report, 'status' => 0));
                foreach($results as $result) {
                    $DB->set_field('zilink_report_writer_data', 'status', 1, array('id' => $result->id));
                }
            }
        }
        return $mform->display();
    }

    public function GetPublishedReportList($args, $refresh = false) {
        global $CFG, $DB, $PAGE, $COURSE, $OUTPUT;

        require_once (dirname(__FILE__) . '/admin/forms/published_reports.php');
        
        $urlparams = array('sesskey' => sesskey(), 'cid' => $this->course->id);
        $url = new moodle_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams);

        $reports = $DB->get_records('zilink_report_writer_reports',array('open' => 0));

        $rows = array();
        if (!empty($reports)) {
            foreach ($reports as $report) {
                $sql = "SELECT DISTINCT userid 
                         FROM {zilink_report_writer_data} 
                         WHERE reportid = :reportid
                         GROUP BY userid";

                $reportsdata = $DB->get_records_sql($sql, array('reportid' => $report->id));

                $sql = "SELECT userid FROM (
                            SELECT DISTINCT userid,setting
                                                     FROM {zilink_report_writer_data}
                                                     WHERE reportid = :reportid1
                                         AND setting = 'teachersignoff' 
                                         AND value = '1'
                                         AND status = 0
                            UNION 
                            SELECT DISTINCT userid,setting
                                                     FROM {zilink_report_writer_data}
                                                     WHERE reportid = :reportid2
                                         AND setting = 'subjectsignoff' 
                                        AND value = '1'
                                        AND status = 0
                            UNION 
                            SELECT DISTINCT userid,setting
                                                     FROM {zilink_report_writer_data}
                                                     WHERE reportid = :reportid3
                                         AND setting = 'smtsignoff' 
                                        AND value = '1'
                                        AND status = 0
                                                             ) c
                            GROUP BY userid
                            HAVING count(*) = 3";

                $completedreports = $DB->get_records_sql($sql, array('reportid1' => $report->id, 'reportid2' => $report->id, 'reportid3' => $report->id));

                $students = $DB->get_records('cohort_members', array('cohortid' => $report->cohortid));


                $name = $DB->get_record('course_categories', array('id' => $report->subjectid))->name;
                $name .= ' - ' . $DB->get_record('course_categories', array('id' => $report->yearid))->name;
                $name .= ' - ' . $DB->get_record('cohort', array('id' => $report->cohortid))->name;

                $assessmentSessions = $this->data->GetGlobalData('assessment_sessions', true);
                $sessions = array();
                foreach ($assessmentSessions->sessions->AssessmentSession as $session) {
                    if ($session->Attribute('RefId') == $report->assessmentsessionrefid) {

                        foreach ($session->SIF_ExtendedElements->SIF_ExtendedElement as $sifextendedelement) {

                            if ($sifextendedelement->Attribute('Name') == 'Name') {

                                $name .= ' - ' . strval($sifextendedelement);
                            }
                        }
                    }
                }
                if(count($completedreports) > 0) {
                    $rows[] = array($name, count($students), (!$reportsdata ? '0' : count($reportsdata)), count($completedreports), $report->published, $report->id);
                }
            }
        } else {
            $url_preview = clone $url;
            $url_preview->params(array('tid' => '1'));
            $action = html_writer::link($url_preview, $OUTPUT->pix_icon("t/add", get_string('create')));

            $rows[] = array('No Reports Available', '', '', '', -1,0);
        }
        
        $mform = new zilink_student_reporting_published_reports_form('', array('rows' => $rows,'cid' => $this->course->id, 'sesskey' => sesskey(), 'tid' => $args['tid']));

        if ($edit_data = $mform->get_data()) {
            
            foreach($edit_data->published as $report => $value) {
                $DB->set_field('zilink_report_writer_reports', 'published', $value, array('id' => $report));
            }
        }
        return $mform->display();
    }

    public function GetReportList($args, $refresh = false) {
        global $CFG, $DB, $PAGE, $COURSE, $OUTPUT;

        $urlparams = array('sesskey' => sesskey(), 'cid' => $this->course->id);
        $url = new moodle_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams);

        if ($args['action'] == 'delete') {
            $url_no = clone $url;
            $url_no->params(array('tid' => '0','cid' => $this->course->id));

            $url_yes = clone $url;
            $url_yes->params(array('tid' => '0', 'action' => 'delete', 'step' => 'confirm', 'rid' => $args['rid']));

            if ($args['step'] == 'confirm') {
                $DB->delete_records('zilink_report_writer_reports', array('id' => $args['rid']));
                $DB->delete_records('zilink_report_writer_data', array('reportid' => $args['rid']));
                $urlparams = array('sesskey' => sesskey(), 'cid' => $this->course->id, 'tid' => 0);
                redirect(new moodle_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams), get_string('report_writer_report_deleted', 'local_zilink'), 1);
            } else {
                $report = $DB->get_record('zilink_report_writer_reports', array('id' => $args['rid']));

                $confrim = new single_button($url_yes, get_string('continue'), 'post');
                $cancel = new single_button($url_no, get_string('cancel'), 'post');

                $allowedSessions = explode(',', $CFG->zilink_data_manager_sessions_allowed);
                $assessmentSessions = $this->data->GetGlobalData('assessment_sessions', true);
                $sessions = array();
                foreach ($assessmentSessions->sessions->AssessmentSession as $session) {
                    if (in_array($session->Attribute('RefId'), $allowedSessions)) {
                        foreach ($session->SIF_ExtendedElements->SIF_ExtendedElement as $sifextendedelement) {

                            if ($sifextendedelement->Attribute('Name') == 'ResultSetId' || $sifextendedelement->Attribute('Name') == 'LocalId') {
                                $rsid = $sifextendedelement;
                            }
                            if ($sifextendedelement->Attribute('Name') == 'Name') {
                                $sessions[$session->Attribute('RefId')] = strval($sifextendedelement);
                            }
                        }
                    }
                }
                asort($sessions);

                $name = $DB->get_record('course_categories', array('id' => $report->subjectid))->name;
                $name .= ' - ' . $DB->get_record('course_categories', array('id' => $report->yearid))->name;
                $name .= ' - ' . $DB->get_record('cohort', array('id' => $report->cohortid))->name;
                $name .= ' - ' . $sessions[$report->assessmentsessionrefid];

                return $OUTPUT->confirm(get_string('report_writer_report_confirm_delete', 'local_zilink', $name), $confrim, $cancel);
            }
        }

        $reports = $DB->get_records('zilink_report_writer_reports');

        $table = new html_table();
        $table->head = array(get_string('report_writer_edit_report_name', 'local_zilink'), get_string('report_writer_number_students', 'local_zilink'), get_string('report_writer_number_reports_started', 'local_zilink'), get_string('report_writer_number_reports_completed', 'local_zilink'), get_string('action'));

        $table->align = array('left', 'center', 'center', 'center','center');
        $table->tablealign = 'center';
        $table->width = '80%';

        $ids = array();
        if (!empty($reports)) {
            foreach ($reports as $report) {
                $sql = "SELECT DISTINCT userid 
                         FROM {zilink_report_writer_data} 
                         WHERE reportid = :reportid
                         GROUP BY userid";

                $reportsdata = $DB->get_records_sql($sql, array('reportid' => $report->id));

                $sql = "SELECT userid FROM (
                            SELECT DISTINCT userid,setting
                                                     FROM {zilink_report_writer_data}
                                                     WHERE reportid = :reportid1
                                         AND setting = 'teachersignoff' 
                                        AND value = '1'
                                        AND status = 0
                            UNION 
                            SELECT DISTINCT userid,setting
                                                     FROM {zilink_report_writer_data}
                                                     WHERE reportid = :reportid2
                                         AND setting = 'subjectsignoff' 
                                        AND value = '1'
                                        AND status = 0
                            UNION 
                            SELECT DISTINCT userid,setting
                                                     FROM {zilink_report_writer_data}
                                                     WHERE reportid = :reportid3
                                         AND setting = 'smtsignoff' 
                                        AND value = '1'
                                        AND status = 0
                                                             ) c
                            GROUP BY userid
                            HAVING count(*) = 3";

                $completedreports = $DB->get_records_sql($sql, array('reportid1' => $report->id, 'reportid2' => $report->id, 'reportid3' => $report->id));
                //$reportsdata = $DB->get_records('zilink_report_writer_data', array('reportid' => $report->id));
                $students = $DB->get_records('cohort_members', array('cohortid' => $report->cohortid));
                //$url_preview = clone $url;
                //$url_preview->params(array('tid' => '3', 'rid' => $report->id));
                //$action = html_writer::link($url_preview, $OUTPUT->pix_icon("t/preview", get_string('preview')));

                $url_edit = clone $url;
                $url_edit->params(array('tid' => '2', 'rid' => $report->id));
                $action = html_writer::link($url_edit, $OUTPUT->pix_icon("t/edit", get_string('edit')));

                if (count($reportsdata) == 0 && $report->open == 0 && $report->published == 0) {
                    $url_delete = clone $url;
                    $url_delete->params(array('tid' => '0', 'rid' => $report->id, 'action' => 'delete'));
                    $action .= html_writer::link($url_delete, $OUTPUT->pix_icon("t/delete", get_string('delete')));
                }

                $name = $DB->get_record('course_categories', array('id' => $report->subjectid))->name;
                $name .= ' - ' . $DB->get_record('course_categories', array('id' => $report->yearid))->name;
                $name .= ' - ' . $DB->get_record('cohort', array('id' => $report->cohortid))->name;

                $assessmentSessions = $this->data->GetGlobalData('assessment_sessions', true);
                $sessions = array();
                foreach ($assessmentSessions->sessions->AssessmentSession as $session) {
                    if ($session->Attribute('RefId') == $report->assessmentsessionrefid) {

                        foreach ($session->SIF_ExtendedElements->SIF_ExtendedElement as $sifextendedelement) {

                            if ($sifextendedelement->Attribute('Name') == 'Name') {

                                $name .= ' - ' . strval($sifextendedelement);
                            }
                        }
                    }
                }

                $table->data[] = array($name, count($students), (!$reportsdata ? '0' : count($reportsdata)), count($completedreports), $action);
            }
        } else {
            $url_preview = clone $url;
            $url_preview->params(array('tid' => '1'));
            $action = html_writer::link($url_preview, $OUTPUT->pix_icon("t/add", get_string('create')));

            $table->data[] = array('No Reports Available', '', '', '', $action);
        }

        return html_writer::tag('div', html_writer::table($table, true), array('id' => 'zilinkreportlist', 'name' => 'zilinkreportlist'));

    }

    public function EditReport($args) {
        global $CFG, $DB;

        require_once (dirname(__FILE__) . '/admin/forms/edit_report.php');

        $components = $this->data->GetGlobalData('assessment_result_components', true);

        $allowedComponents = explode(',', $CFG->zilink_data_manager_components_allowed);

        $cs = array();
        foreach ($components->components->AssessmentResultComponent as $component) {

            if (in_array((string)$component->Attribute('RefId'), $allowedComponents)) {
                $cs[(string)$component->Attribute('RefId')] = (string)$component->Name;
            }
        }

        asort($cs);

        $allowedSessions = explode(',', $CFG->zilink_data_manager_sessions_allowed);
        $assessmentSessions = $this->data->GetGlobalData('assessment_sessions', true);
        $sessions = array('0' => get_string('report_writer_not_required','local_zilink'));
        foreach ($assessmentSessions->sessions->AssessmentSession as $session) {
            if (in_array($session->Attribute('RefId'), $allowedSessions)) {
                foreach ($session->SIF_ExtendedElements->SIF_ExtendedElement as $sifextendedelement) {

                    if ($sifextendedelement->Attribute('Name') == 'ResultSetId' || $sifextendedelement->Attribute('Name') == 'LocalId') {
                        $rsid = $sifextendedelement;
                    }
                    if ($sifextendedelement->Attribute('Name') == 'Name') {
                        $sessions[$session->Attribute('RefId')] = strval($sifextendedelement);
                    }
                }
            }
        }

        $mform = new zilink_student_reporting_edit_report_form('', array('cid' => $this->course->id, 'sesskey' => sesskey(), 'rid' => $args['rid'], 'tid' => $args['tid'], 'components' => $cs, 'sessions' => $sessions));

        if ($edit_data = $mform->get_data()) {
            
            $report = $DB->get_record('zilink_report_writer_reports', array('id' => $args['rid']));
            $report->assessmentsessionrefid = $edit_data->assessmentsessionrefid;
            $report->open = $edit_data->open;
            $report->published = $edit_data->published;

            if ($edit_data->component1 <> 'NR') {
                $report->component1 = $edit_data->component1;
            } else {
                $report->component1 = '';
            }
            if ($edit_data->component2 <> 'NR') {
                $report->component2 = $edit_data->component2;
            } else {
                $report->component2 = '';
            }
            if ($edit_data->component3 <> 'NR') {
                $report->component3 = $edit_data->component3;
            } else {
                $report->component3 = '';
            }
            if ($edit_data->component4 <> 'NR') {
                $report->component4 = $edit_data->component4;
            } else {
                $report->component4 = '';
            }
            if ($edit_data->component5 <> 'NR') {
                $report->component5 = $edit_data->component5;
            } else {
                $report->component5 = '';
            }
            if ($edit_data->component6 <> 'NR') {
                $report->component6 = $edit_data->component6;
            } else {
                $report->component6 = '';
            }

            $DB->update_record('zilink_report_writer_reports', $report, true);
            $urlparams = array('sesskey' => sesskey(), 'cid' => $this->course->id, 'rid' => $args['rid'], 'tid' => 0);
            redirect(new moodle_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams), get_string('report_writer_report_saved', 'local_zilink'), 1);

        }
        return $mform->display();
    }

    public function Mappings($args) {
        global $CFG, $DB;

        require_once (dirname(__FILE__) . '/admin/forms/mappings.php');
        $edit_form = new stdClass();
        $edit_form = new zilink_report_writer_mappings_form('', array('cid' => $this->course->id, 'sesskey' => sesskey(), 'rid' => $args['rid'], 'tid' => $args['tid'], 'ctid' => $args['ctid'], 'ctname' => $args['ctname'], 'cttype' => $args['cttype']));

        $edit_data = new stdClass();
        /*            $edit_data = $edit_form->get_data();
         if ($edit_data->cttype == 'category') {
         $savecategory = $edit_data->ctid;
         $savecourse = 0;
         } else {
         $savecategory = 0;
         $savecourse = $edit_data->ctid;
         } */
        if ($edit_data = $edit_form->get_data()) {
            if ($edit_data->cttype == 'category') {
                $savecategory = $edit_data->ctid;

                $savecourse = 0;
                // if ($edit_data->cttype == 'category')
                if (isset($edit_data->component1)) {
                    $report1 = new Object();
                    if ($edit_data->component1 == '1') {
                        if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('categoryid' => $edit_data->ctid, 'reportorder' => '1'))) {
                            $report1->id = $aspectmapping->id;
                            $DB->delete_records('zilink_report_writer_mapping', array('id' => $report1->id));
                        }
                    } else {
                        if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('categoryid' => $edit_data->ctid, 'reportorder' => '1'))) {
                            $report1->id = $aspectmapping->id;
                        }
                        $report1->categoryid = $savecategory;
                        $report1->courseid = $savecourse;
                        $report1->componentrefid = $edit_data->component1;
                        $report1->reportorder = 1;

                        if (isset($report1->id)) {
                            $DB->update_record('zilink_report_writer_mapping', $report1, true);
                        } else {
                            $DB->insert_record('zilink_report_writer_mapping', $report1, true);
                        }
                    }
                }
                if (isset($edit_data->component2)) {
                    $report2 = new Object();
                    if ($edit_data->component2 == '1') {
                        if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('categoryid' => $edit_data->ctid, 'reportorder' => '2'))) {
                            $report2->id = $aspectmapping->id;
                            $DB->delete_records('zilink_report_writer_mapping', array('id' => $report2->id));
                        }
                    } else {
                        if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('categoryid' => $edit_data->ctid, 'reportorder' => '2'))) {
                            $report2->id = $aspectmapping->id;
                        }
                        $report2->categoryid = $savecategory;
                        $report2->courseid = $savecourse;
                        $report2->componentrefid = $edit_data->component2;
                        $report2->reportorder = 2;
                        if (isset($report2->id)) {
                            $DB->update_record('zilink_report_writer_mapping', $report2, true);
                        } else {
                            $DB->insert_record('zilink_report_writer_mapping', $report2, true);
                        }
                    }
                }
                if (isset($edit_data->component3)) {
                    $report3 = new Object();
                    if ($edit_data->component3 == '1') {
                        if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('categoryid' => $edit_data->ctid, 'reportorder' => '3'))) {
                            $report3->id = $aspectmapping->id;
                            $DB->delete_records('zilink_report_writer_mapping', array('id' => $report3->id));
                        }
                    } else {
                        if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('categoryid' => $edit_data->ctid, 'reportorder' => '3'))) {
                            $report3->id = $aspectmapping->id;
                        }
                        $report3->categoryid = $savecategory;
                        $report3->courseid = $savecourse;
                        $report3->componentrefid = $edit_data->component3;
                        $report3->reportorder = 3;
                        if (isset($report3->id)) {
                            $DB->update_record('zilink_report_writer_mapping', $report3, true);
                        } else {
                            $DB->insert_record('zilink_report_writer_mapping', $report3, true);
                        }
                    }
                }
                if (isset($edit_data->component4)) {
                    $report4 = new Object();
                    if ($edit_data->component4 == '1') {
                        if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('categoryid' => $edit_data->ctid, 'reportorder' => '4'))) {
                            $report4->id = $aspectmapping->id;
                            $DB->delete_records('zilink_report_writer_mapping', array('id' => $report4->id));
                        }
                    } else {
                        if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('categoryid' => $edit_data->ctid, 'reportorder' => '4'))) {
                            $report4->id = $aspectmapping->id;
                        }
                        $report4->categoryid = $savecategory;
                        $report4->courseid = $savecourse;
                        $report4->componentrefid = $edit_data->component4;
                        $report4->reportorder = 4;
                        //print 'edit_data ->component4'.':'.$edit_data ->component4;
                        if ($edit_data->component4 == '0') {
                            if (isset($report4->id)) {
                                $DB->delete_records('zilink_report_writer_mapping', array('id' => $report4->id));
                            }
                        } else {
                            if (isset($report4->id)) {
                                $DB->update_record('zilink_report_writer_mapping', $report4, true);
                            } else {
                                $DB->insert_record('zilink_report_writer_mapping', $report4, true);
                            }
                        }
                    }
                }

                if (isset($edit_data->component5)) {
                    $report5 = new Object();
                    if ($edit_data->component5 == '1') {
                        if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('categoryid' => $edit_data->ctid, 'reportorder' => '5'))) {
                            $report5->id = $aspectmapping->id;
                            $DB->delete_records('zilink_report_writer_mapping', array('id' => $report5->id));
                        }
                    } else {
                        if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('categoryid' => $edit_data->ctid, 'reportorder' => '5'))) {
                            $report5->id = $aspectmapping->id;
                        }
                        $report5->categoryid = $savecategory;
                        $report5->courseid = $savecourse;
                        $report5->componentrefid = $edit_data->component5;
                        $report5->reportorder = 5;
                        if (isset($report5->id)) {
                            $DB->update_record('zilink_report_writer_mapping', $report5, true);
                        } else {
                            $DB->insert_record('zilink_report_writer_mapping', $report5, true);
                        }
                    }
                }
                if (isset($edit_data->component6)) {
                    $report6 = new Object();
                    if ($edit_data->component6 == '1') {
                        if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('categoryid' => $edit_data->ctid, 'reportorder' => '6'))) {
                            $report6->id = $aspectmapping->id;
                            $DB->delete_records('zilink_report_writer_mapping', array('id' => $report6->id));
                        }
                    } else {
                        if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('categoryid' => $edit_data->ctid, 'reportorder' => '6'))) {
                            $report6->id = $aspectmapping->id;
                        }
                        $report6->categoryid = $savecategory;
                        $report6->courseid = $savecourse;
                        $report6->componentrefid = $edit_data->component6;
                        $report6->reportorder = 6;
                        if ($edit_data->component6 == '0') {
                            if (isset($report6->id)) {
                                $DB->delete_records('zilink_report_writer_mapping', array('id' => $report6->id));
                            }
                        } else {
                            if (isset($report6->id)) {
                                $DB->update_record('zilink_report_writer_mapping', $report6, true);
                            } else {
                                $DB->insert_record('zilink_report_writer_mapping', $report6, true);
                            }
                        }
                    }
                }
            } else {
                $savecategory = 0;
                $savecourse = $edit_data->ctid;
                if (isset($edit_data->component1)) {
                    $report1 = new Object();
                    if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('courseid' => $edit_data->ctid, 'reportorder' => '1'))) {
                        $report1->id = $aspectmapping->id;
                    }
                    $report1->categoryid = $savecategory;
                    $report1->courseid = $savecourse;
                    $report1->componentrefid = $edit_data->component1;
                    $report1->reportorder = 1;
                    if ($edit_data->component1 == '0') {
                        if (isset($report1->id)) {
                            $DB->delete_records('zilink_report_writer_mapping', array('id' => $report1->id));
                        }
                    } else {
                        if (isset($report1->id)) {
                            $DB->update_record('zilink_report_writer_mapping', $report1, true);
                        } else {
                            $DB->insert_record('zilink_report_writer_mapping', $report1, true);
                        }
                    }
                }
                if (isset($edit_data->component2)) {
                    $report2 = new Object();
                    if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('courseid' => $edit_data->ctid, 'reportorder' => '2'))) {
                        $report2->id = $aspectmapping->id;
                    }
                    $report2->categoryid = $savecategory;
                    $report2->courseid = $savecourse;
                    $report2->componentrefid = $edit_data->component2;
                    $report2->reportorder = 2;
                    if ($edit_data->component2 == '0') {
                        if (isset($report2->id)) {
                            $DB->delete_records('zilink_report_writer_mapping', array('id' => $report2->id));
                        }
                    } else {
                        if (isset($report2->id)) {
                            $DB->update_record('zilink_report_writer_mapping', $report2, true);
                        } else {
                            $DB->insert_record('zilink_report_writer_mapping', $report2, true);
                        }
                    }
                }
                if (isset($edit_data->component3)) {
                    $report3 = new Object();
                    if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('courseid' => $edit_data->ctid, 'reportorder' => '3'))) {
                        $report3->id = $aspectmapping->id;
                    }
                    $report3->categoryid = $savecategory;
                    $report3->courseid = $savecourse;
                    $report3->componentrefid = $edit_data->component3;
                    $report3->reportorder = 3;
                    if ($edit_data->component3 == '0') {
                        if (isset($report3->id)) {
                            $DB->delete_records('zilink_report_writer_mapping', array('id' => $report3->id));
                        }
                    } else {
                        if (isset($report3->id)) {
                            $DB->update_record('zilink_report_writer_mapping', $report3, true);
                        } else {
                            $DB->insert_record('zilink_report_writer_mapping', $report3, true);
                        }
                    }
                }
                if (isset($edit_data->component4)) {
                    $report4 = new Object();
                    if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('courseid' => $edit_data->ctid, 'reportorder' => '4'))) {
                        $report4->id = $aspectmapping->id;
                    }
                    $report4->categoryid = $savecategory;
                    $report4->courseid = $savecourse;
                    $report4->componentrefid = $edit_data->component4;
                    $report4->reportorder = 4;
                    if ($edit_data->component4 == '0') {
                        if (isset($report4->id)) {
                            $DB->delete_records('zilink_report_writer_mapping', array('id' => $report4->id));
                        }
                    } else {
                        if (isset($report4->id)) {
                            $DB->update_record('zilink_report_writer_mapping', $report4, true);
                        } else {
                            $DB->insert_record('zilink_report_writer_mapping', $report4, true);
                        }
                    }
                }
                if (isset($edit_data->component5)) {
                    $report5 = new Object();
                    if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('courseid' => $edit_data->ctid, 'reportorder' => '5'))) {
                        $report5->id = $aspectmapping->id;
                    }
                    $report5->categoryid = $savecategory;
                    $report5->courseid = $savecourse;
                    $report5->componentrefid = $edit_data->component5;
                    $report5->reportorder = 5;
                    if ($edit_data->component5 == '0') {
                        if (isset($report5->id)) {
                            $DB->delete_records('zilink_report_writer_mapping', array('id' => $report5->id));
                        }
                    } else {
                        if (isset($report5->id)) {
                            $DB->update_record('zilink_report_writer_mapping', $report5, true);
                        } else {
                            $DB->insert_record('zilink_report_writer_mapping', $report5, true);
                        }
                    }
                }
                if (isset($edit_data->component6)) {
                    $report6 = new Object();
                    if ($aspectmapping = $DB->get_record('zilink_report_writer_mapping', array('courseid' => $edit_data->ctid, 'reportorder' => '6'))) {
                        $report6->id = $aspectmapping->id;
                    }
                    $report6->categoryid = $savecategory;
                    $report6->courseid = $savecourse;
                    $report6->componentrefid = $edit_data->component6;
                    $report6->reportorder = 6;
                    if ($edit_data->component6 == '0') {
                        if (isset($report6->id)) {
                            $DB->delete_records('zilink_report_writer_mapping', array('id' => $report6->id));
                        }
                    } else {
                        if (isset($report6->id)) {
                            $DB->update_record('zilink_report_writer_mapping', $report6, true);
                        } else {
                            $DB->insert_record('zilink_report_writer_mapping', $report6, true);
                        }
                    }
                }
            }
            $urlparams = array('sesskey' => sesskey(), 'cid' => $this->course->id, 'rid' => $args['rid'], 'tid' => 3);
            $url = new moodle_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams);
            redirect(new moodle_url('/local/zilink/plugins/report_writer/admin/manage.php', $urlparams), get_string('report_writer_aspect_saved', 'local_zilink'), 1);
        }
        return $edit_form->display();

    }

    public function DefaultArguments($default_args, $args) {
        foreach ($default_args as $default_arg => $value) {
            if (!isset($args[$default_arg])) {
                $args[$default_arg] = $value;
            }
        }

        return $args;
    }

}
