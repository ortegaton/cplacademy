<?php

//
// Purpose: Gets student sen status
// NOTE:
// Do not use this function for a long list of students - it means a lot of sql transactions per student just to get the sen status
//
function hasSEN($stuId, $dataset){
    global $CFG, $fdata;
    $dataset=$dataset ? $dataset : $CFG->mis->cmisDataSet; 
    $facQry='SELECT COUNT(*) AS sen FROM '.$fdata->prefix.'senstustages WHERE studentid=\''.$stuId.'\' AND setid=\''.$dataset.'\'';
    $sen=getFieldValue($facQry, 'sen');    
    if ($sen>0){
        return (true);
    }
}

/**
/* Purpose: Returns map value(s) for criteria label for specific assessment
**/
function mapValsForCritLabel($assessID,$critlabel){
	global $CFG, $fdata;
	
	$facQry='SELECT mapvalue FROM '.$fdata->prefix.'assesscriteria WHERE assessid= \''.$assessID.'\' AND critLabel=\''.$critlabel.'\' ORDER BY mapValue ASC';	
	$result=$fdata->db->execute($facQry);
	$mapVals=Array();
	while ($row = $result->fetchrow()){
		$mapValue=$row['mapvalue'];
		$mapVals[]=$mapValue;
	};
	return ($mapVals);
}

/**
/* Purpose: Returns a criteria label for a specific assessment and map value
/* modified to output lowercase criteria map value (GT 2008/04/07)
**/
function critLabelForMapVal($assessId, $mapVal, $critArr=array()){
	global $fdata;
    
    // use sql if assessCrits is empty
    if (empty($critArr)){
    	$facQry='SELECT critlabel FROM '.$fdata->prefix.'assesscriteria WHERE assessid= \''.$assessId.'\' AND mapvalue='.$mapVal.' ORDER BY critlabel ASC';	
    	$result=$fdata->db->execute($facQry);
    	if (!$result){return(false);} // return false if no criteria label was found for $mapVal
    	$row=$result->fetchrow();    
    	return (strtolower($row['critlabel']));
    } else { 
        foreach ($critArr['bymap'] as $crit){
            if (strtolower($mapVal)==strtolower($crit['mapvalue'])){
                return (strtolower($crit['critlabel'])); 
            }
        }
    }
}

/**
/*Function Purpose: Converts a flat criteria data string to an array with map values as keys
**/	
function flatCriteriaToArray(&$critData){	
    if ($critData==''){return(false);}
    
    $critArray=Array();
    $expArray=explode(chr(10), trim(strval($critData)));
    for ($c=0; $c<count($expArray); $c+=2){
        if ($c+1<count($expArray)){
            $critKey=$expArray[$c];
            $critVal=($c+1)<=count($expArray) ? $expArray[$c+1] : "";
            $critArray[$critKey]=$critVal;
        }
    }    
    return ($critArray);	
}

/**
/* Function Purpose: Converts a flat criteria data string to an array with map values (or labels) as keys and values containing setId, assessId, val, pts
**/
function flatCriteriaToCompArr(&$critRow=array(), &$critArr=array(), &$cpArr=array(), &$critData='', $assessId='', $hashbymap=true){	
    if (empty($critRow)){return (false);}
    if (empty($critArr)){return(false);}
    if (empty($cpArr)){return(false);}        
    $critData=$critData!="" ? $critData : $critRow['criteriadata'];
    if ($critData==""){return(false);}
    $assessId=$assessId!="" ? $assessId : $critRow['assessid'];
    if ($assessId==""){return(false);}
        
    $critArray=Array();
    $expArray=explode(chr(10), trim(strval($critData)));
    for ($c=0; $c<count($expArray); $c+=2){
        if ($c+1<count($expArray)){
            $mapval=$expArray[$c];
            $critVal=($c+1)<=count($expArray) ? $expArray[$c+1] : "";
            // get criteria label
            $critLabel=critLabelForMapVal($assessId, $mapval, $critArr);
            // get points value            
            if (isset($cpArr[$critLabel][$critVal])){                
                $critPts=$cpArr[$critLabel][$critVal];
            } else if (isset($cpArr[$critLabel][strtolower($critVal)])){
                $critPts=$cpArr[$critLabel][strtolower($critVal)];
            } else {
                $critPts=0;
            }
            
            $hashkey=$hashbymap ? $mapval : $critLabel;
            
            $critArray[$hashkey]=array('label'=>$critLabel, 'mapval'=>$mapval, 'setid'=>$critRow['setid'], 'groupid'=>$critRow['groupid'], 'assessid'=>$assessId, 'val'=>$critVal, 'pts'=>$critPts);
        }
    }    
    return ($critArray);	
}


