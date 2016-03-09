<?php

//
// Maps aliases to fields - e.g. student.middlename to nstupersonal.forename
//

class columnAliasMap {

	var $aliases=array(); // "table.fieldName" = alias
	var $shortTableMap=array(); // short names for tables (e.g. pers for nstupersonal)
	var $cfg;
	var $fdata;

	//
	// Class constructor
	//
	function columnAliasMap(){
        global $CFG;
        
		// If config hasn't been set then try global variable
		if(!isset($this->cfg)){
			$this->cfg=$CFG->mis;
		}
		// If msdb hasn't been set then try global variable
		if(!isset($this->fdata)){
			$this->fdata=$GLOBALS['fdata'];
		}	
		
		// establish table map
		$this->establishTableMap();
		
		// establish aliases
		$this->establishAliases();

	}


	//
	// Returns true if field expression is for an exam
	//
	function isExamField($fldExp){
		// make sure field expression has a type delimeter (.) - if not just return false
		$spos=strpos ($fldExp, ".");		
		if ($spos===false){
			return (false);
		}
		if (substr($fldExp, 0, $spos)=='exam'){
			// if its an exam then return true
			return (true);
		}
		return (false);
	}
	
	//
	// Gets the sql field reference by alias (e.g. student.forename becomes pers.forename)
	//
	function getFieldSQL($fldExp){
				
		// make sure field expression has a type delimeter (.) - if not just return fldExp
		$spos=strpos ($fldExp, ".");
		if ($spos===false){
			return ($fldExp);
		}
		
		// check that its not an exam - if it is an exam then just return fldExp			
		if (substr($fldExp, 0, $spos)=='exam'){
			// if its an exam then return $fldExp
			return ($fldExp);
		}

		// get field table and return with short table.field
		$fld=$this->aliases[$fldExp];
		$spos=strpos ($fld, ".");		
		$fldTable=substr($fld, 0, $spos);
		$fldShrtTab=$this->shortTableMap[$fldTable];
		$fld=str_replace($fldTable, $fldShrtTab, $fld); // replace table with short table from table map
		
		return ($fld);
	}
	
	
	
	//
	// Get field and short table concatenated e.g. st_name instead of student.name
	//
	function getFieldTableConcat($fldExp){
		return(str_replace(".", "_", $this->getFieldSQL($fldExp)));
	}
	
	//
	// Gets field name only (i.e. dumps the table and exam portion)
	//
	function getField($fldExp){
		$result=substr($fldExp, (strrpos($fldExp,".")+1), ((strlen($fldExp)-strrpos($fldExp,".")))-1);
		// convert tildes back to spaces (used in exam labels)
		$result=str_replace("~"," ",$result);
		return ($result);
	}
	
	//
	// Sets shortTableMap array
	//
	private function establishTableMap(){
		$this->shortTableMap['nstupersonal']='pers';
		$this->shortTableMap['students']='st';
		$this->shortTableMap['nsturesults']='nr';
		$this->shortTableMap['teachinggroups']='tg';
		$this->shortTableMap['module']='md';
	}
	
	//
	// Establish aliases
	//
	private function establishAliases(){
		
		// DO NSTUPERSONAL TABLE
		$tabAlias="student";
		$tab="nstupersonal";
		
		
		// LIST MAPPINGS FOR FIELDS THAT HAVE ALIASES - e.g. id instead of studentid
		$this->aliases["{$tabAlias}.id"]="{$tab}.studentid";
		$this->aliases["{$tabAlias}.title"]="{$tab}.stutitle";
		$this->aliases["{$tabAlias}.middlename"]="{$tab}.forename2";
		$this->aliases["{$tabAlias}.dob"]="{$tab}.dateofbirth";

		// ADD REST OF FIELDS
		$this->addFieldsToAliases($tabAlias, $tab);
		
		// DO STUDENTS TABLE
		$tabAlias="student";
		$tab="students";		
		
		// ADD REST OF FIELDS
		$this->addFieldsToAliases($tabAlias, $tab);
		
		// DO TEACHING GROUPS TABLE
		$tabAlias="teachinggroup";
		$tab="teachinggroups";
		
		// ADD REST OF FIELDS
		$this->addFieldsToAliases($tabAlias, $tab);
		
		// DO NSTURESULTS TABLE
		$tabAlias="result";
		$tab="nsturesults";
		
		// ADD REST OF FIELDS
		$this->addFieldsToAliases($tabAlias, $tab);

		// DO MODULE TABLE
		$tabAlias="module";
		$tab="module";
		
		// ADD REST OF FIELDS
		$this->addFieldsToAliases($tabAlias, $tab);			
		
		
	}

	private function addFieldsToAliases($tabAlias, $tab){
		$fields=$this->fdata->metacolumnnames($tab,$numericIndex=false);
		foreach ($fields as $field){
			$tabfield=$tab.".".strtolower($field);
			$tabAKey=$tabAlias.".".strtolower($field);
			// only add to alias list if its not already in there
			if (!isset($this->aliases[$tabAKey])){
				$this->aliases[$tabAKey]=$tabfield;
			}
		}
	}
		
}

?>