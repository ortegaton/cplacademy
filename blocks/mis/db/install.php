<?php
require_once($CFG->dirroot.'/lib/dmllib.php');

xmldb_block_mis_install();

function xmldb_block_mis_install(){
	global $CFG, $DB;
	
	$misrole=$DB->record_exists('role',array('shortname'=>'parent'));
	$theroles=$DB->get_records('role');
	$acounter=1;
	foreach ($theroles as $arole){
		$acounter++;
	}	
	if(!$misrole){
		$misroleobj = new stdClass();
		$misroleobj->name = 'Parent';
		$misroleobj->shortname = 'parent';
		$misroleobj->sortorder = (int)$acounter;
		$misroleobj->description = 'This role is allocated to parents of students.<br />The role must exist in the students profile and the parent must then be allocated to this role.<br /><br />See the following url for a detailed explanation:<br /><br />http://docs.moodle.org/en/Parent_role<br />';
		$misroleid=$DB->insert_record('role',$misroleobj,true);
		$acounter++;
		$contextquery=$DB->record_exists('role_context_levels',array('roleid'=>$misroleid));
		if(!$contextquery){
		  $contextobj = new stdClass();
		  $contextobj->roleid = $misroleid;
		  $contextobj->contextlevel = 30;
		  $contextid=$DB->insert_record('role_context_levels',$contextobj,true);
		}
	}
	/*$capobj = new stdClass();
	$capobj->name='block/mis:viewblock';
	$capobj->captype='read';
	$capobj->contextlevel=CONTEXT_SYSTEM;
	$capobj->component='block_mis';
	$DB->insert_record('capabilities',$capobj);	
	$capobj = new stdClass();
	$capobj->name='block/mis:viewstudent';
	$capobj->captype='read';
	$capobj->contextlevel=CONTEXT_SYSTEM;
	$capobj->component='block_mis';
	$DB->insert_record('capabilities',$capobj);
	$capobj = new stdClass();
	$capobj->name='block/mis:viewown';
	$capobj->captype='read';
	$capobj->contextlevel=CONTEXT_SYSTEM;
	$capobj->component='block_mis';
	$DB->insert_record('capabilities',$capobj);
	$capobj = new stdClass();
	$capobj->name='block/mis:viewanystudent';
	$capobj->captype='read';
	$capobj->contextlevel=CONTEXT_SYSTEM;
	$capobj->component='block_mis';
	$DB->insert_record('capabilities',$capobj);
	$capobj = new stdClass();
	$capobj->name='block/mis:manageassessments';
	$capobj->captype='read';
	$capobj->contextlevel=CONTEXT_SYSTEM;
	$capobj->component='block_mis';
	$DB->insert_record('capabilities',$capobj);*/
}
?>