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
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/enrol/zilink/lib.php');
require_once($CFG->dirroot . '/local/zilink/lib.php');
require_once($CFG->dirroot . '/enrol/zilink_cohort/locallib.php');

class local_zilink_external extends external_api {

    public static function zilink_get_users_parameters() {
        return new external_function_parameters(
                            array('types' => new external_single_structure(
                                                    array('type' => new external_multiple_structure(
                                                                        new external_value(PARAM_INT, 'Role'), '', VALUE_OPTIONAL)),
                                                             '', VALUE_DEFAULT, array())));
    }

    public static function zilink_get_users($types) {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . "/user/lib.php");

        $enrol = enrol_get_plugin('zilink');
        if (empty($enrol)) {
            throw new moodle_exception('zilinkpluginnotinstalled', 'local_zilink');
        }

        $params = self::validate_parameters(self::zilink_get_users_parameters(), array('types' => $types));

        $users = $DB->get_records('user', array('deleted' => 0, 'mnethostid' => $CFG->mnet_localhost_id));
        $result = array();

        foreach ($users as $user) {

            $context = context_user::instance($user->id);
            try {
                self::validate_context($context);
            } catch (Exception $e) {
                continue;
            }

            if ($user->id != $USER->id and !has_capability('moodle/user:viewalldetails', $context)) {
                continue;
            }

            if (strlen($user->idnumber) == 32) {
                $userarray = array();
                $userarray['id'] = $user->id;
                $userarray['username'] = $user->username;
                $userarray['firstname'] = '';
                $userarray['lastname'] = '';
                $userarray['email'] = $user->email;
                $userarray['auth'] = $user->auth;
                $userarray['confirmed'] = $user->confirmed;
                $userarray['idnumber'] = $user->idnumber;
                $result[] = $userarray;
            }
        }