/**
/* Function Purpose: Creates a data array with criteria labels as keys (instead of map values) and adds point values
**/	
function niceDataArray($critArr=array(), $cpArr=array(), $assessId=''){
    if (empty($critArr)){return(false);}
    if (empty($cpArr)){return(false);}
    if ($assessId==''){
        return (false);
    }
            
    $niceArray=array();
    
    foreach ($critArr as $key => $val){
        $niceKey=critLabelForMapVal($assessId, $key); // convert key to nice key
        $niceArray[$niceKey]['val']=$val;
        if (isset($cpArr[$niceKey][$val])){
            $niceArray[$niceKey]['pts']=$cpArr[$niceKey][$val];
        } else {
            $niceArray[$niceKey]['pts']=0;
        }
    }
    return ($niceArray);
}

/**
/* Purpose: Get specific exams for a student
**/
function ExamsForStudent($studentid, $setid=false){
	global $CFG, $fdata;
    if (!$setid){
        $setid=$CFG->mis->cmisDataSet;
    }
        
    $facqry='SELECT DISTINCT n.examid,e.name FROM '.$fdata->prefix.'nsturesults AS n LEFT JOIN '.$fdata->prefix.'examinations AS e ON n.examid=e.examid WHERE n.setid=\''.$setid.'\' AND n.studentid=\''.$studentid.'\'';
    
    $result=$fdata->db->execute($facqry);    
    return ($fdata->recordset_to_array($result));    
}


/**
/* Purpose: Gets an assessmentid for a specific exam
**/
function AssessmentIdForExam($examID){	
	global $fdata;	
	$facQry='SELECT assessid FROM '.$fdata->prefix.'examinations WHERE examid=\''.$examID.'\'';		
    return ($fdata->getFieldValue($facQry,'assessid'));
}

//
// Purpose: Gets teaching group record by code
//
function TeachingGroupFromCode($groupCode, $dataset=false, $fields='*'){
	global $CFG, $fdata;
    $dataset=$dataset ? $dataset : $CFG->mis->cmisDataSet; 
	$facQry='SELECT $fields FROM '.$fdata->prefix.'teachinggroups WHERE groupcode=\''.$groupCode.'\' AND setid=\''.$dataset.'\'';	
	$result=$fdata->db->execute($facQry);
    if ($result && $result->recordcount()>0){
        $row = $result->fetchrow();    
        // remove case sensitivity of row field hashes
        $row = array_change_key_case ($row, CASE_LOWER);
        return ($row);
    } else {
        return (false);
    }    
}

//
// Purpose: gets teaching group id by teaching group code
//
function TeachingGroupIdFromCode($groupCode, $dataset=false){
	$tg=TeachingGroupFromCode($groupCode, $dataset, 'groupid');
	return ($tg['groupid']);
}

//
// Purpose: gets teaching group course year by teaching group code
//
function TeachingGroupYearFromCode($groupCode, $dataset=false){
	$tg=TeachingGroupFromCode($groupCode, $dataset, 'crsyear');
	return($tg['crsyear']);	
}

/**
/* Purpose: Get a module id for a specific teaching group - * note: uses groupId NOT groupCode
/* Returns: string - ModuleId
**/
function ModuleIdForTeachingGroup($groupID, $dataset=false){
    global $CFG, $fdata;
    // make sure dataset is either passed in or if not, use config data set
    $dataset=$dataset ? $dataset : $CFG->mis->cmisDataSet; 
    // query database and return result
    $facQry='SELECT moduleid FROM '.$fdata->prefix.'teachinggroups WHERE GroupId='.$groupID.' AND SetId=\''.$dataset.'\'';        
    return (getFieldValue($facQry,'moduleid'));    
}

