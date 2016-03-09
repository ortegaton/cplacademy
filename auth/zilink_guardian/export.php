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

require_once('../../config.php');
require_once('../../lib/csvlib.class.php');
require_once(dirname(__FILE__) . '/auth.php');

require_login();

$PAGE->https_required();
$urlparams = array();
$PAGE->set_url('/auth/zilink_guardian/export.php', $urlparams);
$PAGE->verify_https_required();

if (has_capability('moodle/site:config', context_system::instance())) {
    $sql = 'SELECT u.id, u.username, u.timecreated, gp.password, ud.extended_details '.
           'FROM {zilink_guardian_passwords} gp, {user} u, {zilink_user_data} ud '.
           'WHERE u.idnumber = gp.user_idnumber '.
           'AND ud.user_idnumber = gp.user_idnumber';

    $users = $DB->get_records_sql($sql, null);

    $guardiansusername = array();
    $guardianspassword = array();

    $guardiansusername[] = array('First Name', 'Last Name', 'Salutation', 'Address', 'Username');

    $staff = array();
    $guardianspassword[] = array('First Name', 'Last Name', 'Salutation', 'Address', 'Password');

    $guardianrole = $DB->get_record('role', array('shortname' => 'zilink_guardians'), '*', MUST_EXIST);
    $guardianrestictedrole = $DB->get_record('role', array('shortname' => 'zilink_guardians_restricted'), '*', MUST_EXIST);

    if (is_array($users)) {
        foreach ($users as $user) {
            if (!$user->extended_details == null) {
                $user->details = base64_decode($user->extended_details);
                $user->details = str_replace('ns2:', '', $user->extended_details);
                $user->details = str_replace(':ns2', '', $user->extended_details);

                $record = simplexml_load_string(base64_decode($user->extended_details), 'auth_zilink_guardian_simple_xml_extended');

                if (isset($record->ContactPersonal)) {
                    $record = $record->ContactPersonal;
                } else if (isset($record->WorkforcePersonal)) {
                    $record = $record->WorkforcePersonal;
                } else {
                    continue;
                }

                if (isset($record->PersonalInformation)) {

                    if (!is_object($record->PersonalInformation->Name) && !is_object($record->PersonalInformation->Address)) {
                        continue;
                    }

                    $givenname = $record->PersonalInformation->Name->GivenName;
                    $familyname = $record->PersonalInformation->Name->FamilyName;

                    $salutation = substr($record->PersonalInformation->Name->FullName,
                                         0,
                                        strpos($record->PersonalInformation->Name->FullName, ' ')) . ' ';
                    $salutation .= substr($record->PersonalInformation->Name->GivenName, 0, 1) . ' ';
                    $salutation .= $record->PersonalInformation->Name->FamilyName;

                    $address = '';

                    if (isset($record->PersonalInformation->Address->SAON->Description)) {
                        $address .= (isset($record->PersonalInformation->Address->SAON->Description)). chr(13) . chr(10);
                    } else {
                        $address .= '';
                    }

                    if (isset($record->PersonalInformation->Address->SAON)) {
                        if (isset($record->PersonalInformation->Address->SAON->StartNumber)) {
                            $address .= $record->PersonalInformation->Address->SAON->StartNumber . chr(13) . chr(10);
                        }
                        if (isset($record->PersonalInformation->Address->SAON->EndNumber)) {
                            $address .= '-' . $record->PersonalInformation->Address->SAON->EndNumber;
                        }
                    }

                    if (isset($record->PersonalInformation->Address->PAON->Description)) {
                        $address .= (isset($record->PersonalInformation->Address->PAON->Description)). chr(13) . chr(10);
                    } else {
                        $address .= '';
                    }

                    if (isset($record->PersonalInformation->Address->PAON)) {
                        if (isset($record->PersonalInformation->Address->PAON->StartNumber)) {
                            $address .= $record->PersonalInformation->Address->PAON->StartNumber . chr(13) . chr(10);
                        }
                        if (isset($record->PersonalInformation->Address->PAON->EndNumber)) {
                            $address .= '-' . $record->PersonalInformation->Address->PAON->EndNumber;
                        }
                    }

                    if (isset($record->PersonalInformation->Address->Street)) {
                        $address .= $record->PersonalInformation->Address->Street . chr(13) . chr(10);
                    }

                    if (isset($record->PersonalInformation->Address->Locality)) {
                        $address .= $record->PersonalInformation->Address->Locality . chr(13) . chr(10);
                    }

                    if (isset($record->PersonalInformation->Address->Town)) {
                        $address .= $record->PersonalInformation->Address->Town . chr(13) . chr(10);
                    }

                    if (isset($record->PersonalInformation->Address->AdministrativeArea)) {
                        $address .= $record->PersonalInformation->Address->AdministrativeArea . chr(13) . chr(10);
                    }

                    if (isset($record->PersonalInformation->Address->AdministrativeArea)) {
                        $address .= $record->PersonalInformation->Address->AdministrativeArea . chr(13) . chr(10);
                    }

                    if (isset($record->PersonalInformation->Address->PostTown)) {
                        $address .= $record->PersonalInformation->Address->PostTown . chr(13) . chr(10);
                    }

                    if (isset($record->PersonalInformation->Address->County)) {
                        $address .= $record->PersonalInformation->Address->County . chr(13) . chr(10);
                    }

                    if (isset($record->PersonalInformation->Address->PostCode)) {
                        $address .= $record->PersonalInformation->Address->PostCode . chr(13) . chr(10);
                    }

                    $students = '';

                    $sql = 'SELECT c.instanceid, c.instanceid, u.id, u.idnumber, u.firstname, u.lastname '.
                           'FROM {role_assignments} ra, '.
                           '     {context} c, '.
                           '     {user} u '.
                           'WHERE ra.userid = '.$user->id .
                           ' AND   ra.roleid IN ('.$guardianrole->id .','.$guardianrestictedrole->id.') '.
                           ' AND   ra.contextid = c.id '.
                           ' AND   c.instanceid = u.id '.
                           ' AND   c.contextlevel = ' . CONTEXT_USER;

                    $records = $DB->get_records_sql($sql, null);

                    foreach ($records as $record) {
                        if (!empty($students)) {
                            $students .= ',';
                        }
                        $students .= trim($record->firstname) . ' ' . trim($record->lastname);
                    }

                    $guardiansusername[] = array($givenname, $familyname, $salutation, $address, $user->username, $students);
                    $guardianspassword[] = array($givenname, $familyname, $salutation, $address, $user->password, $students);
                }
            }
        }
        unset($record);
    }

    auth_zilink_guardian_data_export_xls($guardiansusername, $guardianspassword, 'ZiLink Guardian Export', 1);
}
