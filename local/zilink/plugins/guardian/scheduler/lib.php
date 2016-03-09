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
 * Defines functions for use in the block.
 *
 * Defines {@see parenteseve_print_schedule()}, {@see parentseve_get_teachers()},
 * {@see parentseve_isteacher()} and {@see parentseve_search_filter()}.
 *
 * @package block_parentseve
 * @author Mark Johnson <johnsom@tauntons.ac.uk>, Mike Worth <mike@mike-worth.com>
 * @copyright Copyright &copy; 2009, Taunton's College, Southampton, UK
 */

/**
 * Prints a schedule for the teacher specified
 *
 * Prints out a {@see flexible_table} containing a list of all possible appointments for a teacher,
 * with student and parents names for those appointments that have been booked.
 *
 * @param object $teacher the user object for the teacher
 * @param object $session The record for the parents' evening
 * @return boolean was a schedule printed sucessfully? Will return false if teacher has no
 *                 appointments booked
 */
 
require_once(dirname(dirname(dirname(__FILE__))).'/core/data.php');
require_once(dirname(dirname(dirname(__FILE__))).'/core/person.php');
require_once(dirname(dirname(dirname(__FILE__))).'/core/base.php');
 
 
function session_get_schedule($teacher, $session, $id) {
    global $DB;

    $sql = 'SELECT *
            FROM {zilink_guardian_sched_app}
            WHERE teacherid = ?
                AND sessionid = ?
            ORDER BY apptime';
    return $DB->get_records_sql($sql, array($teacher->id, $session->id));
}


/**
 * Get list of teachers for a particular parent's evening
 *
 * If the parents' evening exists and has some teachers defined, returns the user IDs of all 
 * the teachers for the parents' evening.
 *
 * @param object $session The record for the requires parents' evening
 * @return array array of user objects containing only ids, firstnames and lastnames
 */

function session_get_teachers($session) {
    global $DB;

    $select = 'SELECT u.* ';
    $from = 'FROM {zilink_guardian_sched_tch} AS t
            JOIN {user} AS u ON t.userid = u.id ';
    $where = 'WHERE t.sessionid = ? ';
    $order = 'ORDER BY firstname, lastname ASC';
    $params = array($session->id);
    if ($teachers = $DB->get_records_sql($select.$from.$where.$order, $params)) {
        return $teachers;
    } else {
        return array();
    }

}

/**
 * is the supplied user on the list of teachers for a particular parents evening?
 *
 * @param int $userid the id of the user to check
 * @param object $session the record for the specified parents' evening
 * @return bool is the user a teacher on the list?
 */

function session_isteacher($userid, $session) {
    global $DB;

    $params = array('sessionid' => $session->id, 'userid' => $userid);
    return $DB->record_exists('zilink_guardian_sched_tch', $params);
}

/**
 * Does a given string exist in a user's name
 *
 * Used in {@see edit.php} to filter search results for the teacher selection form.
 * Checks to see if the search text occurs within the user's full name (case insensitively).
 *
 * @param object $user The user object containing at least a firstname and lastname attribute
 * @global $searchtext
 * @return bool True if the search text occurs in the user's name, otherwise false.
 *
 */
function session_search_filter($user) {
    global $searchtext;
    return stristr(fullname($user), $searchtext);
}


/**
 * Defines the user selectors for selecting users as teachers for a parents' evening
 *
 * @package block_parentseve
 * @author Mark Johnson <johnsom@tauntons.ac.uk>, Mike Worth <mike@mike-worth.com>
 * @copyright Copyright &copy; 2009, Taunton's College, Southampton, UK
 **/

require_once($CFG->dirroot.'/user/selector/lib.php');

/**
 * User Selector for selecting users to be teachers
 *
 * @uses user_selector_base
 */
class session_teacher_selector extends user_selector_base {
    protected $session;

    /**
     * Constructor, sets name, options and stores Parents' Evening record
     *
     * @param $name string Unique name for the selector
     * @param $session object The database record for the Parents' Evening
     * @param $options array Additional options for the selector
     */
    public function __construct($name, $session, $options = array()) {
        parent::__construct($name, $options);
        $this->session = $session;
    }

    /**
     * Defines the file option for AJAX calls and reutrns options
     *
     * @return array All options defined for the selector, plus the file option
     */
    protected function get_options() {
        $options = parent::get_options();
        $options['file'] = 'local/zilink/plugins/guardian/scheduler/lib.php';
        return $options;
    }

    /**
     * Gets IDs for everyone who's currently a teacher for the given parents' evening
     *
     * @global $DB
     * @return array|bool of user IDs, or false of there aren't any teachers yet
     */
    protected function get_current_teacher_ids() {
        global $DB;
        $teachers = $DB->get_records('zilink_guardian_sched_tch',
                                     array('sessionid' => $this->session->id),
                                     '',
                                     'userid, id, sessionid');
        if ($teachers) {
            return array_keys($teachers);
        }
        return false;
    }