/**
/* Purpose: Get teaching group ids for a specific subject (module) and dataset
/* Returns: Array of teaching group ids
**/
function TeachingGroupIdsForModule($moduleID, $dataset=false){
    global $CFG,$fdata;
    // make sure dataset is either passed in or if not, use config data set
    $dataset=$dataset ? $dataset : $CFG->mis->cmisDataSet;
    // query database and return array of teaching group ids
    $facQry='SELECT groupid FROM '.$fdata->prefix.'teachingGRoups WHERE ModuleId=\''.$moduleID.'\' AND SetId=\''.$dataset.'\'';
    $result=$fdata->db->execute($facQry);
    $tgIds=array();
    while ($row=$result->fetchrow()){
        $tgIds[]=$row['groupid'];
    }
    return ($tgIds);
}

/**
/* Function Purpose: Get all data sets before current data set and return array
**/   
function DataSetsBeforeCurrent(){
    global $CFG;
    return (DataSetsBeforeSpecific($CFG->mis->cmisDataSet));
}

/**
/* Function purpose: Get all data sets before a specific data set
**/
function DataSetsBeforeSpecific($dataset){
    global $CFG, $fdata;
    $facQry='SELECT setid FROM '.$fdata->prefix.'setiddata ORDER BY yearstart desc';
    $result=$fdata->db->execute($facQry);
    $dsArray=array();
    $ignoreDs=true;
    while ($row = $result->fetchrow()){        
        $ds = $row['setid'];              
        if (!$ignoreDs){
            // this will ignore any datasets with the name 'test' in them 
            if (strpos(strtolower($ds),'test')===false){
                $dsArray[]=$ds;
            }
        }
        if ($ds==$dataset){
            $ignoreDs=false;
        }
    }
    return ($dsArray);
}


/**
/* Function purpose: Get all data sets
**/
function AllDataSets(){
    global $CFG, $fdata;
    $facQry='SELECT setid FROM '.$fdata->prefix.'setiddata ORDER BY yearstart desc';
    $result=$fdata->db->execute($facQry);
    $dsArray=array();
    while ($row = $result->fetchrow()){
        $ds=$row['setid'];              
        // this will ignore any datasets with the name 'test' in them 
        if (strpos(strtolower($ds),'test')===false){
            $dsArray[]=$ds;
        }
    }
    return ($dsArray);
}


/**
* Purpose: Return array of criteria for specific assessment (keyed by map value)
* modified to output lowercase criteria map value (GT 2008/04/07)
*/
function CriteriaForAssessment($assId, $refresh=false){
    global $CFG, $fdata, $results_assessCrits;
    
    // Use assessment criteria cached in global variable (if it exists)
    if (isset($results_assessCrits[$assId]) && !$refresh){
        return ($results_assessCrits[$assId]);
    }
    
    // set assesslabs to empty array if not already set
    if (!isset($CFG->mis->assesslabs)){
        $CFG->mis->assesslabs=array();
    }
    
    // convert $CFG->mis->assesslab to lower case keys
    $cfgasslabs=array_change_key_case($CFG->mis->assesslabs, CASE_LOWER);
        
    $facQry='SELECT DISTINCT ac.criterid, ac.ordernum, ac.mapvalue, ac.critlabel, ac.critcomp, ac.critertype, ac.critcomp, c.critertype as crit_critertype  FROM '.$fdata->prefix.'assesscriteria as ac LEFT JOIN '.$fdata->prefix.'criteria as c on ac.criterid=c.criterid WHERE ac.assessid=\''.$assId.'\' ORDER BY ordernum';
    
    $result=$fdata->db->execute($facQry);
    $critArr=array();
    while ($row = $result->fetchrow()){
        $displaylabel=$row['critlabel'];

        // If config file has a replacement label for this label, then use it for display label
        if (isset($cfgasslabs[strtolower($assId)])){
            if (isset($cfgasslabs[strtolower($assId)][strtolower($displaylabel)])){
                $displaylabel=$cfgasslabs[strtolower($assId)][strtolower($displaylabel)];
            }
        }
        
        $critertype=$row['critertype']=='-' || $row['critertype']=='' ? $row['crit_critertype'] : $row['critertype'];
    
        $critObj=array('assessid'=>$assId, 'criterid'=>$row['criterid'], 'ordernum'=>$row['ordernum'], 'mapvalue'=>$row['mapvalue'], 'critlabel'=>strtolower($row['critlabel']), 'critlabeldisp'=>$displaylabel, 'critcomp'=>$row['critcomp'], 'critertype'=>$critertype, 'critcomp'=>strtolower($row['critcomp']));
        // hash by map value
        $critArr['bymap'][$row['mapvalue']]=$critObj;
        // hash by label
        $critArr['bylabel'][strtolower($row['critlabel'])]=$critObj;
    }
    // Add assessment criteria to global cash
    $results_assessCrits[$assId]=$critArr;
    
    // Return criteria array
    return ($critArr);
}

