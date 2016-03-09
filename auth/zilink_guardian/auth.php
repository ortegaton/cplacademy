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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');

/**
 * Manual authentication plugin.
 *
 * @package    auth
 * @subpackage manual
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_plugin_zilink_guardian extends auth_plugin_base {

    public function __construct() {
        $this->authtype = 'zilink_guardian';
        $this->config = get_config('auth/zilink_guardian');
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist. (Non-mnet accounts only!)
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password) {
        global $CFG, $DB, $USER;

        if (!$username || !$password) {
               return false;
        }

        if (!$user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id))) {
            return false;
        }
        if (!validate_internal_user_password($user, $password)) {
            return false;
        } else {
            $guardianrole = $DB->get_record('role', array('shortname' => 'zilink_guardians'), '*', MUST_EXIST );
            $guardianrestictedrole = $DB->get_record('role', array('shortname' => 'zilink_guardians_restricted'), '*', MUST_EXIST );
            $sql = 'SELECT c.instanceid, c.instanceid, u.id, u.idnumber, u.firstname, u.lastname '.
                   '                      FROM {role_assignments} ra,  '.
                   '                           {context} c,  '.
                   '                           {user} u '.
                   '                      WHERE ra.userid =  '.$user->id .
                   '                      AND   ra.roleid IN ( '.$guardianrole->id .','. $guardianrestictedrole->id .') '.
                   '                      AND   ra.contextid = c.id '.
                   '                      AND   c.instanceid = u.id '.
                   '                      AND   c.contextlevel =  '.CONTEXT_USER;

            if ($DB->record_exists_sql($sql, null)) {
                return true;
            } else {
                return false;
            }

        }
        if ($password === 'changeme') {
            set_user_preference('auth_forcepasswordchange', true, $user->id);
        }
        return true;
    }

    /**
     * Updates the user's password.
     *
     * Called when the user password is updated.
     *
     * @param  object  $user        User table object
     * @param  string  $newpassword Plaintext password
     * @return boolean result
     */
    public function user_update_password($user, $newpassword) {

        global $DB;

        $user = get_complete_user_data('id', $user->id);
        $result = true;
        $cachedpassword = $DB->get_record('zilink_guardian_passwords', array('user_idnumber' => $user->idnumber));
        if (is_object($cachedpassword)) {
            $result = $DB->delete_records('zilink_guardian_passwords', array('id' => $cachedpassword->id));
        }
        if ($result) {
            $result = update_internal_user_password($user, $newpassword);
        }
        return $result;
    }

    public function prevent_local_passwords() {
        return false;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    public function is_internal() {
        return true;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    public function can_change_password() {
        return true;
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    public function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    public function can_reset_password() {
        return true;
    }
    
    /**
     * Confirm the new user as registered. This should normally not be used, 
     * but it may be necessary if the user auth_method is changed to manual
     * before the user is confirmed.
     *
     * @param string $username
     * @param string $confirmsecret
     */
    public function user_confirm($username, $confirmsecret = null) {
        global $DB;

        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->confirmed) {
                return AUTH_CONFIRM_ALREADY;
            } else {
                $DB->set_field("user", "confirmed", 1, array("id" => $user->id));
                $DB->set_field("user", "firstaccess", time(), array("id" => $user->id));
                return AUTH_CONFIRM_OK;
            }
        } else {
            return AUTH_CONFIRM_ERROR;
        }
    }
    
    function process_config($config) {
        //override if needed
        return true;
    }
}

