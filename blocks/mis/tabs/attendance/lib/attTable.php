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
	* Project - MIS - Facility to Moodle integration
	*
	**/

	require_once('../../../../../config.php');
	require_once('../../../cfg/config.php');
	require_once('../../../lib/block.php');
    include_once('../../../lib/setvars.php'); // GT Mod set standard variables	    
	require_once("attLibs.php");

	$month = $_GET['month'];
	$year = $_GET['year'];

	if($month == '' && $year == '') { 
		$time = time();
		$month = date('n',$time);
		$year = date('Y',$time);
	}

	$fdata = new facilityData();
	$attDetails = getAttDetails($USER->mis_mdlstuid,$month,$year);

	$weekTable = "<table class=\"attTable\" >\n";
	$weekTable .= "	<tr class=\"dayhead\">\n";
	$weekTable .= "		<td>\n";
	$weekTable .= "			Date\n";
	$weekTable .= "		</td>\n";
	$weekTable .= "		<td>\n";
	$weekTable .= "			Description\n";	
	$weekTable .= "		</td>\n";
	$weekTable .= "		<td>\n";
	$weekTable .= "			Type\n";	
	$weekTable .= "		</td>\n";
	$weekTable .= "		<td>\n";
	$weekTable .= "			Start\n";	
	$weekTable .= "		</td>\n";
	$weekTable .= "		<td>\n";
	$weekTable .= "			Finish\n";	
	$weekTable .= "		</td>\n";
	$weekTable .= "		<td>\n";
	$weekTable .= "			Authorized\n";	
	$weekTable .= "		</td>\n";
	$weekTable .= "		<td>\n";
	$weekTable .= "			Explained\n";	
	$weekTable .= "		</td>\n";
	$weekTable .= "	</tr>\n";
	if ($attDetails !=""){
		foreach($attDetails as $attDetail){
			$weekTable .= "	<tr class=\"" . $attDetail->attribtype . "\">\n";
			$weekTable .= "		<td class=\"attData\">\n";
			$weekTable .= "			" . date("d/m/Y",strtotime($attDetail->attdate));
			$weekTable .= "		</td>\n";		
			$weekTable .= "		<td class=\"attData\">\n";
			$weekTable .= "			" . $attDetail->descrip;
			$weekTable .= "		</td>\n";
			$weekTable .= "		<td class=\"attData\">\n";
			if($attDetail->attribtype == "A"){
				$weekTable .= "Absent";
			}else{
				$weekTable .= "Late";
			}			
			$weekTable .= "		</td>\n";
			$weekTable .= "		<td class=\"attData\">\n";
			$weekTable .= "			" . formatTime($attDetail->starttime);
			$weekTable .= "		</td>\n";
			$weekTable .= "		<td class=\"attData\">\n";
			$weekTable .= "			" . formatTime($attDetail->finishtime);
			$weekTable .= "		</td>\n";
			$weekTable .= "		<td  class=\"attData\">\n";
			if($attDetail->excused == "Y"){
				$weekTable .= "Yes";
			}else{
				$weekTable .= "No";
			}		
			$weekTable .= "		</td>\n";
			$weekTable .= "		<td class=\"attData\">\n";
			if($attDetail->expl == "Y"){
				$weekTable .= "Yes";
			}else{
				$weekTable .= "No";
			}
			$weekTable .= "		</td>\n";
			$weekTable .= "	</tr>\n";
		}
	}
	$weekTable .= "	</table>\n";
	
	$Block = new Block(get_string('attTable','block_mis'). getMonthName() ,"attTable","",$weekTable);
	$table = $Block->draw();
	echo $table;
?>
	
		