//
// Purpose: Get criteria points for specific assessment id
//
function AssessmentCriteriaPoints($assId, $refresh=false){
	global $fdata, $results_assessCritPVs;
    
    if (isset($results_assessCritPVs[$assId]) && !$refresh){
        return ($results_assessCritPVs[$assId]);
    }    
 
    $ptsdescs=AssessmentCriteriaPtsDescs($assId);
 
    // Return criteria points array
	return ($ptsdescs['pts']);
}

//
// Purpose: Get criteria value descriptions for specific assessment id
//
function AssessmentCriteriaDescs($assId, $refresh=false){
	global $fdata, $results_assessCritDescs;
    
    if (isset($results_assessCritDescs[$assId]) && !$refresh){
        return ($results_assessCritDescs[$assId]);
    }    
 
    $ptsdescs=AssessmentCriteriaPtsDescs($assId);
 
    // Return criteria points array
	return ($ptsdescs['desc']);
}

function AssessmentCriteriaPtsDescs($assId){
    global $fdata, $results_assessCritPVs, $results_assessCritDescs;
    
	$cpArray=Array(); // criteria points array
    $descArray=Array(); // description array
    
    // Get criteria values for criteria created inside assessment
	$facQry='SELECT critlabel, critercode, critervalue, gradepoints FROM '.$fdata->prefix.'critervalues WHERE assessid=\''.$assId.'\' ORDER BY critlabel, gradepoints, critercode';	
    
	$rs=$fdata->db->execute($facQry);    
	if ($rs && $rs->recordcount()>0){
		while ($row = $rs->fetchrow()){
			$critLabel=$row['critlabel'];
			$critCode=$row['critercode'];
			$gradePts=$row['gradepoints'];
            $desc=$row['critervalue'];
			$cpArray[strtolower($critLabel)][strtolower($critCode)]=$gradePts;
            $descArray[strtolower($critLabel)][strtolower($critCode)]=$desc;
		}
	}
    
    // Get criteria values for criteria created as re-usable criteria outside of assessment    
    $facQry='SELECT ac.critlabel, cv.critercode, cv.critervalue, cv.gradepoints FROM '.$fdata->prefix.'assesscriteria AS ac LEFT JOIN '.$fdata->prefix.'critervalues AS cv on cv.CriterId=ac.CriterId WHERE ac.assessid=\''.$assId.'\'';
    if ($fdata->dbtype=='access'){
        $facQry.=' AND NOT ISNULL(ac.criterid)';
    } else {
        $facQry.=' AND ac.criterid!=\'\'';        
    }
    
      
    $rs=$fdata->db->execute($facQry);
	if ($rs && $rs->recordcount()>0){
		while ($row = $rs->fetchrow()){
			$critLabel=$row['critlabel'];
			$critCode=$row['critercode'];
			$gradePts=$row['gradepoints'];
            $desc=$row['critervalue'];
			$cpArray[strtolower($critLabel)][strtolower($critCode)]=$gradePts;
            $descArray[strtolower($critLabel)][strtolower($critCode)]=$desc;
		}
	}
	
    // Add criteria points array to global cash
    $results_assessCritPVs[$assId]=$cpArray;
    
    // Add criteria descriptions array to global cash
    $results_assessCritDescs[$assId]=$descArray;
    
    return (array('pts'=>$cpArray, 'desc'=>$descArray));
}


