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


$string['pluginname'] = 'ZiLink - Guardian Accounts';
$string['auth_zilink_guardiandescription'] = 'ZiLink Guardian ensures that parent/guardian accounts are securely created and assigned to students correctly and with the correct permissions.';

$string['filterstudents'] = 'Filter Students';
$string['create_filterstudents_desc'] = 'To create and link parent/guardian accounts use the filter tool to pre-select a group of students who do not have a linked parent/guardian account. Filter students by Year or House or Registration Group to pre-select groups of students.';
$string['manage_filterstudents_desc'] = 'To unlink parent/guardian accounts use the filter tool to pre-select a group of students who do not have a linked parent/guardian account. Filter students by Year or House or Registration Group to pre-select groups of students.';

$string['createaccount'] = 'Create & Link Account(s)';

$string['manageaccounts'] = 'Manage Accounts';
$string['configuration'] = 'Configuration';
$string['mailmerge'] = 'Mail Merge Export';

$string['year'] = 'Year';
$string['registration'] = 'Reg Group';
$string['house'] = 'House';
$string['actions'] = 'actions';
$string['guardians'] = 'Parents/Guardians';

$string['create_linkedaccount'] = 'Create and Link Guardian Accounts';
$string['create_students'] = 'Students without Guardian Linked Accounts';
$string['create_selectedguardians'] = 'Link Parent/Guardian Accounts';
$string['create_linkedaccount_desc'] = 'The students will be listed in the <b>Students without Guardian Linked Accounts</b> list.<br>'.
                                        'Select or multi-select the student(s) and click the <b>Select</b> button.<br>'.
                                        'The student(s) will be listed in the <b>Selected Students</b> box.<br> '.
                                        'To remove a student from the <b>Selected Students</b> box, select the student and click the <b>Remove</b> button.<br>'.
                                        '<br>'.
                                        'Click a student in the <b>Selected Students</b> box and their parent(s)/guardian(s) will be listed in the <b>Parents/Guardians</b> box below.<br>'.
                                        'Select or multi-select the parent(s)/guardian(s) and click the Select button. The parent(s)/guardian(s) will be listed in the <b>Linked Parent/Guardian Accounts</b> box.<br>'.
                                        'To remove a parent/guardian from the <b>Linked Parent/Guardian Accounts</b> box, select the parent/guardian and click the <b>Remove</b> button.<br> '.
                                        '<br>'.
                                        'Select the correct role for all parent(s)/guardian(s) in the <b>Linked Parent/Guardian Accounts</b> box and click <b>Save changes</b>.<br>'.
                                        '<br>'.
                                        '<b>IMPORTANT</b> - all the parent(s)/guardian(s) must have the same role - you cannot mix the roles and create accounts.';


$string['manage_linkedaccount'] = 'Unlink Guardian Accounts';
$string['manage_students'] = 'Students with Guardian Linked Accounts';
$string['manage_selectedguardians'] = 'Unlink Parent/Guardian Accounts';
$string['manage_linkedaccount_desc'] = 'The students will be listed in the <b>Students with Guardian Linked Accounts</b> list.<br>'.
                                        'Select or multi-select the student(s) and click the <b>Select</b> button.<br>'.
                                        'The student(s) will be listed in the <b>Selected Students</b> box.<br> '.
                                        '<br> '.
                                        'To remove a student from the <b>Selected Students</b> box <br>'.
                                        'Select the student and click the <b>Remove</b> button.<br> '.
                                        '<br>'.
                                        'Click a student in the <b>Selected Students</b> box and their parent(s)/guardian(s) will be listed in the <b>Parents/Guardians</b> box below.<br>'.
                                        'Select or multi-select the parent(s)/guardian(s) and click the Select button. The parent(s)/guardian(s) will be listed in the <b>Linked Parent/Guardian Accounts</b> box.<br>'.
                                        'To remove a parent/guardian from the <b>Linked Parent/Guardian Accounts</b> box, select the parent/guardian and click the <b>Remove</b> button.<br>'.
                                        '<br>'.
                                        'To finish click <b>Save changes</b>';



$string['selectedstudents'] = 'Selected Students';
$string['assignrole'] = 'Assign Role';

$string['export'] = 'ZiLink Guardian Account Export';

$string['merge_description'] = 'Your parents will need to be notified of their username and temporary password for their Moodle account.<br>'.
                               '<br>'.
                               'This export will produce a spreadsheet containing two tabs. One tab will contain mail merge information and the username, the other tab will contain mail merge information and the password.<br>'.
                               '<br>'.
                               'This is so that you can send separate letters to your parents/guardians, one containing their username and the other containing their password for greater security.';

$string['mandatorysettings'] = 'Mandatory Settings';
$string['optionalsettings'] = 'Optional Settings';

$string['username_prefix'] = 'Username Prefix';
$string['username_prefix_help'] = 'The username will be created automatically by ZiLink. If you wish a prefix to be added to the username include that prefix in this setting. e.g. "par_"';

$string['email_required'] = 'Email Adress Required';
$string['email_required_help'] = 'Set to <b>Yes</b> if you require the parent/guardian e-mail address to be within the school MIS records before a guardian account is created.';

$string['default_city'] = 'City';
$string['default_city_help'] = 'Add a default city for each Guardian Account user profile.';

$string['default_country'] = 'Country';
$string['default_country_help'] = 'Select a default Country for each Guardian Account user profile.';

$string['default_language'] = 'Language';
$string['default_language_help'] = 'Set the default language for each Guardian Account user profile.';