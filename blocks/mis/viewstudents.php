<?php
    global $USER, $CFG, $DB, $fdata;
    require_once('../../config.php');
    require_once($CFG->dirroot.'/blocks/mis/cfg/config.php');
    require_once($CFG->dirroot.'/blocks/mis/lib/lib_facility_db.php');  
    require_once($CFG->dirroot.'/blocks/mis/lib/moodledb.php');
    require_once($CFG->dirroot.'/user/filters/lib.php');
	
    $url= new moodle_url('/blocks/mis/viewstudents.php');
	$PAGE->set_url($url);
	$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
	$PAGE->set_heading(get_string('mis:viewstudent','block_mis'));
	$PAGE->set_title(get_string('mis:viewstudent','block_mis'));
	$CFG->stylesheets[]=$CFG->wwwroot.'/blocks/mis/css/style.css';
	
	// Define optional URL parameters (for filtering)
    $sort         = optional_param('sort', 'lastname', PARAM_ALPHANUM);
    $dir          = optional_param('dir', 'ASC', PARAM_ALPHA);
	$page         = optional_param('page', 0, PARAM_INT);
	$perpage      = optional_param('perpage', 10, PARAM_INT);  
    
	// Are we allowed to be here?
    require_login();    
	$capsviewany=has_capability('block/mis:viewanystudent', get_context_instance(CONTEXT_SYSTEM));
	if (!$site = get_site()) {
		error("Could not find site-level course");
	}   
    if (((!has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM)))&& !$capsviewany) || !confirm_sesskey()) {
        error("You do not have access to this area");
    }
    
    // Render the page Header
    echo $OUTPUT->header();
    
	// Create filter object instance, grab any filters and merge with predefined SQL selector
	$ufiltering = new user_filtering();
	$context = context_system::instance();
	$extrasql='description = :replacethis';
	$params=array('replacethis'=>'student');
	list($extrasql2, $params2) = $ufiltering->get_sql_filter();
	if(!empty($extrasql2)){$extrasql=$extrasql.' AND '.$extrasql2;}
	$params = array_merge($params, $params2);
	
	// Do the query work...
    $users = get_users_listing($sort, $dir, $page*$perpage, $perpage, '', '', '', $extrasql, $params, $context);
	$usercount = get_users(false);
    $usersearchcount = get_users(false, '', true, null, "", '', '', '', '', '*', $extrasql, $params);
	
	// Set the baseurl for pagination links...
	$baseurl = new moodle_url('viewstudents.php', array('sesskey'=>$USER->sesskey, 'sort' => $sort, 'dir' => $dir, 'perpage' => $perpage));
    
	// If filtered, render heading appropriately to reflect...
	if ($extrasql !== '') {
        echo $OUTPUT->heading("$usersearchcount / $usercount ".get_string('users'));
        $usercount = $usersearchcount;
    } else {
        echo $OUTPUT->heading("$usercount ".get_string('users'));
    }

    // add filter form and active filter display...
    $ufiltering->display_add();
    $ufiltering->display_active();    

    // Create table
	$table = new html_table();
	$table->head = array ("Full Name", "Id Number", "Username");
	$table->align = array ("left", "left", "left");
	$table->width = "100%";

    if ($users){
		// Code contributed by Andrew Nicols - http://moodle.org/mod/forum/discuss.php?d=194977
		$userids = array_map(create_function('$a', 'return $a->id;'), $users);
   		list($sql, $params) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
   		$userdata = $DB->get_records_sql('SELECT id, username, firstname, lastname, idnumber FROM {user} WHERE id ' . $sql, $params);
   		$url = new moodle_url('index.php', array('sesskey' => sesskey()));
		
		// Show pagination navigation (Top)
		echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
		
		// Loop thru and write row data for table
		foreach ($userdata as $user) {
 		    $url->param('userid', $user->id);
		    $link = html_writer::link($url, fullname($user));
 		    $table->data[] = array($link, $user->idnumber, $user->username);
 		}
		// End of code contribution
    }

	if (!empty($table)) {
		// Render the table...
		echo html_writer::table($table);
		
		// Render the bottom pagination navigation...
		echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
	}

// Finally, render the page footer...	
echo $OUTPUT->footer();
?>