/**
/* Increments a criteria value by $incBy (can be negative to decrement)
/* NOTE - INCREMENTS BY VALUES NOT POINTS - so C+2 would be A
**/
function AssCritValPoint(&$cpArray, $critLabel, $critCode, $incBy){
	
	$checkArray=$cpArray[$critLabel];
			
	$caKeys=array_keys($checkArray);
	$cCodePos=-1;
	
	// Find crit code position in array
	for ($c=0; $c<count($caKeys); $c++){
		if ($caKeys[$c] == $critCode) {
			$cCodePos=$c;
			break;
		}	
	}
	
	if ($cCodePos==-1){
		return (null);
	}
	
	// Set value position + increment
	$cCodePos+=$incBy;
	
	if ($cCodePos>count($caKeys)){
		$cCodePos=count($caKeys); // cant go above array size
	} else if ($cCodePos<0){
		$cCodePos=0; // cant go below 0
	}
	
	// Find adjusted crit code points
	$checkArrayVals=array_values($checkArray);
	$adjustedPts=$checkArrayVals[$cCodePos];
	
	// Find adjusted crit code value
	$caKeysVals=array_values($caKeys);
	$adjustedVal=$caKeysVals[$cCodePos];	
	
	// New val points array
	$valPoints=array('pts'=>$adjustedPts, 'val'=>$adjustedVal);
		
	return ($valPoints);
			
}


/**
/* CLASS Purpose: Gets student result data for all teaching groups (to do - not finished)
**/
class studentResultDataAll {
	var $studentID;
	var $examID;

}

/**
/* CLASS Purpose: Note this gets student result data for a specific teaching group
**/
class studentResultData {
	var $studentID;
	var $examID;
	var $groupID;	
	var $dataArray; // the result data as an array
	var $dataArrayNice; // result data as an array with criteria names instead of map values
	var $assessID; // the assessment id of the exam
	var $cpArray; // criteria points table
    var $critArray; // criteria array
    var $anyDataSet; // if I can't find a value in the current data set, should I use a previous years data set
	

    /**
    /* Purpose: This function returns all criteria data for a specific student, exam and teaching group
    /* Returns criteria data as an array with map value as key
    /* In:
    /* studentID
    /* examID
    /* groupID
    /* assessID - when not passed in defaults to -999 marker value (indicates no valida assess id)
    **/    
	function studentResultData(&$studentID, $examID, &$groupID, $assessID=-999, $cpArray=array(), $critArray=array(), $anyDataSet=false){
	
		// Set object properties from passed in variables
		$this->studentID=$studentID;
		$this->examID=$examID;
		$this->groupID=$groupID;
        $this->anyDataSet=$anyDataSet;
				
		// set globals
	    global $CFG, $fdata;	

	    // get assessment ID for exam
		if ($assessID==-999){
			$assessID=AssessmentIdForExam($examID);
		}
		
		$this->assessID=$assessID;
	
        // if criteria array not passed in then get it
        if (empty($critArray)){
            // create array of criteria data for this assessment
            $this->critArray=CriteriaForAssessment($this->assessID);
        } else {
            // set class property to passed in array
            $this->critArray=$critArray;
        }
		
		// if criteria points table not passed in then get it
		if (empty($cpArray)){
			// Create criteria points table
			$this->cpArray=AssessmentCriteriaPoints($this->assessID);
		} else {
			// Do not create criteria points table - used passed in array
			$this->cpArray=$cpArray;			
		}
		        
		// get students criteria data for this exam and current data set
        $critData=$this->GetCriteriaDataForStudentExam($studentID, $examID, $groupID, $CFG->mis->cmisDataSet);
		$dataArray=$this->flatCriteriaToArray($critData);			
        
        // Single Pass - If not using any data set, simply set class properties and exit
        if (!$anyDataSet){
            // set object properties
            $this->dataArray=$dataArray;
            $this->dataArrayNice=$this->createNiceDataArray();
            return;
        }
        
        // Multipass
        
        // Get module id for groupId and CURRENT dataset
        $modId=ModuleIdForTeachingGroup($groupID);
        
        $checkArray=$dataArray; // first time round, check the data array produced for single pass
        
        // Check dataArray in current data set for blank values - fill blank values in with data from other data sets if necessary
        $hasblanks=$this->CheckCritDatBlanksAndMerge($dataArray, $checkArray);

        $altDsCritArr=array();
        
        // If critData has blanks, check other data sets for data - try to fill in blanks
        if ($hasblanks){
            $dsArray=DataSetsBeforeCurrent();
            foreach ($dsArray as $ds){
                $altDsCritArr=$this->GetCriteriaDataForStudentSubjectExam($studentID, $examID, $modId, $ds);
                $hasblanks=$this->CheckCritDatBlanksAndMerge($dataArray, $altDsCritArr);
                // After merge, if no blanks in dataArray then break out of loop
                if (!$hasblanks){
                    break;
                }
            }
        }
        
        // set class property dataArray merged with other data sets
        $this->dataArray=$dataArray;
        $this->dataArrayNice=$this->createNiceDataArray();

	}
    
