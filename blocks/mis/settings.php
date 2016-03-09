<?php
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
		
	$settings->add(new admin_setting_heading(
            'facdet',
            get_string('labelfacdet', 'block_mis'),
            get_string('descfacdet', 'block_mis')
        ));		
	$options = array('access'=>get_string('access', 'block_mis'),'mssql'=>get_string('mssql', 'block_mis'));
	$settings->add(new admin_setting_configselect(
            'mis/dbtype',
            get_string('labeldbtype', 'block_mis'),
            get_string('descdbtype', 'block_mis'),
            'mssql',
			$options
        ));
	$settings->add(new admin_setting_configtext(
            'mis/dsn',
            get_string('labeldsn', 'block_mis'),
            get_string('descdsn', 'block_mis'),
            ''
        ));	
	$settings->add(new admin_setting_configtext(
            'mis/dbq',
            get_string('labeldbq', 'block_mis'),
            get_string('descdbq', 'block_mis'),
            ''
        ));
	$settings->add(new admin_setting_configtext(
            'mis/dbuser',
            get_string('labeldbuser', 'block_mis'),
            get_string('descdbuser', 'block_mis'),
            'stud_admin'
        ));
	$settings->add(new admin_setting_configtext(
            'mis/dbpass',
            get_string('labeldbpass', 'block_mis'),
            get_string('descdbpass', 'block_mis'),
            ''
        ));	
	$settings->add(new admin_setting_configtext(
            'mis/dataset',
            get_string('labeldset', 'block_mis'),
            get_string('descdset', 'block_mis'),
            '2011/2012'
        ));	
	$settings->add(new admin_setting_configtext(
            'mis/firstyr',
            get_string('labelfirstyr', 'block_mis'),
            get_string('descfirstyr', 'block_mis'),
            '7'
        ));		
	$settings->add(new admin_setting_configtext(
            'mis/lastyr',
            get_string('labellastyr', 'block_mis'),
            get_string('desclastyr', 'block_mis'),
            '13'
        ));	
	$settings->add(new admin_setting_heading(
            'mis/pztab',
            get_string('labelpztab', 'block_mis'),
            get_string('descpztab', 'block_mis')
        ));
	$settings->add(new admin_setting_configcheckbox(
            'mis/welcome',
            get_string('labelwel', 'block_mis'),
            get_string('descwel', 'block_mis'),
            1
        ));
	$settings->add(new admin_setting_configcheckbox(
            'mis/attendance',
            get_string('labelatt', 'block_mis'),
            get_string('descatt', 'block_mis'),
            1
        ));	
	$settings->add(new admin_setting_configcheckbox(
            'mis/assessment',
            get_string('labelasse', 'block_mis'),
            get_string('descasse', 'block_mis'),
            1
        ));
	$settings->add(new admin_setting_configcheckbox(
            'mis/profile',
            get_string('labelprofi', 'block_mis'),
            get_string('descprofi', 'block_mis'),
            1
        ));			
	$settings->add(new admin_setting_configcheckbox(
            'mis/timetable',
            get_string('labeltimet', 'block_mis'),
            get_string('desctimet', 'block_mis'),
            1
        ));					
	$settings->add(new admin_setting_configcheckbox(
            'mis/calendar',
            get_string('labelcale', 'block_mis'),
            get_string('desccale', 'block_mis'),
            0
        ));
	$options = array(
		'welcome'=>get_string('welcomeTab', 'block_mis'),
		'attendance'=>get_string('attendanceTab', 'block_mis'),
		'assessment'=>get_string('assessmentTab', 'block_mis'),
		'profile'=>get_string('profileTab', 'block_mis'),
		'timetable'=>get_string('timetableTab', 'block_mis'),
		'calendar'=>get_string('calendarTab', 'block_mis')
		);
	$settings->add(new admin_setting_configselect(
            'mis/deftab',
            get_string('labeldeftab', 'block_mis'),
            get_string('descdeftab', 'block_mis'),
            'mssql',
			$options
        ));
	$settings->add(new admin_setting_heading(
            'mis/misc',
            get_string('labelmisc', 'block_mis'),
            get_string('descmisc', 'block_mis')
        ));	
	$settings->add(new admin_setting_configcheckbox(
            'mis/https',
            get_string('labelhttps', 'block_mis'),
            get_string('deschttps', 'block_mis'),
            1
        ));
							
}
?>