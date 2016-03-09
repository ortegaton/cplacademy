<?php
	/**
	* (c) Alan Hardy - Frederick Gent School 2008
	* 
	* Licence - GNU GENERAL PUBLIC LICENSE - Version 3, 29 June 2007
	*           Refer to http://www.gnu.org/licenses/gpl.html for full terms
	*
	* Version - Alpha 
	*
	* Date    - 03-03-2008
	*
	* Project - ParentZone - Facility to Moodle integration
	*
	**/

	require_once('../../config.php');
	require_once("../../course/lib.php");
	require_once($CFG->dirroot.'/lib/blocklib.php');
	require_once($CFG->dirroot.'/user/profile/lib.php');
	global $DB;
	$userid  = required_param('userid',          PARAM_INT);   // user id
	$courseid = required_param('courseid',        PARAM_INT);   // user id
	$sectionid = optional_param('sectionid',     0,      PARAM_INT);   // user id
	$editon = optional_param('editon',     0,      PARAM_INT);   // edit

	if (!isset($editon))
	{
	$editon="";
	}

	if (! $site = get_site()) {
		echo 'Could not find site-level course';
	}

	if (!$adminuser = get_admin()) {
		echo 'Could not find site admin';
	}


	if (empty($id)) {         // See your own profile by default
		require_login();
		$id = $USER->id;
	}

	if (! $user = $DB->get_record('user',array('id'=>$id))) {
		error("No e-Portfolio exists for this user");
	}


	if (!empty($CFG->forceloginforprofiles)) {
		require_login();
		if (isguest()) {
			redirect("$CFG->wwwroot/login/index.php");
		}
	}



	require('lib/block.php');


	$userDetails = $DB->get_record('user',array('id'=>$userid));
	if($userDetails === false) {
		 error('Unable to locate this user');
	}

	$courseDetails = $DB->get_record('course',array('id'=>$courseid));
	if($courseDetails === false) {
		 error('Unable to locate this course');
	}

	//print_header( $userDetails->firstname. " " . $userDetails->lastname . ":" . $courseDetails->fullname , $site->fullname,"<a href=\"{$CFG->wwwroot}/course/view.php?id=" . $courseDetails->id . "\">" . $courseDetails->fullname . "</a>-><a href=\"$CFG->wwwroot/blocks/eportfolio/summaryPage.php?courseid=$courseid\">e-Portfolios</a> -><a href=\"$CFG->wwwroot/blocks/eportfolio/index.php?courseid=$courseid&userid=$userid\">" .$userDetails->firstname. " " . $userDetails->lastname . "</a>->" . $courseDetails->fullname);


	echo "<div align='center'>\n";
	echo "<br>\n";
	echo "<table border='0' width='96%' id='table1' cellpadding='0' cellspacing='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='100%' align=\"left\">\n";
	echo "			<img src='images/epmid.gif'><font face='verdana' size='4'><b> " . $userDetails->firstname. " " . $userDetails->lastname . "'s " . $courseDetails->fullname ." e-Porftolio</b></font>\n<br>\n<br>\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "	</table>\n";

	$topicList = $DB->get_field('eportfolio_courses','includeTopics',array('courseid'=>$courseid));
	$topicList = rtrim($topicList, ",");
	$topicList = ltrim($topicList, ",");

	$arrTopics = explode(",", $topicList);

	for ($i=0; $i<count($arrTopics);$i++){
		if (!isset($whereList)){
			$whereList = "id =". $arrTopics[$i];
		}else{
			$whereList .= " OR id =" . $arrTopics[$i];
		}
	}

	if (!$sectionid){
		$sectionid = $arrTopics[0];
	}

	$sql = 'SELECT * FROM {course_sections} WHERE '. $whereList . ' AND course=' . $courseid;
	$sections = $DB->get_records_sql($sql);
	if($sections === false) {
		error('Unable to get sections');
	}
	echo "<table width=\"95%\">\n";
	echo "	<tr height=\"45px\">\n";
	/*
	foreach($sections as $section) {
		if ($section->id == $sectionid){
			echo "		<td class=\"tableft\"  style=\"background-image:url('images/activetableft.gif');background-repeat:  no-repeat; background-position: top right;\" width=\"10px\"></td>\n";
			echo "		<td align=\"center\"   style=\"background-image:url('images/activetabback.gif');\"><font face=\"verdana\" size=\"1\"><a style=\"color:white;\" href=\"?userid=" . $userid ."&courseid=" . $courseid ."&sectionid=" . $section->id . "\"><b>" . strip_tags($section->summary) . "</a></b></font></td>\n";
			echo "		<td class=\"tabright\" style=\"background-image:url('images/activetabright.gif');background-repeat:  no-repeat; background-position: top left;\" width=\"10px\"></td>\n";


		}else{
			echo "		<td class=\"tableft\"  style=\"background-image:url('images/tableft.gif');background-repeat:  no-repeat; background-position: top right;\" width=\"10px\"></td>\n";
			echo "		<td align=\"center\"   style=\"background-image:url('images/tabback.gif');\"><font face=\"verdana\" size=\"1\"><a style=\"color:white;\" href=\"?userid=" . $userid ."&courseid=" . $courseid ."&sectionid=" . $section->id . "\"><b>" . strip_tags($section->summary) . "</a></b></font></td>\n";
			echo "		<td class=\"tabright\" style=\"background-image:url('images/tabright.gif');background-repeat:  no-repeat; background-position: top left;\" width=\"10px\"></td>\n";

		}
	}
	*/
	echo "	</tr>\n";
	echo "</table>\n";

	//draw column heading
	echo "<table width=\"95%\" border=\"1px\" style=\"border-collapse:collapse;border-color:black;\">\n";
	echo "	<tr>\n";
		echo "		<td class=\"headingblock header\" width='45%' align=\"center\"><font face=\"verdana\" size=\"2\"><b>\n";
		echo "			Assignment\n";
		echo "		</font></b></td>\n";
		echo "		<td class=\"headingblock header\" width='40%' align=\"center\"><font face=\"verdana\" size=\"2\"><b>\n";
		echo "			Submitted\n";
		echo "		</font></b></td>\n";
		echo "		<td class=\"headingblock header\" width='15%' align=\"center\"><font face=\"verdana\" size=\"2\"><b>\n";
		echo "			Teacher Grade\n";
		echo "		</font></b></td>\n";
	echo "	</tr>\n";
	//get all modules from this course & section and that are assignments

	$assList = $DB->get_field('eportfolio_courses','includeAssignments',array('courseid'=>$courseid));
	$assList = rtrim($assList, ",");
	$assList = ltrim($assList, ",");
	$arrAssignments = explode(",", $assList);
	for ($i=0; $i<count($arrAssignments);$i++){
		if (!isset($whereList2)){
			$whereList2 = "ass.id =". $arrAssignments[$i];
		}else{
			$whereList2 .= " OR ass.id =" . $arrAssignments[$i];
		}
	}

	$sql  = "SELECT cm.id as cmid, \n";
	$sql .= "       cm.instance,\n";
	$sql .= "       cm.course,\n";
	$sql .= "       cm.section,\n";
	$sql .= "       cm.module,\n";
	$sql .= "       ass.id,\n";
	$sql .= "       ass.name, \n";
	$sql .= "	ass.grade,\n";
	$sql .= "	ass.description\n";
	$sql .= " FROM  {course_modules} AS cm, {assignment} AS ass\n" ;

	   $sql .= " WHERE cm.instance = ass.id \n";
	$sql .= " AND   cm.course =".$courseid;
	//$sql .= " AND   cm.section =".$sectionid ;
	$sql .= " AND   cm.module  = 1";
	$sql .= " AND (" .  $whereList2 . ") " ;

	$sql .= "ORDER BY ass.id";
	$modules = $DB->get_records_sql($sql);

	if($modules) {

		foreach($modules as $module) {
		//get details for this instance

			echo "	<tr height=\"30px\" align=\"left\">\n";
			echo "		<td title=\"" . strip_tags($module->description) . "\">\n";
			echo "			<img src='".$CFG->modpixpath."/assignment/icon.gif' class=\"icon\" alt=\"\" /><font face=\"verdana\" size=\"2\">". $module->name . "</font>\n";
			echo "		</td>\n";

			$assSubmission = $DB->get_record('assignment_submissions',array('assignment'=>$module->instance,'userid'=>$userid));
			if($assSubmission) {
				if ($assSubmission->grade == "-1")
				{
					$icon = "needsmarking.gif";
					$desc = "This assignment has been submitted but is awaiting marking.";
					echo "		<td align=\"center\"><font face=\"verdana\" size=\"2\">\n";

					echo "		</font></td>\n";
					echo "		<td align=\"left\">\n";
					echo "			<img title =\"" . $desc . "\" src=\"images/" . $icon . "\">"; 	
					echo "		</td>\n";
				}else{
					$icon ="complete.gif";
					$desc = "This assignment is complete.";
					echo "		<td align=\"center\"><font face=\"verdana\" size=\"2\">\n";
					//$fileLink = getFileLink($courseid,$userid,$module->instance);
					//echo $fileLink;
					echo userdate($assSubmission->timemodified);
					echo "		</font></td>\n";
					echo "		<td align=\"left\"><font face=\"verdana\" size=\"2\">\n";
					$humanGrade = epf_display_grade($module ,$assSubmission->grade);
					if (strtolower($humanGrade) == strtolower("Below Pass")){
						echo "			<table><tr><td></td> ";
						echo "			<td><font face=\"verdana\" size=\"2\">" . $humanGrade . "</font></td></tr></table>";
					}else{
						echo "			<table><tr><td></td> ";
						echo "			<td><font face=\"verdana\" size=\"2\">" . $humanGrade . "</font></td></tr></table>";
					}
					echo "		</td>\n";

				}
			}else{
				$icon ="notuploaded.gif";
				$desc = "Warning! You have not completed this assignment yet!";
				echo "		<td align=\"center\"><font face=\"verdana\" size=\"2\">\n";
				//echo "			This assignment has not been submitted.<br> Please Upload the assignment <a href=\"" . $CFG->wwwroot . "/mod/assignment/view.php?id=" . $module->cmid . "\" target=\"new\"><b> Here</b></a>\n"; 	
				echo "		</font></td>\n";					
				echo "		<td align=\"left\">\n";
				//echo "			<img title =\"" . $desc . "\" src=\"images/" . $icon . "\">"; 	
				echo "		</td>\n";
			}

			echo "	</tr>\n";
		}
	}	 

	echo "</table>\n";
	echo "<br>\n";

	//print_footer();


	function getFileLink($courseid,$userid,$assignmentid){
	global $CFG, $USER;


	$filearea = $courseid.'/moddata/assignment/'. $assignmentid .'/'.$userid;

	$output = '';

	$fullFileArea = $CFG->dataroot . "/" .$filearea ;
	$newestFile="";
	if ($files = get_directory_list($fullFileArea)) {
	require_once($CFG->libdir.'/filelib.php');
	foreach ($files as $key => $file) {

		if($newestFile <  filemtime($fullFileArea ."/".$file)){
			$newestFile =filemtime($fullFileArea ."/".$file);
			$newestFileName = $file;
		}	

		$icon = mimeinfo('icon', $newestFileName);
		if ($CFG->slasharguments) {
			$ffurl = "$CFG->wwwroot/file.php/$filearea/$newestFileName";
			} else {
			$ffurl = "$CFG->wwwroot/file.php?file=/$filearea/$newestFileName";
		}
	}


		$output .= '<img align="middle" src="'.$CFG->pixpath.'/f/'.$icon.'" class="icon" alt="'.$icon.'" />'.
		   '<a href="'.$ffurl.'" >'.$newestFileName.'</a><br />';
	}        



	return $output;    
	}

	function epf_display_grade($module ,$grade) {

		if ($module->grade >= 0) {    // Normal number
			if ($grade == -1) {
				  return '-';
			} else {
				 return $grade.' / '.$module->grade;
			}
		} else {                                // Scale
			 if (empty($scalegrades[$module->instance])) {
				 if ($scale = $DB->get_record('scale',array('id'=>-($module->grade)))) {
					 $scalegrades[$module->instance] = make_menu_from_list($scale->scale);
				 } else {
					  return '-';
				 }
			 }
			 if (isset($scalegrades[$module->instance][$grade])) {
				 return $scalegrades[$module->instance][$grade];
			 }
			   return '-';
		  }
	  }
?>