    /**
    /* Purpose: Check criteria data for blanks and merge with main data array if blanks exist there
    /* In By Ref:        
    /*     $dataArray - main array of criteria data
    /* In By Val:
    /*     $checkArray - array to try and fill in blanks of dataArray
    /* Returns:
    /* $hasblanks - boolean does data array still have blanks?
    **/
    function CheckCritDatBlanksAndMerge(&$dataArray, $checkArray){
        $hasblanks=false;
        foreach ($this->critArray as $mv=>$crit){
            // if any value is blank in both checkArray and main dataArray, then set hasblanks to true ELSE merge with existing $dataArray
            if (!isset($dataArray[$mv]) || $dataArray[$mv]==""){                
                if (!isset($checkArray[$mv]) || $checkArray[$mv]==""){
                    $hasblanks=true;
                    //echo ("<h3>$mv is blank in data array and checkarray</h3>");
                } else {
                    // dataArray doesn't have this key so use checkArray's val
                    $dataArray[$mv]=$checkArray[$mv];
                    //echo ("<h3>$mv is blank in data array but not checkarray</h3>");
                }
            }
        }
        //var_dump($dataArray);
        return ($hasblanks);
    }
            


    /**
    /* Function Purpose: Get students criteria data for specific exam by studentid, examid and teaching group id
    /* Returns: String as flat criteria data
    **/ 
    function GetCriteriaDataForStudentExam($studentID, $examID, $groupID, $dataSet){
        global $fdata;
		$facQry='SELECT criteriadata FROM '.$fdata->prefix.'nsturesults WHERE studentid=\''.$studentID.'\' AND examid=\''.$examID.'\' AND groupid='.$groupID.' AND setid=\''.$dataSet.'\' ORDER BY LineNum'; 
		$critData='';
		$result=$fdata->db->execute($facQry);	
		while ($row = $result->fetchrow()){
			$critData.=$row['criteriadata'];
		}
        return ($critData);
    }
    
    /**
    /* Function Purpose: Get students criteria data(s) for specific exam by studentid, examid and module id (subject id)
    /* Returns: Array of criteria data
    **/
    function GetCriteriaDataForStudentSubjectExam($studentID, $examID, $moduleID, $dataSet){
        global $fdata;
		
        // Get teaching groups for moduleID and dataSet - if no teaching groups found then return empty string        
        $tgIds=TeachingGroupIdsForModule($moduleID, $dataSet);
        if (empty($tgIds)){
            return (array());
        }
        
        // Get criteria data for each group and merge any blanks
        $prevCritArr=array();
        $critArr=array();
        $critData="";
        $hasBlanks=true; // until proven otherwise
        foreach ($tgIds as $tgId){
            $critData=$this->GetCriteriaDataForStudentExam($studentID, $examID, $tgId, $dataSet);
            if ($critData!=""){
                $critArr=$this->flatCriteriaToArray($critData);
                if (!empty($critArr)){
                    // check for blanks and merge with previous critArr (if not on first loop)
                    if (!empty($prevCritArr)){
                        $hasBlanks=$this->CheckCritDatBlanksAndMerge($critArr, $prevCritArr);
                    } else {
                        // just check for blanks
                        $hasBlanks=$this->CheckCritDatBlanksAndMerge($critArr, $critArr);
                    }
                }
                if (!$hasBlanks){
                    return ($critArr);
                }
            }
            $prevCritArr=$critArr;
        }
        return ($critArr);        
    }
	
