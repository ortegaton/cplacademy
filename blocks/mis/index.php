<?php
	/**
	* Original (c) Alan Hardy - Frederick Gent School 2008
	* For Moodle 2.2+ (c) Marc Coyles - Ossett Academy 2012
	*
	* Licence - GNU GENERAL PUBLIC LICENSE - Version 3, 29 June 2007
	*           Refer to http://www.gnu.org/licenses/gpl.html for full terms
	*
	* Version - Alpha
	*
	* Date    - 16-02-2012
	*
	* Project - MIS - Facility to Moodle integration
	*
	**/

	require_once('../../config.php');
 	require_once('cfg/config.php');
	require_once('lib/lib_facility_db.php');
	require_once('lib/block.php');
    
    include_once('lib/setvars.php'); // GT Mod set standard variables - THIS GETS THE STUDENTS ID, ETC
    
 	require_once($CFG->dirroot.'/blocks/mis/tabs/tab_base.class.php');
        
  	$postTab   = optional_param('tab', false, PARAM_ALPHA);
      	
    global $CFG, $DB, $HTTPSPAGEREQUIRED, $PAGE, $userDetails, $fdata;
        
	$url= new moodle_url('/blocks/mis/index.php');
	$PAGE->set_url($url);
	$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
	$PAGE->requires->css('/blocks/mis/css/style.css');
	 require_login();
    // Is the entire site https?
    $siteishttps=stripos($CFG->wwwroot, 'https://')!==false;
    $CFG->mis->siteishttps=$siteishttps;
        
    // Override https setting of $CFG->wwwroot
    $CFG->wwwroot=get_mis_www();
    $HTTPSPAGEREQUIRED=isset($CFG->mis->https) && $CFG->mis->https;
    
    if ($HTTPSPAGEREQUIRED){
        $CFG->httpswwwroot=$CFG->wwwroot;
    }
   
  	$fdata=new facilityData();

  	$userDetails = $DB->get_record('user', array('id'=>$USER->mis_mdlstuid));
		if($userDetails === false) {
			 error('Unable to locate this user');
	}    

    if (!$site = get_site()) {
		echo $OUTPUT->box(get_string('noaccess','block_mis'), 'generalbox boxaligncenter');
		echo $OUTPUT->footer();
		die();
	}
	if (empty($id)) {         // See your own profile by default
		$id = $USER->id;
	}
	
    if (isguestuser()) {
        redirect("$CFG->wwwroot/login/index.php");
    }
	
	$tabDir = 'blocks/mis/tabs';
	//get list of modules in tab dir
	$tabs = $CFG->mis->tabs;
	$content = "";
	$currentTab = false;
    $firstTab = false;
	//loop through modules
	foreach($tabs as $tab=>$value){
    
        // set first tab if not already set
        $firstTab=$firstTab ? $firstTab : $tab;
    
		//form module file path
		$classFile= $CFG->dirroot."/".$tabDir."/".$tab ."/tab_" .$tab .".php";
		
		if ($value){
			//check if its a valid module and load class
			if (file_exists($classFile)){
				require_once($classFile);
				$className = 'tab_' . $tab;

				//create an instance of class tab_* where *=modulename
				${$tab} = new $className($tab,$fdata);

				//set the path for includes
				${$tab}->setTabPath($CFG->dirroot . "/blocks/mis/tabs/". $tab);

				//build an array of titles for tabs
				$availableTabs[$tab] = ${$tab}->getTitle();
			}
		}
	}
    	
	if (!$postTab){
		$postTab = isset($CFG->mis->defaultTab) ? $CFG->mis->defaultTab : $firstTab;
	}
	
	//Get the name / title of the current tab
	$currentTab = ${$postTab}->getName();
    $currentTitle = ${$postTab}->getTitle();
                     
    //set parent zone title if not set
    if (!isset($CFG->mis->title)){
        $CFG->mis->title='Parent Zone';
    } 
    
    // can person view any student
    $capsviewany=has_capability('block/mis:viewanystudent', get_context_instance(CONTEXT_SYSTEM));
                    
    
    if ($capsviewany){
        // navigation for people who can view any student       
        $PAGE->navbar->add($CFG->mis->title, new moodle_url(${$currentTab}->blockwww.'/viewstudents.php?sesskey='.$USER->sesskey));
		$PAGE->navbar->add($userDetails->firstname.' '.$userDetails->lastname, new moodle_url(${$currentTab}->blockwww.'/index.php?sesskey='.$USER->sesskey.'&amp;user='.$USER->mis_mdlstuid));
		     
    } else {
        // navigation for people who can only view specific students (i.e. children)       
        $PAGE->navbar->add($CFG->mis->title, new moodle_url(${$currentTab}->blockwww.'/index.php?sesskey='.$USER->sesskey.'&amp;user='.$USER->mis_mdlstuid));
		$PAGE->navbar->add($userDetails->firstname.' '.$userDetails->lastname, new moodle_url(${$currentTab}->blockwww.'/index.php?sesskey='.$USER->sesskey.'&amp;user='.$USER->mis_mdlstuid));
		
    }
	$personalcontext = get_context_instance(CONTEXT_USER, $USER->mis_mdlstuid);    
    $capsok=has_capability('block/mis:viewstudent', $personalcontext) || $capsviewany;    
    if ($capsok){
        $stuname=$userDetails->firstname.' '.$userDetails->lastname;
    } else {
        $stuname='unauthorised student access';
    }
   	$cache=true;
    // get header html
	$PAGE->set_heading($stuname);
	$PAGE->set_title($stuname.' | ' . $currentTitle);
	$PAGE->set_cacheable($cache);
	$header=$OUTPUT->header();
   	
    // make sure site object exists
    if (! $site = get_site()) {
        $site = new object();
        $site->shortname = get_string('home');
    }    
    
    // If the site does not normally require https- make the home link non https
   if (!$siteishttps){
        $repsrch='href="'.$CFG->wwwroot.'">'.$site->shortname;
        $reprep='href="'.str_replace('https://', 'http://', $CFG->wwwroot.'">').$site->shortname;    
        $header=str_replace($repsrch, $reprep, $header);
        $repsrch='href="'.$CFG->wwwroot.'/">'.$site->shortname;
        $reprep='href="'.str_replace('https://', 'http://', $CFG->wwwroot.'/">').$site->shortname;    
        $header=str_replace($repsrch, $reprep, $header);
    }
    
    // output header html
    echo ($header);
    
   	
   	foreach ($availableTabs as $name=>$title){
        $wwwroot=${$name}->blockwww;
        // Profile tab wont work over ssl because it keeps trying to revert to https - so just use https
        if ($name=='profile'){
            $wwwroot=str_ireplace('https://', 'http://', $wwwroot);
        }
		$toprow[] = new tabobject($name, $wwwroot.'/index.php?tab=' . $name.'&userid='.$USER->mis_mdlstuid, $title);
   	}

   	$tabs = array($toprow);
   	print_tabs($tabs, strtolower($currentTab));
   	${$postTab}->render();

    // Restore https setting of CFG->wwwroot
    $CFG->wwwroot=get_mis_www($siteishttps);
    echo $OUTPUT->footer();
?>