        if ($DB->record_exists_sql('SELECT * FROM {zilink_user_data} WHERE extended_details IS NOT NULL', null)) {

            $users = $DB->get_records('zilink_user_data');

            foreach ($users as $user) {

                if (in_array($user->user_idnumber, $result)) {
                    continue;
                }

                $roles = simplexml_load_string(base64_decode($user->roles), 'zilink_local_simple_xml_extended');

                if (empty($roles)) {
                    continue;
                }

                foreach ($roles->roles->role as $role) {

                    if ($role->Attribute('type') == 'guardian' && $role->Attribute('value') == 'true') {
                        $userarray['id'] = 0;
                        $userarray['username'] = '';
                        $userarray['firstname'] = '';
                        $userarray['lastname'] = '';
                        $userarray['email'] = '';
                        $userarray['auth'] = 'zilink_guardian';
                        $userarray['confirmed'] = 0;
                        $userarray['idnumber'] = $user->user_idnumber;
                        $result[] = $userarray;
                    }
                }
            }
        }
        return $result;
    }

    public static function zilink_get_users_returns() {
        return new external_multiple_structure(new external_single_structure( array('id' => new external_value(PARAM_NUMBER, 'ID of the user'), 'username' => new external_value(PARAM_RAW, 'Username policy is defined in Moodle security config'), 'firstname' => new external_value(PARAM_NOTAGS, 'The first name(s) of the user'), 'lastname' => new external_value(PARAM_NOTAGS, 'The family name of the user'), 'email' => new external_value(PARAM_TEXT, 'An email address - allow email as root@localhost'), 'auth' => new external_value(PARAM_SAFEDIR, 'Auth plugins include manual, ldap, imap, etc'), 'confirmed' => new external_value(PARAM_NUMBER, 'Active user: 1 if confirmed, 0 otherwise'), 'idnumber' => new external_value(PARAM_RAW, 'An arbitrary ID code number perhaps from the institution'), )));
    }

    public static function zilink_enrol_course_teacher_parameters() {
        return new external_function_parameters( array('enrolments' => new external_multiple_structure(new external_single_structure( array('courseidnumber' => new external_value(PARAM_TEXT, 'course idnumber'), 'useridnumber' => new external_value(PARAM_TEXT, 'user idnumber'), )))));
    }

    public static function zilink_enrol_course_teacher($enrolments) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->dirroot . "/cohort/lib.php");

        $params = self::validate_parameters(self::zilink_enrol_course_teacher_parameters(), array('enrolments' => $enrolments));
        $transaction = $DB->start_delegated_transaction();

        $enrol = enrol_get_plugin('zilink');
        if (empty($enrol)) {
            throw new moodle_exception('zilinkpluginnotinstalled', 'local_zilink');
        }

        $enrolments = array();

        $sql = "select * from {role} where " . $DB->sql_compare_text('shortname') . " = 'editingteacher'";
        $role = $DB->get_record_sql($sql, null);

        foreach ($params['enrolments'] as $enrolment) {

            $instance = null;

            $users = $DB->get_records('user', array('idnumber' => $enrolment['useridnumber']));
            $courses = $DB->get_records('course', array('idnumber' => $enrolment['courseidnumber']));
            $cohorts = $DB->get_records('cohort', array('idnumber' => $enrolment['courseidnumber']));

            foreach ($users as $user) {

                foreach ($cohorts as $cohort) {
                    if (!$DB->record_exists('zilink_cohort_teachers', array('userid' => $user->id, 'cohortid' => $cohort->id))) {
                        $record = new stdClass();
                        $record->userid = $user->id;
                        $record->cohortid = $cohort->id;
                        $DB->insert_record('zilink_cohort_teachers', $record);
                    }

                    $cohortcourses = $DB->get_records('enrol', array('enrol' => 'zilink_cohort', 'customint1' => $cohort->id));

                    $courseids = array();
                    if (!empty($cohortcourses)) {
                        foreach ($cohortcourses as $cohortcourse) {
                            $courseids[] = $cohortcourse->courseid;
                        }
                        $courses = array_merge($courses, $DB->get_records_list('course', 'id', $courseids));
                    }
                }

                foreach ($courses as $course) {

                    $instance = null;
                    $enrolinstances = enrol_get_instances($course->id, false);

                    foreach ($enrolinstances as $courseenrolinstance) {
                        if ($courseenrolinstance->enrol == "zilink") {
                            $instance = $courseenrolinstance;
                            break;
                        }
                    }
                    if (empty($instance)) {

                        $enrol = enrol_get_plugin('zilink');
                        if ($id = $enrol->add_instance($course, array('customint1' => $course->id, 'roleid' => $role->id))) {
                            $instance = $DB->get_record('enrol', array('id' => $id), '*', MUST_EXIST);
                            unset($id);
                        } else {
                            $errorparams = new stdClass();
                            $errorparams->courseid = $course->id;
                            throw new moodle_exception('wsnoinstance', 'enrol_manual', $errorparams);
                        }
                    }

                    $enrolment['timestart'] = isset($enrolment['timestart']) ? $enrolment['timestart'] : 0;
                    $enrolment['timeend'] = isset($enrolment['timeend']) ? $enrolment['timeend'] : 0;
                    $enrolment['status'] = (isset($enrolment['suspend']) && !empty($enrolment['suspend'])) ? ENROL_USER_SUSPENDED : ENROL_USER_ACTIVE;

                    $enrol->enrol_user($instance, $user->id, $role->id, $enrolment['timestart'], $enrolment['timeend'], $enrolment['status']);
                }

                $enrolments[] = array('courseidnumber' => $enrolment['courseidnumber'], 'useridnumber' => $enrolment['useridnumber']);
            }
        }

        $transaction->allow_commit();

        return $enrolments;
    }

    public static function zilink_enrol_course_teacher_returns() {
        return new external_multiple_structure(new external_single_structure( array('courseidnumber' => new external_value(PARAM_TEXT, 'course idnumber'), 'useridnumber' => new external_value(PARAM_TEXT, 'user idnumber'), )));
    }

    public static function zilink_unenrol_course_teacher_parameters() {
        return new external_function_parameters( array('enrolments' => new external_multiple_structure(new external_single_structure( array('courseidnumber' => new external_value(PARAM_TEXT, 'course idnumber'), 'useridnumber' => new external_value(PARAM_TEXT, 'user idnumber'), )))));
    }

    public static function zilink_unenrol_course_teacher($enrolments) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/enrollib.php');

        $params = self::validate_parameters(self::zilink_enrol_course_teacher_parameters(), array('enrolments' => $enrolments));

        $transaction = $DB->start_delegated_transaction();

        $enrol = enrol_get_plugin('zilink');
        if (empty($enrol)) {
            throw new moodle_exception('zilinkpluginnotinstalled', 'local_zilink');
        }

        $enrolments = array();

        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));

        foreach ($params['enrolments'] as $enrolment) {

            $instance = null;

            $users = $DB->get_records('user', array('idnumber' => $enrolment['useridnumber']));
            $courses = $DB->get_records('course', array('idnumber' => $enrolment['courseidnumber']));
            $cohorts = $DB->get_records('cohort', array('idnumber' => $enrolment['courseidnumber']));

            foreach ($users as $user) {

                foreach ($cohorts as $cohort) {
                    $cohortcourses = $DB->get_records('enrol', array('enrol' => 'zilink_cohort', 'customint1' => $cohort->id));

                    $courseids = array();
                    if (!empty($cohortcourses)) {
                        foreach ($cohortcourses as $cohortcourse) {
                            $courseids[] = $cohortcourse->courseid;
                        }

                        $courses = array_merge($courses, $DB->get_records_list('course', 'id', $courseids));
                    }

                    if ($DB->record_exists('zilink_cohort_teachers', array('userid' => $user->id, 'cohortid' => $cohort->id))) {
                        $DB->delete_records('zilink_cohort_teachers', array('userid' => $user->id, 'cohortid' => $cohort->id));
                    }
                }

                foreach ($courses as $course) {
                    $instance = null;
                    $enrolinstances = enrol_get_instances($course->id, false);

                    foreach ($enrolinstances as $courseenrolinstance) {
                        if ($courseenrolinstance->enrol == "zilink") {
                            $instance = $courseenrolinstance;
                            break;
                        }
                    }
                    if (!empty($instance)) {

                        $enrol->unenrol_user($instance, $user->id);
                    }
                }
            }
            $enrolments[] = array('courseidnumber' => $enrolment['courseidnumber'], 'useridnumber' => $enrolment['useridnumber']);
        }

        $transaction->allow_commit();

        return $enrolments;
    }

    public static function zilink_unenrol_course_teacher_returns() {
        return new external_multiple_structure(new external_single_structure( array('courseidnumber' => new external_value(PARAM_TEXT, 'course idnumber'), 'useridnumber' => new external_value(PARAM_TEXT, 'user idnumber'), )));
    }

    public static function zilink_enrol_cohort_staff_parameters() {
        return new external_function_parameters( array('enrolments' => new external_multiple_structure(new external_single_structure( array('useridnumber' => new external_value(PARAM_TEXT, 'user idnumber'), 'cohortidnumber' => new external_value(PARAM_TEXT, 'cohort id'), )))));
    }

    public static function zilink_enrol_cohort_staff($enrolments) {
        global $DB, $CFG;

        require_once($CFG->dirroot . "/cohort/lib.php");

        $params = self::validate_parameters(self::zilink_enrol_cohort_staff_parameters(), array('enrolments' => $enrolments));

        $enrolmentlist = array();
        $transaction = $DB->start_delegated_transaction();

        $usercount = 0;
        $cohortcount = 0;
        foreach ($params['enrolments'] as $enrolment) {

            $users = $DB->get_records('user', array('idnumber' => $enrolment['useridnumber']));
            $cohorts = $DB->get_records('cohort', array('idnumber' => $enrolment['cohortidnumber']));

            if ($users) {
                foreach ($users as $user) {

                    if ($cohorts) {
                        foreach ($cohorts as $cohort) {
                            cohort_add_member($cohort->id, $user->id);
                        }
                    } 
                }
                $enrolmentlist[] = array('cohortidnumber' => $enrolment['cohortidnumber'], 'useridnumber' => $enrolment['useridnumber']);
            } else {
                $enrolmentlist[] = array('cohortidnumber' => $enrolment['cohortidnumber'], 'useridnumber' => $enrolment['useridnumber']);
            }
        }
        $transaction->allow_commit();
        return $enrolmentlist;
    }

    public static function zilink_enrol_cohort_staff_returns() {
        return new external_multiple_structure(new external_single_structure( array('useridnumber' => new external_value(PARAM_TEXT, 'user idnumber'), 'cohortidnumber' => new external_value(PARAM_TEXT, 'cohort idnumber'), )));
    }

    public static function zilink_unenrol_cohort_staff_parameters() {
        return new external_function_parameters( array('enrolments' => new external_multiple_structure(new external_single_structure( array('cohortidnumber' => new external_value(PARAM_TEXT, 'cohort id'), 'useridnumber' => new external_value(PARAM_TEXT, 'user idnumber'))))));
    }

    public static function zilink_unenrol_cohort_staff($enrolments) {
        global $DB, $CFG;

        require_once($CFG->dirroot . "/cohort/lib.php");

        $params = self::validate_parameters(self::zilink_unenrol_cohort_staff_parameters(), array('enrolments' => $enrolments));
        $transaction = $DB->start_delegated_transaction();
        $enrolmentlist = array();

        foreach ($enrolments as $enrolment) {

            $users = $DB->get_records('user', array('idnumber' => $enrolment['useridnumber']));

            foreach ($users as $user) {
                $cohorts = $DB->get_records('cohort', array('idnumber' => $enrolment['cohortidnumber']));

                foreach ($cohorts as $cohort) {
                    cohort_remove_member($cohort->id, $user->id);
                }
            }
            $enrolmentlist[] = array('useridnumber' => $enrolment['useridnumber'], 'cohortidnumber' => $enrolment['cohortidnumber']);
        }

        $transaction->allow_commit();

        return $enrolmentlist;
    }

    public static function zilink_unenrol_cohort_staff_returns() {
        return new external_multiple_structure(new external_single_structure( array('useridnumber' => new external_value(PARAM_TEXT, 'course id'), 'cohortidnumber' => new external_value(PARAM_TEXT, 'course idnumber'), )));
    }

    public static function zilink_get_staff_cohort_enrolments_parameters() {
        return new external_function_parameters( array('options' => new external_single_structure( array('ids' => new external_multiple_structure(new external_value(PARAM_INT, 'Category id'), 'List of category idnumbers. If empty return all categories
                                            except front page course.', VALUE_OPTIONAL)), 'options - operator OR is used', VALUE_DEFAULT, array())));
    }

    public static function zilink_get_staff_cohort_enrolments($enrolments) {
        global $DB, $CFG;

        $sql = 'SELECT u.idnumber as useridnumber, c.idnumber as cohortidnumber  '.
               ' FROM {cohort} c  '.
               ' JOIN {cohort_members} cm  ON c.id = cm.cohortid '.
               ' JOIN {user} u ON u.id = cm.userid '.
               ' WHERE c.component =  ?'.
               ' AND c.idnumber IN (\'00000000000000000000000000000000\','
                                    .'\'00000000000000000000000000000001\', '
                                    .' \'00000000000000000000000000000002\')';

        $enrolments = $DB->get_recordset_sql($sql, array('enrol_zilink'));

        $enrolmentlist = array();

        if ($enrolments->valid()) {

            foreach ($enrolments as $enrolment) {
                if (strlen($enrolment->useridnumber) == 32 && strlen($enrolment->cohortidnumber) == 32) {
                    $enrolmentlist[] = array('cohortidnumber' => $enrolment->cohortidnumber, 'useridnumber' => $enrolment->useridnumber);
                }
            }
            return $enrolmentlist;
        }
        return $enrolmentlist;
    }

    public static function zilink_get_staff_cohort_enrolments_returns() {
        return new external_multiple_structure(new external_single_structure( array('useridnumber' => new external_value(PARAM_TEXT, 'course id'), 'cohortidnumber' => new external_value(PARAM_TEXT, 'course idnumber'), )));
    }

    public static function zilink_get_courses_parameters() {
        return new external_function_parameters( array('options' => new external_single_structure( array('idnumbers' => new external_multiple_structure(new external_value(PARAM_INT, 'Course idnumber'), 'List of course idnumber. If empty return all courses
                                            except front page course.', VALUE_OPTIONAL)), 'options - operator OR is used', VALUE_DEFAULT, array())));
    }

    public static function zilink_get_courses($options) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");

        $params = self::validate_parameters(self::zilink_get_courses_parameters(), array('options' => $options));

        if (!key_exists('idnumbers', $params['options']) or empty($params['options']['idnumbers'])) {
            $courses = $DB->get_records('course');
        } else {
            $courses = $DB->get_records_list('course', 'idnumber', $params['options']['idnumbers']);
        }

        $coursesinfo = array();
        foreach ($courses as $course) {

            if (strlen($course->idnumber) == 32) {

                $context = context_course::instance($course->id);
                try {
                    self::validate_context($context);
                } catch (Exception $e) {
                    $exceptionparam = new stdClass();
                    $exceptionparam->message = $e->getMessage();
                    $exceptionparam->courseid = $course->id;
                    throw new moodle_exception(get_string('errorcoursecontextnotvalid', 'webservice', $exceptionparam));
                }
                require_capability('moodle/course:view', $context);

                $courseinfo = array();
                $courseinfo['id'] = $course->id;
                $courseinfo['fullname'] = $course->fullname;
                $courseinfo['shortname'] = $course->shortname;
                $courseinfo['categoryid'] = $course->category;
                $courseinfo['summary'] = null;
                $courseinfo['summaryformat'] = $course->summaryformat;
                $courseinfo['format'] = $course->format;
                $courseinfo['startdate'] = $course->startdate;
                if (isset($course->numsections)) {
                    $courseinfo['numsections'] = $course->numsections;
                } else {
                    $courseinfo['numsections'] = null;
                }

                $courseadmin = has_capability('moodle/course:update', $context);
                if ($courseadmin) {
                    $courseinfo['categorysortorder'] = $course->sortorder;
                    $courseinfo['idnumber'] = $course->idnumber;
                    $courseinfo['showgrades'] = $course->showgrades;
                    $courseinfo['showreports'] = $course->showreports;
                    $courseinfo['newsitems'] = $course->newsitems;
                    $courseinfo['visible'] = $course->visible;
                    $courseinfo['maxbytes'] = $course->maxbytes;
                    if (isset($course->hiddensections)) {
                        $courseinfo['hiddensections'] = $course->hiddensections;
                    } else {
                        $courseinfo['hiddensections'] = null;
                    }
                    $courseinfo['groupmode'] = $course->groupmode;
                    $courseinfo['groupmodeforce'] = $course->groupmodeforce;
                    $courseinfo['defaultgroupingid'] = $course->defaultgroupingid;
                    $courseinfo['lang'] = $course->lang;
                    $courseinfo['timecreated'] = $course->timecreated;
                    $courseinfo['timemodified'] = $course->timemodified;
                    $courseinfo['forcetheme'] = $course->theme;
                    $courseinfo['enablecompletion'] = $course->enablecompletion;
                    if (isset($course->completionstartonenrol)) {
                        $courseinfo['completionstartonenrol'] = $course->completionstartonenrol;
                    } else {
                        $courseinfo['completionstartonenrol'] = null;
                    }
                    $courseinfo['completionnotify'] = $course->completionnotify;
                }

                if ($courseadmin or $course->visible or has_capability('moodle/course:viewhiddencourses', $context)) {
                    $coursesinfo[] = $courseinfo;
                }
            }
        }

        return $coursesinfo;
    }

    public static function zilink_get_courses_returns() {
        return new external_multiple_structure(new external_single_structure( array('id' => new external_value(PARAM_INT, 'course id'), 'shortname' => new external_value(PARAM_TEXT, 'course short name'), 'categoryid' => new external_value(PARAM_INT, 'category id'), 'categorysortorder' => new external_value(PARAM_INT, 'sort order into the category', VALUE_OPTIONAL), 'fullname' => new external_value(PARAM_TEXT, 'full name'), 'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL), 'summary' => new external_value(PARAM_RAW, 'summary'), 'summaryformat' => new external_value(PARAM_INT, 'the summary text Moodle format'), 'format' => new external_value(PARAM_ALPHANUMEXT, 'course format: weeks, topics, social, site, ..'), 'showgrades' => new external_value(PARAM_INT, '1 if grades are shown, otherwise 0', VALUE_OPTIONAL), 'newsitems' => new external_value(PARAM_INT, 'number of recent items appearing on the course page', VALUE_OPTIONAL), 'startdate' => new external_value(PARAM_INT, 'timestamp when the course start'), 'maxbytes' => new external_value(PARAM_INT, 'largest size of file that can be uploaded into the course', VALUE_OPTIONAL), 'showreports' => new external_value(PARAM_INT, 'are activity report shown (yes = 1, no =0)', VALUE_OPTIONAL), 'visible' => new external_value(PARAM_INT, '1: available to student, 0:not available', VALUE_OPTIONAL), 'hiddensections' => new external_value(PARAM_INT, 'How the hidden sections in the course are displayed to students', VALUE_OPTIONAL), 'groupmode' => new external_value(PARAM_INT, 'no group, separate, visible', VALUE_OPTIONAL), 'groupmodeforce' => new external_value(PARAM_INT, '1: yes, 0: no', VALUE_OPTIONAL), 'defaultgroupingid' => new external_value(PARAM_INT, 'default grouping id', VALUE_OPTIONAL), 'timecreated' => new external_value(PARAM_INT, 'timestamp when the course have been created', VALUE_OPTIONAL), 'timemodified' => new external_value(PARAM_INT, 'timestamp when the course have been modified', VALUE_OPTIONAL), 'enablecompletion' => new external_value(PARAM_INT, 'Enabled, control via completion and activity settings. Disbaled, 
                                        not shown in activity settings.', VALUE_OPTIONAL), 'completionnotify' => new external_value(PARAM_INT, '1: yes 0: no', VALUE_OPTIONAL), 'lang' => new external_value(PARAM_ALPHANUMEXT, 'forced course language', VALUE_OPTIONAL), 'forcetheme' => new external_value(PARAM_ALPHANUMEXT, 'name of the force theme', VALUE_OPTIONAL), ), 'course'));
    }

    public static function zilink_create_courses_parameters() {
        $courseconfig = get_config('moodlecourse');
        return new external_function_parameters( array('courses' => new external_multiple_structure(new external_single_structure( array('idnumber' => new external_value(PARAM_TEXT, 'idnumber'), 'fullname' => new external_value(PARAM_TEXT, 'full name'), 'shortname' => new external_value(PARAM_TEXT, 'course short name'), 'categoryid' => new external_value(PARAM_INT, 'categoryid'), )), 'courses to create')));
    }

    public static function zilink_create_courses($courses) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->libdir . '/completionlib.php');
        require_once($CFG->libdir . '/coursecatlib.php');

        ZiLinkLoadDefaults();

        $params = self::validate_parameters(self::zilink_create_courses_parameters(), array('courses' => $courses));

        if ($CFG->zilink_cohort_auto_create == 0) {

            return $courses;
        }

        if ($CFG->zilink_category_structure == 0 && $CFG->zilink_category_root == 0) {
            return $courses;
        }

        if (($CFG->zilink_course_auto_create_classes == 1 || $CFG->zilink_course_auto_create_years == 1) && $CFG->zilink_course_template == 0) {
            
            throw new moodle_exception(get_string('zilink_plugin_missing_template', 'local_zilink'));
        } else {
            $template = $DB->get_record('course', array('id' => $CFG->zilink_course_template));
            if (!$template && ($CFG->zilink_course_auto_create_classes == 1 || $CFG->zilink_course_auto_create_years == 1)) {
                throw new moodle_exception(get_string('zilink_plugin_missing_template', 'local_zilink') . $template->fullname);
            }
        }

        $cohorts = $DB->get_records('cohort', array('component' => 'enrol_zilink'));
        $role = $DB->get_record('role', array('shortname' => 'student'));

        $transaction = $DB->start_delegated_transaction();

        $custom = file_exists($CFG->dirroot . '/local/zilink/plugins/courses/custom/course_create.php');
        $enrol = enrol_get_plugin('zilink_cohort');

        foreach ($params['courses'] as $course) {

            $year = zilinkGetYear($course['shortname']);

            $mdlcourse = $DB->get_record('course', array('idnumber' => $course['idnumber'], 'shortname' => $course['shortname'], 'fullname' => $course['fullname']));

            if (!is_object($mdlcourse)) {
                $mdlcourse = $DB->get_record('course', array('shortname' => $course['shortname']));

                if (is_object($mdlcourse)) {

                    $mdlcourse->idnumber = $course['idnumber'];
                    $DB->update_record('course', $mdlcourse);

                }
            }

            if (!is_object($mdlcourse)) {

                $courseparent = $course['categoryid'];
                $subjectcategory = $DB->get_record('course_categories', array('id' => $course['categoryid']));

                if (is_object($subjectcategory)) {
                    $courseparent = $subjectcategory->id;

                    if (!in_array($subjectcategory->name, array('House', 'Year Group', 'Registration Group'))) {
                        if (!$year == null) {
                            if ($CFG->zilink_category_structure == 2) {
                                $yearcategory = $DB->get_record('course_categories', array('name' => 'Year ' . $year, 'parent' => $courseparent));

                                if (!is_object($yearcategory)) {

                                    $yearcategory = new stdClass();
                                    $yearcategory->name = 'Year ' . $year;
                                    $yearcategory->parent = $courseparent;
                                    $yearcategory->sortorder = 999;
                                    $yearcategory->visible = 0;
                                    $yearcategory = coursecat::create($yearcategory);
                                }
                                $courseparent = $yearcategory->id;
                            }
                            if ($CFG->zilink_course_auto_create_years == 1 && $custom == false && !in_array($subjectcategory->name, array('House', 'Year Group', 'Registration Group'))) {
                                $mdlcourse = $DB->get_record('course', array('shortname' => 'Year ' . $year . ' ' . $subjectcategory->name));
                                if (!is_object($mdlcourse) && $year <> null) {

                                    $template->id = null;
                                    $template->idnumber = '';
                                    $template->shortname = 'Year ' . $year . ' ' . $subjectcategory->name;
                                    $template->fullname = 'Year ' . $year . ' ' . $subjectcategory->name;
                                    $template->visible = 0;
                                    $template->category = $courseparent;
                                    $course['id'] = create_course($template)->id;
                                    $mdlcourse = $template;
                                    $mdlcourse->id = $course['id'];
                                }

                                if ($cohorts) {
                                    foreach ($cohorts as $cohort) {
                                        if ($cohort->idnumber == $course['idnumber']) {
                                            if ($zilink_enrol = $DB->get_records('enrol', array('courseid' => $mdlcourse->id, 'roleid' => $role->id, 'customint1' => $cohort->id))) {
                                                $enrolinstances = enrol_get_instances($mdlcourse->id, false);
                                                $count = 0;
                                                $max = count($zilink_enrol) - 1;
                                                foreach ($enrolinstances as $courseenrolinstance) {
                                                    if ($courseenrolinstance->enrol == "zilink_cohort" && $courseenrolinstance->customint1 == $cohort->id) {
                                                        if ($count < $max) {
                                                            $enrol->delete_instance($courseenrolinstance);
                                                            $count++;
                                                        }
                                                    }
                                                }
                                            } else {
                                                $enrol->add_instance($mdlcourse, array('customint1' => $cohort->id, 'roleid' => $role->id));
                                                enrol_zilink_cohort_sync($mdlcourse->id);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($CFG->zilink_course_auto_create_classes == 1 && $custom == false) {
                        if (!in_array($subjectcategory->name, array('House', 'Year Group', 'Registration Group'))) {
                            $classcategory = $DB->get_record('course_categories', array('name' => 'Classes', 'parent' => $courseparent));
                            if (!is_object($classcategory)) {

                                $classcategory = new stdClass();
                                $classcategory->name = 'Classes';
                                $classcategory->parent = $courseparent;
                                $classcategory->sortorder = 999;
                                $classcategory->visible = 0;
                                $classcategory = coursecat::create($classcategory);
                                $courseparent = $classcategory->id;
                            }
                            $courseparent = $classcategory->id;
                        }
                        if ($CFG->zilink_course_auto_create_classes == 1) {
                            $mdlcourse = $DB->get_record('course', array('shortname' => $course['shortname']));
                            if (is_object($mdlcourse)) {
                                $mdlcourse->idnumber = $course['idnumber'];
                                $mdlcourse->category = $courseparent;
                                $DB->update_record('course', $mdlcourse);
                            } else {
                                $template->id = null;
                                $template->idnumber = $course['idnumber'];
                                $template->shortname = $course['shortname'];
                                $template->fullname = $course['fullname'];
                                $template->visible = 0;
                                $template->category = $courseparent;
                                $course['id'] = create_course($template)->id;
                            }
                        }
                    } else if ($CFG->zilink_course_auto_create_classes == 0 && $custom == false && $CFG->zilink_course_auto_create_years == 1) {
                        if (in_array($subjectcategory->name, array('House', 'Year Group', 'Registration Group'))) {
                            $mdlcourse = $DB->get_record('course', array('shortname' => $course['shortname']));
                            if (is_object($mdlcourse)) {
                                $mdlcourse->idnumber = $course['idnumber'];
                                $mdlcourse->category = $courseparent;
                                $DB->update_record('course', $mdlcourse);
                            } else {
                                $template->id = null;
                                $template->idnumber = $course['idnumber'];
                                $template->shortname = $course['shortname'];
                                $template->fullname = $course['fullname'];
                                $template->visible = 0;
                                $template->category = $courseparent;
                                $course['id'] = create_course($template)->id;
                            }
                        }
                    }

                    if ($custom) {
                        include($CFG->dirroot . '/enrol/zilink/custom/course_create.php');
                        $resultcourses[] = array('id' => $course['id'], 'shortname' => $course['shortname'], 'fullname' => $course['fullname'], 'idnumber' => $course['idnumber']);
                    } else {
                        $resultcourses[] = array('id' => 0, 'shortname' => $course['shortname'], 'fullname' => $course['fullname'], 'idnumber' => $course['idnumber']);
                    }
                } else {
                    $resultcourses[] = array('id' => 0, 'shortname' => $course['shortname'], 'fullname' => $course['fullname'], 'idnumber' => $course['idnumber']);
                }
            } else {
                
                $courseparent = $course['categoryid'];
                $subjectcategory = $DB->get_record('course_categories', array('id' => $course['categoryid']));

                if (is_object($subjectcategory)) {
                    $courseparent = $subjectcategory->id;

                    if (!in_array($subjectcategory->name, array('House', 'Year Group', 'Registration Group'))) {
                        if (!$year == null) {
                            if ($CFG->zilink_category_structure == 2) {
                                $yearcategory = $DB->get_record('course_categories', array('name' => 'Year ' . $year, 'parent' => $courseparent));

                                if (!is_object($yearcategory)) {

                                    $yearcategory = new stdClass();
                                    $yearcategory->name = 'Year ' . $year;
                                    $yearcategory->parent = $courseparent;
                                    $yearcategory->sortorder = 999;
                                    $yearcategory->visible = 0;
                                    $yearcategory = coursecat::create($yearcategory);
                                }
                                $courseparent = $yearcategory->id;
                            }
                            if ($CFG->zilink_course_auto_create_years == 1 && $custom == false && !in_array($subjectcategory->name, array('House', 'Year Group', 'Registration Group'))) {
                                $mdlcourse = $DB->get_record('course', array('shortname' => 'Year ' . $year . ' ' . $subjectcategory->name));
                                if (!is_object($mdlcourse) && $year <> null) {

                                    $template->id = null;
                                    $template->idnumber = '';
                                    $template->shortname = 'Year ' . $year . ' ' . $subjectcategory->name;
                                    $template->fullname = 'Year ' . $year . ' ' . $subjectcategory->name;
                                    $template->visible = 0;
                                    $template->category = $courseparent;
                                    $course['id'] = create_course($template)->id;
                                    $mdlcourse = $template;
                                    $mdlcourse->id = $course['id'];
                                }

                                if ($cohorts) {
                                    foreach ($cohorts as $cohort) {
                                        if ($cohort->idnumber == $course['idnumber']) {
                                            if ($zilink_enrol = $DB->get_records('enrol', array('courseid' => $mdlcourse->id, 'roleid' => $role->id, 'customint1' => $cohort->id))) {
                                                $enrolinstances = enrol_get_instances($mdlcourse->id, false);
                                                $count = 0;
                                                $max = count($zilink_enrol) - 1;
                                                foreach ($enrolinstances as $courseenrolinstance) {
                                                    if ($courseenrolinstance->enrol == "zilink_cohort" && $courseenrolinstance->customint1 == $cohort->id) {
                                                        if ($count < $max) {
                                                            $enrol->delete_instance($courseenrolinstance);
                                                            $count++;
                                                        }
                                                    }
                                                }
                                            } else {
                                                $enrol->add_instance($mdlcourse, array('customint1' => $cohort->id, 'roleid' => $role->id));
                                                enrol_zilink_cohort_sync($mdlcourse->id);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $resultcourses[] = array('id' => $mdlcourse->id, 'shortname' => $mdlcourse->shortname, 'fullname' => $mdlcourse->fullname, 'idnumber' => $mdlcourse->idnumber);
            }
        }
        fix_course_sortorder();
        $transaction->allow_commit();
        return $resultcourses;
    }

    public static function zilink_create_courses_returns() {
        return new external_multiple_structure(new external_single_structure( array('id' => new external_value(PARAM_INT, 'course id', VALUE_OPTIONAL), 'idnumber' => new external_value(PARAM_TEXT, 'course idnumber'), 'shortname' => new external_value(PARAM_TEXT, 'short name'), 'fullname' => new external_value(PARAM_TEXT, 'full name'), 'idnumber' => new external_value(PARAM_TEXT, 'course idnumber'))));
    }

    public static function zilink_sync_course_cohorts_parameters() {
        return new external_function_parameters( array('options' => new external_single_structure( array('idnumbers' => new external_multiple_structure(new external_value(PARAM_TEXT, 'idnumber'), 'List of course idnumber. If empty return all courses
                                            except front page course.', VALUE_OPTIONAL)), 'options - operator OR is used', VALUE_DEFAULT, array())));
    }

    public static function zilink_sync_course_cohorts($options) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->dirroot . "/enrol/zilink_cohort/locallib.php");

        ZiLinkLoadDefaults();
        $params = self::validate_parameters(self::zilink_sync_course_cohorts_parameters(), array('options' => $options));

        if (($CFG->zilink_course_auto_create_classes == 0 && $CFG->zilink_course_auto_create_years == 0) || file_exists($CFG->dirroot . '/enrol/zilink/custom/create_course.php')) {
            $success = array('success' => 1);
            return array($success);
        }

        if (!empty($params['options']['idnumbers'])) {
            if ($courses = $DB->get_records_list('course', 'idnumber', $params['options']['idnumbers'])) {
                if ($cohorts = $DB->get_records_list('cohort', 'idnumber', $params['options']['idnumbers'])) {
                    $enrol = enrol_get_plugin('zilink_cohort');

                    foreach ($courses as $course) {
                        foreach ($cohorts as $cohort) {
                            if ($cohort->idnumber == $course->idnumber) {

                                if ($zilink_enrol = $DB->get_records('enrol', array('courseid' => $course->id, 'roleid' => 5, 'customint1' => $cohort->id))) {
                                    $enrolinstances = enrol_get_instances($course->id, false);
                                    $count = 0;
                                    $max = count($zilink_enrol) - 1;
                                    foreach ($enrolinstances as $courseenrolinstance) {
                                        if ($courseenrolinstance->enrol == "zilink_cohort" && $courseenrolinstance->customint1 == $cohort->id) {
                                            if ($count < $max) {
                                                $enrol->delete_instance($courseenrolinstance);
                                                $count++;
                                            }
                                        }
                                    }
                                } else {
                                    $enrol->add_instance($course, array('customint1' => $cohort->id, 'roleid' => 5));
                                    enrol_zilink_cohort_sync($course->id);
                                }
                                $success = array('success' => 1);
                            }
                        }
                    }
                    $success = array('success' => 1);
                } else {
                    $success = array('success' => 0);
                }
            } else {
                if ($CFG->zilink_course_auto_create_classes == 0 || file_exists($CFG->dirroot . '/enrol/zilink/custom/course_create.php')) {
                    $success = array('success' => 1);
                } else {
                    $success = array('success' => 0);
                }
            }
        } else {
            $success = array('success' => 0);
        }
        return array($success);
    }

    public static function zilink_sync_course_cohorts_returns() {
        return new external_multiple_structure(new external_single_structure( array('success' => new external_value(PARAM_TEXT, 'success'), )));
    }

    public static function zilink_get_categories_parameters() {
        return new external_function_parameters( array('options' => new external_single_structure( array('ids' => new external_multiple_structure(new external_value(PARAM_INT, 'Category id'), 'List of category idnumbers. If empty return all categories
                                            except front page course.', VALUE_OPTIONAL)), 'options - operator OR is used', VALUE_DEFAULT, array())));
    }

    public static function zilink_get_categories($options) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");

        $course_categories = $DB->get_records('course_categories');

        $categories = array();
        foreach ($course_categories as $coursecategory) {

            $category = array();
            $category['id'] = $coursecategory->id;
            $category['name'] = $coursecategory->name;
            $category['description'] = '';
            $category['parent'] = $coursecategory->parent;
            $categories[] = $category;
        }

        return $categories;
    }

    public static function zilink_get_categories_returns() {
        return new external_multiple_structure(new external_single_structure( array('id' => new external_value(PARAM_INT, 'category id'), 'name' => new external_value(PARAM_TEXT, 'category name'), 'description' => new external_value(PARAM_TEXT, 'category description'), 'parent' => new external_value(PARAM_INT, 'parent'), ), 'categories'));
    }

    public static function zilink_create_categories_parameters() {

        return new external_function_parameters( array('categories' => new external_multiple_structure(new external_single_structure( array('idnumber' => new external_value(PARAM_TEXT, 'idnumber'), 'name' => new external_value(PARAM_TEXT, 'name'), 'description' => new external_value(PARAM_TEXT, 'description'), 
        )), 'categories to create')));
    }

    public static function zilink_create_categories($categories) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->libdir . '/completionlib.php');
        require_once($CFG->libdir . '/coursecatlib.php');
        
        ZiLinkLoadDefaults();
        if ($CFG->zilink_category_structure == 0) {
            return $categories;
        }

        $params = self::validate_parameters(self::zilink_create_categories_parameters(), array('categories' => $categories));

        $transaction = $DB->start_delegated_transaction();

        $createdcategories = array();
        foreach ($params['categories'] as $category) {
            /*
             if ($category['parent']) {
             if (!$DB->record_exists('course_categories', array('id' => $category['parent']))) {
             throw new moodle_exception('unknowcategory');
             }
             $context = context_coursecat::instance($category['parent']);
             } else {
             $context = context_system::instance();
             }
             self::validate_context($context);
             require_capability('moodle/category:manage', $context);
             */
            // Check name.
            if (core_text::strlen($category['name']) > 255) {
                throw new moodle_exception('categorytoolong');
            }

            $newcategory = new stdClass();
            $newcategory->name = urldecode($category['name']);
            $newcategory->parent = $CFG->zilink_category_root;
            $newcategory->sortorder = 999;
            if (!empty($category['description'])) {
                $newcategory->description = $category['description'];
            }
            if (!empty($category['idnumber'])) {
                if (core_text::strlen($category['idnumber']) > 100) {
                    throw new moodle_exception('idnumbertoolong');
                }

                try {
                    if ($existing = $DB->get_record('course_categories', array('idnumber' => $category['idnumber']))) {
                        if ($existing->name <> $category['name']) {
                            
                            $existing->name = $category['name'];
                            $DB->update_record('course_categories', $existing);
                        }
                        $resultcategories[] = array('idnumber' => $category['idnumber'], 'name' => $category['name'], 'description' => '', 'parent' => $CFG->zilink_category_root);
                        continue;
                    } else {
                        if ($existing = $DB->get_record('course_categories', array('name' => $category['name']))) {
                            $existing->idnumber = $category['idnumber'];
                            $DB->update_record('course_categories', $existing);
                            $resultcategories[] = array('idnumber' => $category['idnumber'], 'name' => $category['name'], 'description' => '', 'parent' => $CFG->zilink_category_root);
                            continue;
                        }
                    }
                    $newcategory->idnumber = $category['idnumber'];
                } catch (Exception $e) {
                    if ($existing = $DB->get_record('course_categories', array('name' => $category['name'], 'parent' => $CFG->zilink_category_structure))) {
                        $resultcategories[] = array('idnumber' => $category['idnumber'], 'name' => $category['name'], 'description' => '', 'parent' => $CFG->zilink_category_root);
                        continue;
                    }
                }
            }

            $newcategory = coursecat::create($newcategory);

            $resultcategories[] = array('idnumber' => $category['idnumber'], 'name' => $category['name'], 'description' => '', 'parent' => $CFG->zilink_category_root);
        }
        fix_course_sortorder();
        $transaction->allow_commit();
        return $resultcategories;

    }

    public static function zilink_create_categories_returns() {
        return new external_multiple_structure(new external_single_structure( array('idnumber' => new external_value(PARAM_TEXT, 'category idnumber', VALUE_OPTIONAL), 'name' => new external_value(PARAM_TEXT, 'category name'), 'description' => new external_value(PARAM_TEXT, 'category description'), 'parent' => new external_value(PARAM_TEXT, 'parent', VALUE_OPTIONAL), ), 'categories'));
    }

    public static function zilink_get_cohorts_parameters() {
        return new external_function_parameters( array('options' => new external_single_structure( array('idnumbers' => new external_multiple_structure(new external_value(PARAM_INT, 'Cohort id'), 'List of cohort idnumbers. If empty return all cohort
                                            except front page course.', VALUE_OPTIONAL)), 'options - operator OR is used', VALUE_DEFAULT, array())));
    }

    public static function zilink_get_cohorts($options) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/cohort/lib.php");

        $params = self::validate_parameters(self::zilink_get_cohorts_parameters(), array('options' => $options));
        if (!key_exists('ids', $params['options']) or empty($params['options']['idnumbers'])) {
            $cohorts = $DB->get_records('cohort');
        } else {
            $cohorts = $DB->get_records_list('cohort', 'id', $params['options']['idnumbers'], 'component', 'zilink');
        }

        $cohortlist = array();
        foreach ($cohorts as $cohort) {

            $cohortlistitem = array();
            $cohortlistitem['id'] = $cohort->id;
            $cohortlistitem['idnumber'] = $cohort->idnumber;
            $cohortlistitem['name'] = $cohort->name;
            $cohortlistitem['description'] = $cohort->description;

            if (strlen($cohort->idnumber) == 32) {
                $cohortlist[] = $cohortlistitem;
            }
        }

        return $cohortlist;
    }

    public static function zilink_get_cohorts_returns() {
        return new external_multiple_structure(new external_single_structure( array('id' => new external_value(PARAM_TEXT, 'cohort id'), 'idnumber' => new external_value(PARAM_TEXT, 'cohort idnumber'), 'name' => new external_value(PARAM_TEXT, 'cohort name', VALUE_OPTIONAL), 'description' => new external_value(PARAM_TEXT, 'description', VALUE_OPTIONAL), ), 'cohort'));
    }

    public static function zilink_create_cohorts_parameters() {
        $courseconfig = get_config('moodlecourse');
        return new external_function_parameters( array('cohorts' => new external_multiple_structure(new external_single_structure( array('name' => new external_value(PARAM_TEXT, 'name'), 'idnumber' => new external_value(PARAM_TEXT, 'idnumber'), 'description' => new external_value(PARAM_TEXT, 'description'), )), 'cohorts to create')));
    }

    public static function zilink_create_cohorts($cohorts) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/cohort/lib.php");
        require_once($CFG->libdir . '/completionlib.php');

        ZiLinkLoadDefaults();

        $params = self::validate_parameters(self::zilink_create_cohorts_parameters(), array('cohorts' => $cohorts));

        if ($CFG->zilink_cohort_auto_create == 1) {
            $transaction = $DB->start_delegated_transaction();
            foreach ($params['cohorts'] as $cohort) {

                $mdlcohort = $DB->get_record('cohort', array('idnumber' => $cohort['idnumber'], 'component' => 'enrol_zilink'));
                if ($mdlcohort) {
                    if ($mdlcohort->name <> $cohort['name']) {
                        if (!$mdlcohortcheck = $DB->get_record('cohort', array('name' => $cohort['name'], 'component' => 'enrol_zilink'))) {
                            $mdlcohort->name = $cohort['name'];
                            $DB->update_record('cohort', $mdlcohort);
                        }
                    }

                } else {
                    $mdlcohort = $DB->get_record('cohort', array('name' => $cohort['name'], 'component' => 'enrol_zilink'));
                    if ($mdlcohort) {
                        if ($mdlcohort->idnumber <> $cohort['idnumber']) {
                            $mdlcohort->idnumber = $cohort['idnumber'];
                            $DB->update_record('cohort', $mdlcohort);

                        }
                        
                    } else {
                        $coh = new StdClass();
                        $coh->name = $cohort['name'];
                        $coh->idnumber = $cohort['idnumber'];
                        $coh->description = $cohort['description'];
                        $coh->contextid = get_system_context()->id;
                        $coh->component = 'enrol_zilink';
                        $cohort['id'] = cohort_add_cohort($coh);

                    }
                }
                $resultcohorts[] = array('id' => $mdlcohort->id, 'idnumber' => $cohort['idnumber'], 'name' => $cohort['name'], 'description' => $cohort['description']);
            }
            $transaction->allow_commit();
        } else {
            $resultcohorts = $cohorts;
        }

        return $resultcohorts;

    }

    public static function zilink_create_cohorts_returns() {
        return new external_multiple_structure(new external_single_structure( array('name' => new external_value(PARAM_TEXT, 'name'), 'idnumber' => new external_value(PARAM_TEXT, 'idnumber'), 'description' => new external_value(PARAM_TEXT, 'description'), ), 'cohorts'));
    }

    public static function zilink_delete_cohorts_parameters() {
        return new external_function_parameters( array('options' => new external_single_structure( array('ids' => new external_multiple_structure(new external_value(PARAM_INT, 'Cohort id'), 'List of cohort id. If empty return all cohort
                                            except front page course.')), 'options - operator OR is used', VALUE_DEFAULT, array())));
    }

    public static function zilink_delete_cohorts($options) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/cohort/lib.php");

        $params = self::validate_parameters(self::zilink_delete_cohorts_parameters(), array('options' => $options));
        $cohorts = $DB->get_records_list('cohort', 'id', $params['options']['ids']);
        $cohortlist = array();
        foreach ($cohorts as $cohort) {
            cohort_delete_cohort($cohort);
            $cohortlist[] = array('id' => $cohort->id, 'idnumber' => $cohort->idnumber, 'name' => $cohort->name);
        }
        return $cohortlist;
    }

    public static function zilink_delete_cohorts_returns() {
        return new external_multiple_structure(new external_single_structure( array('id' => new external_value(PARAM_TEXT, 'cohort id'), 'idnumber' => new external_value(PARAM_TEXT, 'cohort idnumber'), 'name' => new external_value(PARAM_TEXT, 'cohort name'), ), 'cohort'));
    }

    public static function zilink_sort_categories_parameters() {
        return new external_function_parameters( array());
    }

    public static function zilink_sort_categories() {
        global $CFG, $DB;
        
        require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->libdir . '/completionlib.php');
        require_once($CFG->libdir . '/coursecatlib.php');

        ZiLinkLoadDefaults();

        if (!isset($CFG->zilink_category_sorting)) {
            $CFG->zilink_category_sorting = 0;
        }

        if($CFG->zilink_category_sorting == 0 || $CFG->dbtype == 'mssql' || $CFG->dbtype == 'sqlsrv' || $CFG->dbtype == 'pgsql') {
            $success = array();
            $success[] = array('success' => 1);
            return $success;
        }

        $transaction = $DB->start_delegated_transaction();

        $cat = coursecat::get($CFG->zilink_category_root,MUST_EXIST,true);
        $categories = $cat->get_children(array( 'sort' => array('name' => 1)));
        
        //if ($categories = get_categories($CFG->zilink_category_root, "name ASC", 'c.id, c.name, c.sortorder')) {
        if($categories) {
            $sql = 'SELECT MAX(sortorder) AS max, 1 FROM {course_categories} WHERE parent = ?';
            $catcount = $DB->get_records_sql($sql, array($CFG->zilink_category_root));
            $catcount = $catcount->max + 100;

            foreach ($categories as $category) {
                if ($CFG->dbtype == 'pgsql') {
                    $sql = 'SELECT     id, '.
                           '         name, '.
                           '         sortorder, '.
                           '         CASE      WHEN (substr(name, strpos(name, \' \' ")+1) ~ :q) '.
                           '                 THEN CASE    WHEN(length(substr(name, strpos(name, \' \')+1)) = 2) '.
                           '                             THEN cast(substr(name, strpos(name, \' \')+1) as text) '.
                           '                             ELSE cast(text(0) || substr(name, strpos(name, \' \')+1) as text) END '.
                           '                 ELSE text(999999) END as year_number '.
                           '         FROM {course_categories} '.
                           '         WHERE parent = '. $category->id .
                           '         ORDER BY year_number asc;';

                } else if ($CFG->dbtype == 'mssql' || $CFG->dbtype == 'sqlsrv') {
                    $success = array();
                    $success[] = array('success' => 1);
                    return $success;
                } else {
                    $sql = 'SELECT '.
                           '     id, '.
                           '     name,  '.
                           '     sortorder, '.
                           '     (CASE  '.
                           '         SUBSTRING_INDEX(name, \' \', -1) + 0 '.
                           '         WHEN 0  '.
                           '         THEN (CASE  '.
                           '                 SUBSTRING_INDEX(SUBSTRING_INDEX(name, \' \', 2), \' \', -1) REGEXP :q '.
                           '                 WHEN TRUE  '.
                           '                 THEN SUBSTRING_INDEX(SUBSTRING_INDEX(name, \' \', 2), \' \', -1) '.
                           '                 ELSE 999999 END)  '.
                           '         ELSE SUBSTRING_INDEX(name, \' \', -1) + 0 END) '.
                           '     as year_number FROM {course_categories} WHERE parent = ' . $category->id . ' ORDER BY year_number+0 asc';
                }

                if ($subcategories = $DB->get_records_sql($sql, array('q' => '\'^-?[0-9]+$\''))) {

                    $sql = 'SELECT MAX(sortorder) AS max, 1 FROM {course_categories} WHERE parent = ?';
                    $subcatcount = $DB->get_record_sql($sql, array($category->id));
                    $subcatcount = $subcatcount->max + 100;

                    foreach ($subcategories as $subcategory) {
                        if ($CFG->zilink_course_sorting) {
                            
                            $sc = coursecat::get($subcategory->id,MUST_EXIST,true);
                            //$sc->get_courses(array('sort' => array('fullname' => 1)));
                            //if ($subcategorycourses = get_courses($subcategory->id, "fullname ASC", 'c.id, c.fullname, c.sortorder')) {
                            if ($subcategorycourses = $sc->get_courses(array('sort' => array('fullname' => 1)))) {
                                $sql = 'SELECT MAX(sortorder) AS max, 1 FROM {course} WHERE category = ?';
                                $count = $DB->get_record_sql($sql, array($subcategory->id));
                                $count = $count->max + 100;

                                foreach ($subcategorycourses as $subcategory_course) {
                                    $DB->set_field('course', 'sortorder', $count, array('id' => $subcategory_course->id));
                                    $count++;
                                }
                            }
                        }
                        $DB->set_field('course_categories', 'sortorder', $subcatcount, array('id' => $subcategory->id));
                        $subcatcount++;
                    }

                    if ($CFG->dbtype == 'pgsql') {
                        $sql = 'SELECT     id, '.
                               '         fullname, '.
                               '         sortorder, '.
                               '         CASE      WHEN (substr(fullname, strpos(fullname, \' \')+1) ~ :q) '.
                               '                 THEN CASE    WHEN(length(substr(fullname, strpos(fullname, \' \')+1)) = 2) '.
                               '                             THEN cast(substr(fullname, strpos(fullname, \' \')+1) as text) '.
                               '                             ELSE cast(text(0) || substr(fullname, strpos(fullname, \' \')+1) as text) END '.
                               '                 ELSE text(999999) END as year_number '.
                               '         FROM {course} '.
                               '         WHERE category = ' . $category->id .
                               '         ORDER BY year_number asc;';

                    } else if ($CFG->dbtype == 'mssql' || $CFG->dbtype == 'sqlsrv') {
                        $success = array();
                        $success[] = array('success' => 1);
                        return $success;
                    } else {
                        $sql = 'SELECT id, '.
                               '       fullname, '.
                               '       sortorder, '.
                               '       (CASE '.
                               '               SUBSTRING_INDEX(fullname, \' \', -1) + 0 '.
                               '               WHEN 0 '.
                               '               THEN (CASE '.
                               '                  SUBSTRING_INDEX(SUBSTRING_INDEX(fullname, \' \', 2), \' \', -1) REGEXP :q '.
                               '                  WHEN TRUE '.
                               '                  THEN SUBSTRING_INDEX(SUBSTRING_INDEX(fullname, \' \', 2), \' \', -1) '.
                               '                  ELSE 999999 END) '.
                               '               ELSE SUBSTRING_INDEX(fullname, \' \', -1) + 0 END) '.
                               '    as year_number FROM {course} WHERE category = " . $category->id . " ORDER BY year_number+0 asc ';
                    }

                    if ($CFG->zilink_course_sorting) {
                        if ($courses = $DB->get_records_sql($sql, array('q' => '\'^-?[0-9]+$\''))) {

                            $coursecount = $DB->get_record_sql('SELECT MAX(sortorder) AS max, 1 FROM ' . $CFG->prefix . 'course WHERE category = ' . $category->id);
                            $coursecount = $count->max + 100;

                            foreach ($courses as $course) {
                                $DB->set_field('course', 'sortorder', $coursecount, array('id' => $subcategory->id));
                                $coursecount++;
                            }
                        }
                    }
                }

                $DB->set_field('course_categories', 'sortorder', $catcount, array('id' => $category->id));
                $catcount++;
            }
            $transaction->allow_commit();
            build_context_path(true);
        }

        $success = array();
        $success[] = array('success' => 1);
        return $success;
    }

    public static function zilink_sort_categories_returns() {
        return new external_multiple_structure(new external_single_structure( array('success' => new external_value(PARAM_TEXT, 'success'), )));
    }

    public static function zilink_enrol_cohort_student_parameters() {
        return new external_function_parameters(
                        array('enrolments' => new external_multiple_structure(
                                            new external_single_structure( array('useridnumber' => new external_value(PARAM_TEXT, 'user idnumber'),
                                                                            'cohortidnumber' => new external_value(PARAM_TEXT, 'cohort id'), )))));
    }

    public static function zilink_enrol_cohort_student($enrolments) {
        global $DB, $CFG;

        require_once($CFG->dirroot . "/cohort/lib.php");

        $params = self::validate_parameters(self::zilink_enrol_cohort_student_parameters(), array('enrolments' => $enrolments));

        $enrolmentlist = array();
        $transaction = $DB->start_delegated_transaction();

        $usercount = 0;
        $cohortcount = 0;
        foreach ($params['enrolments'] as $enrolment) {

            $users = $DB->get_records('user', array('idnumber' => $enrolment['useridnumber']));
            $cohorts = $DB->get_records('cohort', array('idnumber' => $enrolment['cohortidnumber']));

            if ($users) {
                foreach ($users as $user) {

                    if ($cohorts) {
                        foreach ($cohorts as $cohort) {
                            cohort_add_member($cohort->id, $user->id);
                        }
                    } 
                }
                $enrolmentlist[] = array('cohortidnumber' => $enrolment['cohortidnumber'], 'useridnumber' => $enrolment['useridnumber']);
            } else {
                
                $enrolmentlist[] = array('cohortidnumber' => $enrolment['cohortidnumber'], 'useridnumber' => $enrolment['useridnumber']);
            }
        }
        $transaction->allow_commit();
        return $enrolmentlist;
    }

    public static function zilink_enrol_cohort_student_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array('useridnumber' => new external_value(PARAM_TEXT, 'user idnumber'),
                        'cohortidnumber' => new external_value(PARAM_TEXT, 'cohort idnumber'), )));
    }

    public static function zilink_unenrol_cohort_student_parameters() {
        return new external_function_parameters(
                        array('enrolments' => new external_multiple_structure(
                        new external_single_structure(
                                    array('cohortidnumber' => new external_value(PARAM_TEXT, 'cohort id'),
                                            'useridnumber' => new external_value(PARAM_TEXT, 'user idnumber'))))));
    }

    public static function zilink_unenrol_cohort_student($enrolments) {
        global $DB, $CFG;

        require_once($CFG->dirroot . "/cohort/lib.php");

        $params = self::validate_parameters(self::zilink_unenrol_cohort_student_parameters(), array('enrolments' => $enrolments));

        $transaction = $DB->start_delegated_transaction();
        $enrolmentlist = array();

        foreach ($enrolments as $enrolment) {

            $users = $DB->get_records('user', array('idnumber' => $enrolment['useridnumber']));

            foreach ($users as $user) {
                $cohorts = $DB->get_records('cohort', array('idnumber' => $enrolment['cohortidnumber']));

                foreach ($cohorts as $cohort) {
                    cohort_remove_member($cohort->id, $user->id);
                }
            }
            $enrolmentlist[] = array('useridnumber' => $enrolment['useridnumber'], 'cohortidnumber' => $enrolment['cohortidnumber']);
        }
        $transaction->allow_commit();
        return $enrolmentlist;
    }

    public static function zilink_unenrol_cohort_student_returns() {
        return new external_multiple_structure(new external_single_structure(
                                    array('useridnumber' => new external_value(PARAM_TEXT, 'course id'),
                                    'cohortidnumber' => new external_value(PARAM_TEXT, 'course idnumber'), )));
    }

    public static function zilink_get_cohort_enrolments_parameters() {
        return new external_function_parameters(
                    array('options' => new external_single_structure(
                                array('ids' => new external_multiple_structure(new external_value(PARAM_INT, 'Category id'),
                                'List of category idnumbers. If empty return all categories except front page course.', VALUE_OPTIONAL)),
                                'options - operator OR is used', VALUE_DEFAULT, array())));
    }

    public static function zilink_get_cohort_enrolments($enrolments) {
        global $DB, $CFG;

        $sql = 'SELECT u.idnumber as useridnumber, c.idnumber as cohortidnumber '.
               ' FROM {cohort} c '.
               ' JOIN {cohort_members} cm  ON c.id = cm.cohortid '.
               ' JOIN {user} u ON u.id = cm.userid '.
               ' WHERE c.component = ? ';

        $enrolments = $DB->get_recordset_sql($sql, array('enrol_zilink'));
        $enrolmentlist = array();

        if ($enrolments->valid()) {
            foreach ($enrolments as $enrolment) {
                if (strlen($enrolment->useridnumber) == 32 && strlen($enrolment->cohortidnumber) == 32) {
                    if (!in_array($enrolment->cohortidnumber, array('00000000000000000000000000000000',
                                                                    '00000000000000000000000000000001',
                                                                    '00000000000000000000000000000002'))) {
                        $enrolmentlist[] = array('cohortidnumber' => $enrolment->cohortidnumber, 'useridnumber' => $enrolment->useridnumber);
                    }
                }
            }
            return $enrolmentlist;
        }
        return $enrolmentlist;
    }

    public static function zilink_get_cohort_enrolments_returns() {
        return new external_multiple_structure(
                        new external_single_structure(
                                    array('useridnumber' => new external_value(PARAM_TEXT, 'course id'),
                                          'cohortidnumber' => new external_value(PARAM_TEXT, 'course idnumber'), )));
    }
    
    
    public static function zilink_get_student_guardian_links_parameters() {
        return new external_function_parameters(
                    array('options' => new external_single_structure(
                                array('ids' => new external_multiple_structure(new external_value(PARAM_INT, 'Category id'),
                                'List of category idnumbers. If empty return all categories except front page course.', VALUE_OPTIONAL)),
                                'options - operator OR is used', VALUE_DEFAULT, array())));
    }

    public static function zilink_get_student_guardian_links($enrolments) {
        global $DB, $CFG;


        $guardianrole = $DB->get_record('role', array('shortname' => 'zilink_guardians'), '*', MUST_EXIST );
        $guardianrestictedrole = $DB->get_record('role', array('shortname' => 'zilink_guardians_restricted'), '*', MUST_EXIST );
        
        $sql = 'SELECT c.instanceid as id, u.idnumber as studentidnumber, g.idnumber as guardianidnumber '.
                'FROM '.
                '    {role_assignments} ra, '.
                '    {context} c, '.
                '    {user} u, '.
                '    {user} g '.
                'WHERE '.
                '      ra.roleid IN ( '.$guardianrole->id .','. $guardianrestictedrole->id .') '.
                'AND   ra.contextid = c.id '.
                'AND   c.instanceid = u.id '.
                'AND   ra.userid = g.id '.
                'AND   c.contextlevel = '.CONTEXT_USER;

        $links = $DB->get_recordset_sql($sql, array(null));
        $linkslist = array();

        if ($links->valid()) {
            foreach ($links as $link) {
                
                if (strlen($link->studentidnumber) == 32 && strlen($link->guardianidnumber) == 32) {
                    $linkslist[] = array('guardianidnumber' => $link->guardianidnumber, 'studentidnumber' => $link->studentidnumber);
                }
            }
            return $linkslist;
        }
        return $linkslist;
    }

    public static function zilink_get_student_guardian_links_returns() {
        return new external_multiple_structure(
                        new external_single_structure(
                                    array('studentidnumber' => new external_value(PARAM_TEXT, 'student idnumber'),
                                          'guardianidnumber' => new external_value(PARAM_TEXT, 'guardian idnumber'), )));
    }
    
    public static function zilink_remove_student_guardian_links_parameters() {
        return new external_function_parameters(
                        array('enrolments' => new external_multiple_structure(
                        new external_single_structure(
                                    array('studentidnumber' => new external_value(PARAM_TEXT, 'student idnumber'),
                                          'guardianidnumber' => new external_value(PARAM_TEXT, 'guardian idnumber'))))));
    }

    public static function zilink_remove_student_guardian_links($links) {
        global $DB, $CFG;

        $params = self::validate_parameters(self::zilink_remove_student_guardian_links_parameters(), array('enrolments' => $enrolments));

        $guardianrole = $DB->get_record('role', array('shortname' => 'zilink_guardians'));
        $guardianrolerestricted = $DB->get_record('role', array('shortname' => 'zilink_guardians_restricted'));
            
        $enrolmentlist = array();

        foreach ($links as $link) {

            $students = $DB->get_records('user', array('idnumber' => $link['studentidnumber']));
            $guardians = $DB->get_records('user', array('idnumber' => $link['guardianidnumber']));
        
            foreach ($students as $student) {
                
                $context = context_user::instance($student->id);
                
                foreach ($guardians as $guardian) {
                    role_unassign($guardianrole->id, $guardian->id , $context->id, 'auth_zilink_guardian');
                    role_unassign($guardianrolerestricted->id, $guardian->id , $context->id, 'auth_zilink_guardian');
                }
            }
        }
        return $links;

    }

    public static function zilink_remove_student_guardian_links_returns() {
        return new external_multiple_structure(new external_single_structure(
                                    array('studentidnumber' => new external_value(PARAM_TEXT, 'course id'),
                                    'guardianidnumber' => new external_value(PARAM_TEXT, 'course idnumber'), )));
    }

    public static function zilink_set_user_timetable_parameters() {
        return new external_function_parameters( array('timetables' => new external_multiple_structure(new external_single_structure( array('useridnumber' => new external_value(PARAM_TEXT, 'useridnumber'), 'xml' => new external_value(PARAM_TEXT, 'xml'), )))));
    }

    public static function zilink_set_user_timetable($timetables) {
        global $DB, $CFG;

        require_once($CFG->dirroot . "/cohort/lib.php");

        $params = self::validate_parameters(self::zilink_set_user_timetable_parameters(), array('timetables' => $timetables));

        $transaction = $DB->start_delegated_transaction();

        $timetablelist = array();

        foreach ($params['timetables'] as $timetable) {
            $sql = "select * from {zilink_user_data} where " . $DB->sql_compare_text('user_idnumber') . " = '" . $timetable['useridnumber'] . "'";
            if ($record = $DB->get_record_sql($sql, null)) {
                $DB->set_field('zilink_user_data', 'timetable', base64_decode($timetable['xml']), array('id' => $record->id));
            } else {
                $tt = new stdClass();
                $tt->user_idnumber = $timetable['useridnumber'];
                $tt->timetable = base64_decode($timetable['xml']);
                $DB->insert_record('zilink_user_data', $tt);
            }
            $timetablelist[] = array('useridnumber' => $timetable['useridnumber']);
        }

        $transaction->allow_commit();
        purge_all_caches();
        return $timetablelist;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function zilink_set_user_timetable_returns() {
        return new external_multiple_structure(new external_single_structure( array('useridnumber' => new external_value(PARAM_TEXT, 'user id'), )));
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function zilink_get_teacher_enrolments_parameters() {
        return new external_function_parameters(
                        array('options' => new external_single_structure(
                                        array('idnumbers' => new external_multiple_structure(
                                                    new external_value(PARAM_INT, 'Course idnumber'),
                                                    'List of course idnumber. If empty return all courses except front page course.', VALUE_OPTIONAL)),
                                                    'options - operator OR is used', VALUE_DEFAULT, array())));
    }

    /**
     * Get Teacher Enrolmentscourses
     * @param array $options
     * @return array
     */
    public static function zilink_get_teacher_enrolments($options) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::zilink_get_teacher_enrolments_parameters(), array('options' => $options));

        $sql =  'SELECT u.id, u.idnumber as useridnumber , c.idnumber as courseidnumber '.
                'FROM    {role_assignments} ra,  '.
                '    {context} ct, '.
                '    {course} c, '.
                '    {user} u '.
                'WHERE   ra.roleid = :roleid '. 
                'AND     ct.contextlevel = 50  '.
                'AND     ct.id = ra.contextid  '.
                'AND     ct.instanceid = c.id '.
                'AND     ra.userid = u.id  '.
                'AND     c.idnumber <> \'\'    '.
                //'AND     (ra.component = \'enrol_zilink\' OR ra.component = \'\') ';
                'AND     (ra.component = \'enrol_zilink\') ';

        $enrolmentlist = array();

        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
        if ($DB->count_records('zilink_cohort_teachers') < 1) {
            return $enrolmentlist;
        }

        $enrolments = $DB->get_recordset_sql($sql, array('roleid' => $role->id));

        if ($enrolments->valid()) {

            foreach ($enrolments as $enrolment) {
                if (strlen($enrolment->courseidnumber) == 32 && strlen($enrolment->useridnumber) == 32) {
                    $enrolmentlist[] = array('useridnumber' => $enrolment->useridnumber, 'courseidnumber' => $enrolment->courseidnumber);
                }
            }
        }
/*
        $sql = 'SELECT zct.id as id, u.idnumber as useridnumber, c.idnumber as courseidnumber '.
               'FROM    {zilink_cohort_teachers} zct, '.
               '         {user} u, '.
               '         {cohort} c '.
               ' WHERE zct.userid = u.id '.
               ' AND zct.cohortid = c.id';

        $enrolments = $DB->get_records_sql($sql, array(null));

        if ($enrolments) {
            foreach ($enrolments as $enrolment) {
                if (strlen($enrolment->courseidnumber) == 32 && strlen($enrolment->useridnumber) == 32) {
                    $enrolmentlist[] = array('useridnumber' => $enrolment->useridnumber, 'courseidnumber' => $enrolment->courseidnumber);
                }
            }
        }
*/
        return $enrolmentlist;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function zilink_get_teacher_enrolments_returns() {
        return new external_multiple_structure(
                            new external_single_structure( 
                                            array( 'useridnumber' => new external_value(PARAM_TEXT, 'user idnumber'),
                                                    'courseidnumber' => new external_value(PARAM_TEXT, 'course idnumber'),
                        ), 'teacher_enrolments'));
    }

    public static function zilink_set_user_data_parameters() {
        return new external_function_parameters( array('data' => new external_multiple_structure(new external_single_structure( array('useridnumber' => new external_value(PARAM_TEXT, 'useridnumber'), 'type' => new external_value(PARAM_TEXT, 'type'), 'xml' => new external_value(PARAM_RAW, 'xml'), )))));
    }

    public static function zilink_set_user_data($data) {
        global $DB, $CFG;

        $params = self::validate_parameters(self::zilink_set_user_data_parameters(), array('data' => $data));
        $transaction = $DB->start_delegated_transaction();
        $list = array();

        
        $count = 0;
        foreach ($params['data'] as $dataitem) {
            $sql = "select * from {zilink_user_data} where " . $DB->sql_compare_text('user_idnumber') . " = '" . $dataitem['useridnumber'] . "'";
            if ($record = $DB->get_record_sql($sql, null)) {
                $record->{$dataitem['type']} = $dataitem['xml'];
                $record->timemodified = strtotime("now");

                $DB->update_record('zilink_user_data', $record);
            } else {
                $record = new stdClass();
                $record->user_idnumber = $dataitem['useridnumber'];
                $record->{$dataitem['type']} = $dataitem['xml'];
                $record->timemodified = strtotime("now");
                $DB->insert_record('zilink_user_data', $record);
            }
            if ($count == 100) {
                $transaction->allow_commit();
                $transaction = $DB->start_delegated_transaction();
                $count = 0;
            }
            $count++;
            $list[] = array('useridnumber' => $dataitem['useridnumber']);
        }
        $transaction->allow_commit();
        purge_all_caches();
        return $list;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function zilink_set_user_data_returns() {
        return new external_multiple_structure(new external_single_structure( array('useridnumber' => new external_value(PARAM_TEXT, 'user id'), )));
    }

    public static function zilink_set_global_data_parameters() {
        return new external_function_parameters(
                        array('data' => new external_multiple_structure(
                                new external_single_structure( array('type' => new external_value(PARAM_TEXT, 'type'),
                                            'xml' => new external_value(PARAM_RAW, 'xml'), )))));
    }

    public static function zilink_set_global_data($data) {
        global $DB, $CFG;

        $params = self::validate_parameters(self::zilink_set_global_data_parameters(), array('data' => $data));
        $transaction = $DB->start_delegated_transaction();
        $list = array();

        foreach ($params['data'] as $dataitem) {
            $sql = "select * from {zilink_global_data} where " . $DB->sql_compare_text('setting') . " = '" . $dataitem['type'] . "'";
            if ($record = $DB->get_record_sql($sql, null)) {
                $DB->set_field('zilink_global_data', 'value', base64_decode($dataitem['xml']), array('id' => $record->id));
            } else {
                $record = new stdClass();
                $record->setting = $dataitem['type'];
                $record->value = base64_decode($dataitem['xml']);

                $DB->insert_record('zilink_global_data', $record);
            }
            $list[] = array('type' => $dataitem['type']);
        }
        
        

        $transaction->allow_commit();
        purge_all_caches();
        
        return $list;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function zilink_set_global_data_returns() {
        return new external_multiple_structure(new external_single_structure( array('type' => new external_value(PARAM_TEXT, 'type'), )));
    }

    public static function zilink_get_versions_parameters() {
        return new external_function_parameters(
                            array('options' => new external_single_structure(
                                            array('idnumbers' => new external_multiple_structure(
                                                    new external_value(PARAM_INT, 'Course idnumber'), 
                                                            'List of course idnumber. If empty return all courses except front page course.',
                                                             VALUE_OPTIONAL)), 'options - operator OR is used', VALUE_DEFAULT, array())));
    }

    public static function zilink_get_versions($options) {
        global $CFG, $DB;

        $version = array();

        $packageversionfile = "$CFG->dirroot/local/zilink/package_version.php";
        $package = new stdClass();
        include($packageversionfile);

        $versionfile = "$CFG->dirroot/enrol/zilink/version.php";
        $plugin = new stdClass();
        include($versionfile);
        $enrol = $plugin;

        $versionfile = "$CFG->dirroot/enrol/zilink_cohort/version.php";
        $plugin = new stdClass();
        include($versionfile);
        $enrolcohort = $plugin;

        $versionfile = "$CFG->dirroot/enrol/zilink_guardian/version.php";
        $plugin = new stdClass();
        include($versionfile);
        $enrolguardian = $plugin;

        $versionfile = "$CFG->dirroot/blocks/zilink/version.php";
        $plugin = new stdClass();
        include($versionfile);
        $block = $plugin;
        $versionfile = "$CFG->dirroot/auth/zilink_guardian/version.php";
        $plugin = new stdClass();
        include($versionfile);
        $zilinkguardian = $plugin;

        $version[] = array('type' => 'moodle', 'version' => 'Moodle version is ' . $CFG->release);
        $version[] = array( 'type' => 'zilink_package',
                            'version' => 'ZiLink - Package version is ' . $package->release);
        $version[] = array('type' => 'zilink_auth_guardian',
                            'version' => 'ZiLink - Guardian Authenication version is ' .
                                        $zilinkguardian->release . ' (Build: ' . $zilinkguardian->version . ')');
        $version[] = array('type' => 'zilink_enrol',
                            'version' => 'ZiLink - Enrolment version is ' .
                                            $enrol->release . ' (Build: ' . $enrol->version . ')');
        $version[] = array('type' => 'zilink_enrol_cohort',
                            'version' => 'ZiLink - Cohort Enrolment version is ' .
                                        $enrolcohort->release . ' (Build: ' . $enrolcohort->version . ')');
        $version[] = array('type' => 'zilink_enrol_guardian',
                            'version' => 'ZiLink - Guardian Access version is ' .
                                            $enrolguardian->release . ' (Build: ' . $enrolguardian->version . ')');
        $version[] = array('type' => 'zilink_block',
                            'version' => 'ZiLink - Super Block version is ' .
                                            $block->release . ' (Build: ' . $block->version . ')');

        return $version;
    }

    public static function zilink_get_versions_returns() {
        return new external_multiple_structure(
                    new external_single_structure(
                            array('type' => new external_value(PARAM_TEXT, 'type'),
                            'version' => new external_value(PARAM_TEXT, 'verion'), ), 'version'));
    }

}

function zilinkGetYear($value) {
    $year = null;
    if (is_numeric(substr($value, 0, 2))) {
        $year = (int)substr($value, 0, 2);
        if ($year > 14) {
            if (is_numeric(substr($value, 0, 1))) {
                $year = (int)substr($value, 0, 1);
            }
        }
    } else if (is_numeric(substr($value, 0, 1))) {
        $year = (int)substr($value, 0, 1);
    }
    return $year;
}

if (!class_exists('zilink_local_simple_xml_extended')) {

    class zilink_local_simple_xml_extended extends SimpleXMLElement {
        public function Attribute($name) {
            foreach ($this->Attributes() as $key => $val) {
                if ($key == $name) {
                    return (string)$val;
                }
            }
            return '';
        }
    }

}