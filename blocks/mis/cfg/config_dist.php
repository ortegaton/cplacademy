<?php

require_once(dirname(__FILE__).'../../../../config.php');

/**
/* (c) Guy Thomas Ossett School 2007
/* Licence - GNU GENERAL PUBLIC LICENSE - Version 3, 29 June 2007
/* Refer to http://www.gnu.org/licenses/gpl.html for full terms
**/


/** 
/* !SECURITY!
/* NOTE THE ACTUAL CONFIG FILES FOR YOUR SYSTEM SHOULD BE INCLUDED AS A SEPERATE FILE OUTSIDE VIRTUAL DIR / WEB ROOT FOR SECURITY REASONS - (In exactly the same way your actual moodle config file should be outside virtual dir / web root)
**/



// GT MOD - Constants used to set student id type
define ('STU_UNID_ID',0); // studentid field
define ('STU_UNID_UPN',1); // upn field

/**
/* Facility MS Access Details
**/
/*
$cfg->cmisDBType="access";
$cfg->cmisDBQ="c:\\FacilityLocal\\admin\\facilitylocal2.mdb";
$cfg->cmisDBUser="";
$cfg->cmisDBPwd="";
$cfg->cmisDataSet='2007/2008';
$cfg->cmisTabPrefix='';
*/

/**
/* Facility MSSQL Details
**/
$cfg->cmisDBType="mssql";
$cfg->cmisDB='CMIS_ADMIN';
$cfg->cmisDBServer='server1'; // change this to CMIS db server
$cfg->cmisDBUser='user'; // change this to CMIS db username
$cfg->cmisDBPwd='password'; // change this to CMIS db password
$cfg->cmisDataSet='2007/2008'; // change this to your current dataset
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
$cfg->daycodes['K']='holiday';
$cfg->daycodes['#']='closed';
$cfg->daycodes['Y']='closed';

// GT MOD - enable png graphs
$cfg->imgcharts=true;

// GT REPORTING MOD - enable assessment labels to be converted to something more readable
// Note, all keys should be lower case
// Also Note: You only have to set this for labels you are not happy with.
$cfg->assesslabs['ks3_tut']['tutor']='Tutor Comment';
$cfg->assesslabs['ks3_tut']['hoy commen']='Head of Year Comment';
$cfg->assesslabs['ks3assess']['aut pts']=false; // false means do not display
$cfg->assesslabs['ks3assess']['spr pts']=false;
$cfg->assesslabs['ks3assess']['sum pts']=false;
$cfg->assesslabs['ks3assess']['targ pts']=false;
$cfg->assesslabs['ks4assess']['aut pts']=false;
$cfg->assesslabs['ks4assess']['spr pts']=false;
$cfg->assesslabs['ks4assess']['sum pts']=false;
$cfg->assesslabs['ks4assess']['targ pts']=false;
$cfg->assesslabs['ks4target']['targ pts']=false;


$cfg->securitycode = "AGIFDJ4mgaidlif£532esgfl";

// NOTE - you do not need to configure a database for eportal extender anymore, you just need the table to exist in moodle.
$cfg->eeDbTablePrefix=$CFG->prefix.'mis_'; // GT Mod 2008/04/07 (added cache table prefix so that it can be integrated into moodle with ease)
$cfg->eeDbMaxCacheAge=array('days'=>4, 'hours'=>0, 'minutes'=>22); // Max cache age

// GT Mod 2008/04/14 - url base for reporting lib DEPRECATED 2008/09/11
// $cfg->reporturlbase=$CFG->wwwroot.'/blocks/mis/lib/reports/';

$cfg->GCSE_ShortCourses=array('ci', 're'); // array of half courses

/**
* School Settings
*/
$cfg->firstYear=7; // first year number of students at this school
$cfg->lastYear=13; // last year number of students at this school


/**
* MIS Tabs - set true to display and false to hide
*/
$cfg->tabs['welcome']=true;
$cfg->tabs['eportfolio']=false;
$cfg->tabs['attendance']=false;
$cfg->tabs['assessment']=true;
$cfg->tabs['profile']=true;
$cfg->tabs['timetable']=true;
$cfg->tabs['targets']=false;
$cfg->tabs['rewards']=false; // requires rewards block
$cfg->tabs['events']=true;

// $CFG->mis->defaultTab='welcome';


/**
* Force parent zone urls to use https?
*/
$cfg->https=true;
	
/**
/* Default Values
*/
$cfg->theme = 'default';
$cfg->dateformat = 'd/m/Y'; // if you want to change this, please refer to http://www.php.net/manual/en/function.date.php

/**
/* Debugging Config
**/
$cfg->debug = false;
$cfg->debugadminonly = true; // only show debug messages to admin users

// GT MOD 2008/09/11 Add config to global config
$CFG->mis=$cfg;

require_once($CFG->dirroot.'/lib/adodb/adodb.inc.php');
require_once($CFG->dirroot.'/blocks/mis/lib/lib_facility_db.php');

?>