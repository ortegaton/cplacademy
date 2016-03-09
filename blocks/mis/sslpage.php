<?php
 	/**
	* (c) Alan Hardy and Guy Thomas - Frederick Gent School & Ossett School 2008
	* 
	* Licence - GNU GENERAL PUBLIC LICENSE - Version 3, 29 June 2007
	*           Refer to http://www.gnu.org/licenses/gpl.html for full terms
	*
	* Version - Alpha 
	*
	* Date    - 2008-09-12
	*
	* Project - MIS - Facility to Moodle integration
    *
    * Purpose - This page forces any requested url to use the SSL protocol for all links
	*
	**/
    
    global $CFG;
    
	require_once('../../config.php');    
    require_once($CFG->dirroot.'/lib/moodlelib.php');
    require_once($CFG->dirroot.'/blocks/mis/cfg/config.php');
    require_once($CFG->dirroot.'/blocks/mis/lib/urllib.php');
    
    
    // Override https setting of $CFG->wwwroot
    $CFG->wwwroot=get_mis_www();
    
    $url=urldecode(required_param('url', PARAM_LOCALURL));

    $page=get_url_contents($url);    
    $page=str_ireplace('http://', 'https://', $page);
    
    echo ($page);

?>