	function AssessmentCriteriaPoints(){
        // deprecated - kept in for compatibility with old code - is now just a wrapper for class property $this->cpArray
		return ($this->cpArray);
	}

    /**
    /*Function Purpose: Converts a flat criteria data string to an array with map values as keys
    **/	
	function flatCriteriaToArray(&$critData){	
		if ($critData==""){return(false);}
		
		$critArray=Array();
		$expArray=explode(chr(10), trim(strval($critData)));
		for ($c=0; $c<count($expArray); $c+=2){
            if ($c+1<count($expArray)){
                $critKey=$expArray[$c];
                $critVal=($c+1)<=count($expArray) ? $expArray[$c+1] : "";
                $critArray[$critKey]=$critVal;
            }
		}
		
		return ($critArray);	
	}

    /**
    /* Function Purpose: Creates a data array with criteria labels as keys (instead of map values) and adds point values
    **/	
	function createNiceDataArray(){
		if (!$this->dataArray){return(false);}
				
		$niceArray=array();
		
		foreach ($this->dataArray as $key => $val){
			$niceKey=critLabelForMapVal($this->assessID, $key); // convert key to nice key
			$niceArray[$niceKey]['val']=$val;
			if (isset($this->cpArray[$niceKey][$val])){
				$niceArray[$niceKey]['pts']=$this->cpArray[$niceKey][$val];
			} else {
				$niceArray[$niceKey]['pts']=0;
			}
		}
		return ($niceArray);
	}
}

function studentResultItem(&$studentID, $examID, &$groupID, $critlabel){

	/**
	/* This function returns 1 specific item of data for a student based on an exam, teaching group, and criteria label
	/* It is an inefficient function when used to retrieve all data items for a result because an SQL transaction
	/* has to occurr for each data item.
	/* Tip: If you want to return all data items for a student result, use the function studentResultData and pull each
	/* value out from the returned array - that would be far faster than this function.
	**/

    global $CFG, $fdata;
	
	// attempt to get criteria array for student, if none returned then return blank string
	$stuResult=new studentResultData($studentID, $examID, $groupID);
	$assessID=$stuResult->assessID;
	
	/*
	$critArray=$stuResult->dataArray;
	
	if (!$critArray){return("");}
		
	// get map values for this assessment and criteria
	$mapVals=mapValsForCritLabel($assessID,$critlabel);
	
	$resultVal=""; // default blank until found
	for ($m=0; $m<count($mapVals); $m++){
		$resultVal=$critArray[$mapVals[$m]];
		if ($resultVal!=""){
			return ($resultVal);
		}
	}
	*/
	
	$critArray=$stuResult->dataArrayNice;	
	if (!$critArray){return("");}	
	$resultVal=$critArray[$critlabel];
	
	return ($resultVal);
}



function getValFromCritData(&$critData, &$mapValue){
	/**
	/* Purpose: Gets a specific value from criteria data 
	**/
	//$critData.=ord(10); // always end critData with chr10
			
	$critArray=explode(chr(10), trim(strval($critData)));
	for ($c=0; $c<count($critArray); $c+=2){
		if ($mapValue==$critArray[$c]){
			if (($c+1)<count($critArray)){
				return ($critArray[$c+1]);
			}
		}
	}
	
}

function examColumns($examCode){
	/**
	/* Purpose: Gets exam columns for a specific exam code
	**/
	
	global $CFG, $fdata;
	$cols=array();
	$facQry='SELECT a.critlabel FROM '.$fdata->prefix.'examinations AS e LEFT JOIN '.$fdata->prefix.'assesscriteria AS a ON a.assessid= e.assessid WHERE e.examid=\''.$examCode.'\'';
	$rs=$fdata->db->execute($facQry);
	while ($row = $rs->fetchrow()){
		$cols[]=$row['critlabel'];
	}
	return ($cols);
}

function getModules($dataset=false){
    global $CFG, $fdata;
    
    // make sure dataset is either passed in or if not, use config data set
    $dataset=$dataset ? $dataset : $CFG->mis->cmisDataSet;    
    
    // Get modules for specific dataset
    $facQry='SELECT moduleid, name FROM '.$fdata->prefix.'module WHERE setid=\''.$dataset.'\'';
    $rs=$fdata->doQuery($facQry, true);
    return ($rs);
}


?>