    /**
     * Builds where clause for selecting users who are not currently selected as teachers
     *
     * @param $current_teacherids array The IDs of users who are already selected as teachers
     * @return array Where clause and parameters
     */
    protected function where_sql($current_teacherids) {
        global $DB;
        if ($current_teacherids) {
            list($in_sql, $params) = $DB->get_in_or_equal($current_teacherids,
                                                          SQL_PARAMS_QM,
                                                          'param',
                                                          false);
            $where = 'id '.$in_sql;
        } else {
            $where = '';
            $params = array();
        }

        return array($where, $params);
    }

    /**
     * Finds the users to be displayed in the list
     *
     * Gets all non-deleted users found by {@see where_sql} filtered by the search terms
     *
     * @param $search The search term entered in the form
     * @return array Multi-dimentional array of headings and users
     */
    public function find_users($search) {
        global $DB;
        $where = '';
        $params = array();
        $current_teacherids = $this->get_current_teacher_ids();
        list($where, $params) = $this->where_sql($current_teacherids);

        if ($search) {
            if (!empty($where)) {
                $where .= ' AND ';
            }
            $where .= '('.$DB->sql_like($DB->sql_concat('firstname', '" "', 'lastname'), '?');
            $where .= ' OR '.$DB->sql_like('email', '?').')';
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
        }
        
        if (!empty($where)) {
            $where .= ' AND ';
        }
        $params[] = 0;
        $where .= 'deleted = ?';
        
        if($cohort =  $DB->get_record('cohort',array('idnumber' => '00000000000000000000000000000000'))) {
            
            $teachers = $DB->get_records('cohort_members',
                                         array('cohortid' => $cohort->id),
                                         '',
                                         'userid, id');
                                         
            if($teachers)
            {
                if (!empty($where)) {
                    $where .= ' AND ';
                    
                    $params2 = array();
                    list($in_sql, $params2) = $DB->get_in_or_equal(array_keys($teachers),
                                                                  SQL_PARAMS_QM,
                                                                  'param',
                                                                   true);
                                                                   
                    $params = array_merge($params,$params2);
                    $where .= 'id '.$in_sql;
                }
            }
        }
        
        return array(get_string('guardian_scheduler_available_teachers','local_zilink') => $DB->get_records_select('user', $where, $params));
    }
}

/**
 * User selector for selecting (and removing) existing teachers from at Parents' Evening
 *
 * @uses parentseve_teacher_selector
 */
class session_selected_teacher_selector extends session_teacher_selector {

    /**
     * Builds where clause to select just existing teachers
     *
     * @param $current_teachersids array IDs of users who are already teachers
     * @return array Where clause and parameters
     */
    protected function where_sql($current_teacherids) {
        global $DB;
        if ($current_teacherids) {
            list($in_sql, $params) = $DB->get_in_or_equal($current_teacherids);
            $where = 'id '.$in_sql;
        } else {
            $where = 'id IS NULL';
            $params = array();
        }

        return array($where, $params);
    }
    
    public function find_users($search) {
        global $DB;
        $where = '';
        $params = array();
        $current_teacherids = $this->get_current_teacher_ids();
        list($where, $params) = $this->where_sql($current_teacherids);

        if ($search) {
            if (!empty($where)) {
                $where .= ' AND ';
            }
            $where .= '('.$DB->sql_like($DB->sql_concat('firstname', '" "', 'lastname'), '?');
            $where .= ' OR '.$DB->sql_like('email', '?').')';
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
        }
        
        if (!empty($where)) {
            $where .= ' AND ';
        }
        $params[] = 0;
        $where .= 'deleted = ?';
        
        if($cohort =  $DB->get_record('cohort',array('idnumber' => '00000000000000000000000000000000'))) {
            
            $teachers = $DB->get_records('cohort_members',
                                         array('cohortid' => $cohort->id),
                                         '',
                                         'userid, id');
                                         
            if($teachers)
            {
                if (!empty($where)) {
                    $where .= ' AND ';
                    
                    $params2 = array();
                    list($in_sql, $params2) = $DB->get_in_or_equal(array_keys($teachers),
                                                                  SQL_PARAMS_QM,
                                                                  'param',
                                                                   true);
                                                                   
                    $params = array_merge($params,$params2);
                    $where .= 'id '.$in_sql;
                }
            }
        }
        
        return array(get_string('guardian_scheduler_bookable_teachers','local_zilink') => $DB->get_records_select('user', $where, $params));
    }
    
}