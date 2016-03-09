<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once(dirname(__FILE__).'/lib.php');
    global $PAGE;
    $section = $_REQUEST['section'];
    if($section == 'modsettingrmc') {
    	$PAGE->requires->css('/mod/rmc/css/rmc.css');
    }
    /* $settings->add(new admin_setting_configtext('mod_rmc_send_email',
         get_string('purchase_email', 'rmc'), get_string('purchase_email_desc', 'rmc'), '', PARAM_EMAIL));
    $settings->add(new admin_setting_configtext('mod_rmc_alfresco_url',
         get_string('alfresco_url', 'rmc'), get_string('alfresco_url_desc', 'rmc'), '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('mod_rmc_alfresco_username',
         get_string('alfresco_username', 'rmc'), get_string('alfresco_username_desc', 'rmc'), '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('mod_rmc_alfresco_password',
         get_string('alfresco_password', 'rmc'), get_string('alfresco_password_desc', 'rmc'), '', PARAM_TEXT)); 
    $settings->add(new admin_setting_configtext('mod_rmc_customer_name',
    		get_string('customer_name', 'rmc'), get_string('customer_name_desc', 'rmc'), '', PARAM_TEXT));*/
    $settings->add(new admin_setting_configtext('mod_rmc_token',
    		get_string('access_token', 'rmc'), get_string('access_token_desc', 'rmc'), '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('mod_rmc_module_helptext_id',
         get_string('rmc_help_text_id', 'rmc'), get_string('rmc_help_text_id_desc', 'rmc'), 'workspace://SpacesStore/3cb48a4f-6600-4ccd-9069-b24077c9fb55', PARAM_RAW));
    $settings->add(new admin_setting_configtext('mod_rmc_email',
    		get_string('rmc_email', 'rmc'), get_string('rmc_email_desc', 'rmc'), '', PARAM_EMAIL));
    $settings->add(new admin_setting_heading('rmcversion', '', 'Ready Made Content v3.0.1 20141111-1'));
/*    $settings->add(new admin_setting_configcheckbox('mod_rmc_module_logging',
         get_string('rmc_logging', 'rmc'), get_string('rmc_logging_desc', 'rmc'), 1));     */
}
