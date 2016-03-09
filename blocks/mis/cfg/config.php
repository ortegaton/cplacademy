<?php

require_once(dirname(__FILE__).'../../../../config.php');

/**
/* Original (c) Guy Thomas Ossett School 2007 (for Moodle > 1.9)
/* (c) Marc Coyles - Ossett Academy 2012 (for Moodle 2.2+)
/* Licence - GNU GENERAL PUBLIC LICENSE - Version 3, 29 June 2007
/* Refer to http://www.gnu.org/licenses/gpl.html for full terms
/* 
/* !SECURITY!
/* NOTE THE ACTUAL CONFIG FILES FOR YOUR SYSTEM SHOULD BE INCLUDED AS A 
/* SEPERATE FILE OUTSIDE VIRTUAL DIR / WEB ROOT FOR SECURITY REASONS  
/* (In exactly the same way your actual moodle config file should be outside virtual dir / web root)
**/

// GT MOD - Constants used to set student id type
define ('STU_UNID_ID',0); // studentid field
define ('STU_UNID_UPN',1); // upn field

/**
/* Facility Database
**/
$cfg = new stdClass();
$cfg->cmisTabPrefix='';

/**
/* Facility database non-specific details
**/
$cfg->stu_unidtype=STU_UNID_ID; // GT MOD -  set student id type to studentid

// GT ATTENDANCE MOD
// List of closure day codes (note: school day is worked out by the system)
// The valid types against each code key are weekend, inset, holiday and closed
$cfg->daycodes['WK']='weekend';
$cfg->daycodes['INS']='inset';
$cfg->daycodes['HOL']='holiday';
$cfg->daycodes['Q'] = 'holiday';
$cfg->daycodes['BNK']='holiday';
$cfg->daycodes['K']='holiday';
$cfg->daycodes['#']='closed';
$cfg->daycodes['Y']='closed';

// GT MOD - enable png graphs
$cfg->imgcharts=true;

// NOTE - you do not need to configure a database for eportal extender anymore, you just need the table to exist in moodle.
$cfg->eeDbTablePrefix=$CFG->prefix.'mis_';
$cfg->eeDbMaxCacheAge=array('days'=>4, 'hours'=>0, 'minutes'=>22); // Max cache age

$cfg->GCSE_ShortCourses=array('ci', 're'); // array of half courses

/**
/* Debugging Config
**/
$cfg->debug = false;

$cfg->title = 'Student Information';

/**
* Time table settings
*/
$cfg->tt_lecturercode=false; // if set to true, time table will show lecturer code instead of first initial surname
$cfg->tt_eventsstagger=false; // staggers events between two lanes in each day row
	
/**
/* Default Values
*/
$cfg->theme = "default";



############################################################################
#EVERYTHING BEYOND THIS POINT IS DEFINED VIA MOODLE'S BLOCK SETTINGS SYSTEM#
############################################################################



/**
/* Facility Details
**/
$cfg->cmisDBType=get_config('mis','dbtype');
$cfg->cmisDSN=get_config('mis','dsn');
$cfg->cmisDBQ=get_config('mis','dbq');
$cfg->cmisDBUser=get_config('mis','dbuser');
$cfg->cmisDBPwd=get_config('mis','dbpass');
$cfg->cmisDataSet=get_config('mis','dataset');

/**
* School Settings
*/
$cfg->firstYear=get_config('mis','firstyr'); // first year number of students at this school
$cfg->lastYear=get_config('mis','lastyr'); // last year number of students at this school

/**
* Parent Zone Tabs - set true to display and false to hide
*/
$cfg->tabs['welcome']=get_config('mis','welcome');
//$cfg->tabs['eportfolio']=false;
$cfg->tabs['attendance']=get_config('mis','attendance'); //must be true for welcome tab to work
$cfg->tabs['assessment']=get_config('mis','assessment');
$cfg->tabs['profile']=get_config('mis','profile');
$cfg->tabs['timetable']=get_config('mis','timetable');//must be true for welcome tab to work
//$cfg->tabs['targets']=false;
//$cfg->tabs['rewards']=false;
$cfg->tabs['calendar']=get_config('mis','calendar');
$cfg->defaultTab=get_config('mis','deftab');

/**
* Force parent zone urls to use https?
*/
$cfg->https=get_config('mis','https');

$CFG->mis=$cfg;

require_once($CFG->dirroot.'/lib/adodb/adodb.inc.php');
require_once($CFG->dirroot.'/blocks/mis/lib/lib_facility_db.php');
?>