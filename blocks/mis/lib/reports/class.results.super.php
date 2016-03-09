<?php

//
// Author: Guy Thomas
// Date:2007 - 09 - 18
// Purpose: SUPER Class for results
//

require_once("lib_results.php");
require_once("class.propsbyparam.php");

class superresults extends propsbyparam{

    //
    // Protected Class Properties
    //    
    protected $_rawResultsSQL; // sql used to generate raw results
    protected $_rawResults; // raw results array from sql query
    protected $_studentsSQL; // sql used to generate a list of students
    protected $_studentsById; // array hashsed by studentid based on result of _studentsSQL
    protected $_resultsBlock=array(); // current results block - this is reset at the start of every new block
    protected $_exams=array(); // array of exam records
    protected $_assessIdsByExamIds=array(); // array of assessment ids hashed by exam ids
    
    //
    // Public Class Properties (Set by class)
    //
    var $resultsArray=array(); // array of results data with stupid Facility multi criteriadata lines collapsed into 1 line
    var $numResults=0;
    var $baseSQL='';
        
    //
    // Public Optional Class Properties (Set by params)
    //
    var $dataSets=array(); // empty OR datasets to use when recovering results data (ordered by most relevant first) * system will only return maximum of 1 result row regardless of how many data sets there are - it will however merge them if not all values are complete.
    var $primarySet=''; // primary data set
    var $anyDataSet=true; // if true, will check all datasets for results data
    var $anyDSCriteriaInc=array(); // array of criteria to include on merge
    var $anyDSCriteriaExc=array(); // array of criteria to exclude on merge
    var $examIds=array(); // array of exams (filters results if passed as param)
    var $assessIds=array(); // array of assessids (filters results if passed as param)    
    var $assessCrits=array(); // array of assessment criteria values
    var $assessCritPVs=array(); // array of assessment criteria points values
    var $assessCritDescs=array(); // array of assessment criteria value descriptions
    var $crsYrs=array(); // array of course years (filters results if passed as param)
    var $moduleIds=array(); // array of moduleIds (filters results if passed as param)
    var $groupIds=array(); // array of teahing groupIds (filters results if passed as param)
    var $groupCodes=array(); // array of teahing groupCodes (filters results if passed as param)
    var $classGroupIds=array(); // array of class group codes (filters results if passed as param)
    var $studentIds=array(); // array of studentIds (filters results if passed as param)    
    var $groupOnModule=true; // if true, groups all results into one module
    var $cfg;
    var $fdata;
    var $sqlOrder='';
    var $sqlAppendWhere=''; // append sql after where
    var $numBlocks=0;
    var $debugOutToScreen=false; // debug output to screen ?
    var $debugLogId=0;
    var $getComments=false; // get comments for criteria linked to comments
    var $valsToDescs=false; // convert values codes to full value descriptions?

	//
	// Class Constructor
	//
	function superresults($params=array()){
    
        global $fdata, $CFG;
                
        // Set default base sql (if not already set)
        $this->baseSQL=$this->baseSQL!='' ? $this->baseSQL : 'SELECT  ns.*, nstu.surname, nstu.forename, nstu.forename2, nstu.calledname, nstu.dateofbirth, nstu.stusex, stu.classgroupid, stu.courseyear, tg.groupcode FROM (('.$fdata->prefix.'nsturesults AS ns LEFT JOIN '.$fdata->prefix.'students AS stu ON stu.studentid=ns.studentid) LEFT JOIN '.$fdata->prefix.'teachinggroups AS tg ON (ns.groupid=tg.groupid AND ns.setid=tg.setid)) LEFT JOIN '.$fdata->prefix.'nstupersonal AS nstu ON (nstu.studentid=ns.studentid AND nstu.setid=ns.setid)';
        
        // Set default sql order (if not already set)
        $this->sqlOrder=$this->sqlOrder!='' ? $this->sqlOrder : ' ORDER BY ns.StudentId, ns.ExamId, ns.ModuleId, ns.AssessId, ns.SetId, ns.GroupId, ns.LineNum';
        
        // Set properties by parameters     
		parent::propsbyparam($params);
        
		// If config hasn't been set then try global variable
		if(!isset($this->cfg)){
			$this->cfg=$CFG->mis;
		}
        
        // set assesslabs to empty array if not already set
        if (!isset($CFG->mis->assesslabs)){
            $CFG->mis->assesslabs=array();
        }        
        
		// If fdata hasn't been set then try global variable
		if(!isset($this->fdata)){
            if (isset($GLOBALS['fdata'])){
                $this->fdata=$GLOBALS['fdata'];
            } else {
                $GLOBALS['fdata']=new facilityData();
                $this->fdata=$GLOBALS['fdata'];
            }
		}
        
        //set data sets property
        $this->setDataSets();
        
        // If primary set is not defined then use the first set in dataSets
        $this->primarySet=$this->primarySet!='' ? $this->primarySet : $this->dataSets[0];
        $this->primarySet=$this->removeFirstUnderscore($this->primarySet);
        $this->generateResultsArray();
    }
    
    //
    // Purpose: Forces the dataSets property to have a value
    //
    protected function setDataSets(){   
        // If dataSets is empty, set it to current dataset or all datasets before and including current (if anyDataSet is true)
        if (empty($this->dataSets)){
            // get primary or config data set
            $firstSet=$this->primarySet!='' ? $this->primarySet : $this->cfg->cmisDataSet;        
            // If any data set is relevant, build array of all datasets before and including first dataset
            if ($this->anyDataSet){
                $this->dataSets=array_unique(array_merge(array($firstSet), DataSetsBeforeSpecific($firstSet)));
            } else {
                // Else, set dataSets to primary or config data set
                $this->dataSets=array($firstSet);
            }
        } else {     
            if ($this->anyDataSet){
                $this->dataSets=array_unique(array_merge($this->dataSets, DataSetsBeforeSpecific($this->dataSets[0])));
            }
        }
    }    
    
    //
    // Purpose: Generate resultsArray
    //
    function generateResultsArray(){               
    
        // Generate raw results
        $this->_generateRawResults();
        
        // Return empty array if no results
        if (empty($this->_rawResults)){
            return (array());
        }

        // Set empty class properties from raw results
        $this->_setEmptyClassProperties();       

        // Override Criteria Labels with those in moodle mis config
        $this->_overrideCritLabels();        
        
        // Convert raw results to results array        
        $prevConcatKey='';
        foreach ($this->_rawResults as $row){
            // force row keys to lower case
            $row=array_change_key_case($row, CASE_LOWER);
            
            // force values to be a string by prefixing with _
            $examId='_'.strtolower($row['examid']); // remove case sensitivity
            $studentId='_'.$row['studentid'];            
            $moduleId='_'.$row['moduleid'];
            $setId='_'.$row['setid'];
            $groupId='_'.$row['groupid'];
            
            $concatKey=$examId.$studentId.$moduleId.$setId.$groupId;
            if ($concatKey!=$prevConcatKey && $prevConcatKey!=''){
                $this->outputLog('new result block detected '.$concatKey);
                $this->_procResultsBlock(); // dump results block into to $resultsArray
            }
            
            // add result row to cash
            $this->_resultsBlock[]=$row;
            
            $prevConcatKey=$concatKey;
            
        }
        // process last resultsblock
        $this->_procResultsBlock();
        
        // set number of results
        $this->numResults=count($this->resultsArray);
               
    }

    //
    // Purpose: Get exams for exam ids in results (hashed by examids)
    // @returns array of row objects hashed by _(examid),  note that examid is lower case
    //
    function getExams(){
        global $fdata;
        
        // If exams already set then just return it
        if (!empty($this->_exams)){
            return ($this->_exams);
        }
                        
        // Just get all examinations, rather than a complicated or multiple sql statements (this is faster)
        $sql='SELECT * FROM '.$fdata->prefix.'examinations';
        $rs=$fdata->doQuery($sql, true);
        
        $exams=array();
        foreach ($rs as $row){
            $exams['_'.strtolower($row->examid)]=$row;
        }
        // create subset - i.e. only return exam rows that are used (or filtered by)
        $retexams=array();        
        foreach ($this->examIds as $examid){
            $retexams[strtolower($examid)]=$exams[strtolower($examid)];
        }
        
        $this->_exams=$retexams;
        return ($retexams);
    }
    
    //
    // Purpose: Get array of assessment ids hashed by exam
    //
    function getAssessIdsByExamIds(){
    
        // If assessIdsByExamIds already set then simply return it
        if (!empty($this->_assessIdsByExamIds)){
            return ($this->_assessIdsByExamIds);
        }
        $exams=$this->getExams();
        
        foreach ($exams as $exam){            
            $this->_assessIdsByExamIds[strtolower($exam->examid)]=strtolower($exam->assessid);
        }
        return ($this->_assessIdsByExamIds);
    }
    
    //
    // Purpose: Get criteria by exam id
    //
    function getCriteriaByExamId($examid){
        // pre-pend examid with underscore if not already
        $examid=$examid[0]!='_' ? '_'.$examid : $examid;
        $exams=$this->getExams();
        $assid=$exams[$examid]->assessid;
        $criteria=$this->assessCrits[$assid];         
        return ($criteria);
    }
    
    
    //
    // Purpose: Parses and moves results within block into $resultsArray
    //
    private function _procResultsBlock(){
        // Go through results block and create criteria array
        $critData='';
        foreach ($this->_resultsBlock as $row){
            $this->outputLog ("stu = ".$row['studentid']." exam = ".$row['examid']." module = ".$row['moduleid']." dataset = ".$row['setid']." groupid = ".$row['groupid']." line num = ".$row['linenum']);
            $critData.=$row['criteriadata'];            
        }
                        
        // If row is not set then abort
        if (!isset($row)){
            return (false);
        }
        
        // Add student id to studentids array
        if (!in_array($row['studentid'], $this->studentIds)){
            $this->studentIds[]=$row['studentid'];
        }
        
        $assId=$row['assessid'];

        //$this->outputLog(var_export($this->assessCrits[$assId]));
                
        // Convert flat criteria data to array
        if ($critData!=""){            
            $critArr=$this->flatCriteriaToCompArr(
                $this->_resultsBlock[0],
                $critData, $assId, true
            );
            $critArrByLabel=$this->flatCriteriaToCompArr(
                $this->_resultsBlock[0],                
                $critData, $assId, false
            );            
        } else {
            return;
        }
        
        // Add criteria array to row
        $row['criteriaarray']=$critArr;
        $row['criteriaarraybylabel']=$critArrByLabel;
        
        // force values to be a string by prefixing with _
        $examId='_'.strtolower($row['examid']); // make examid lower case (remove case sensitivity)
        $studentId='_'.$row['studentid'];
        $moduleId='_'.$row['moduleid'];
        $setId='_'.$row['setid'];
        $groupId='_'.$row['groupid'];
        
        // Add row to resultsArray
        $this->resultsArray[$examId][$studentId][$moduleId][$setId][$groupId]=$row;
  
        $this->_resultsBlock=array(); // empty block
        
        $this->numBlocks++;
        
        $this->outputLog ('<p>'.$examId.$studentId.$moduleId.$setId.$groupId.'</p>');
    }
    
    //
    // Purpose: Get criteria item by label for specific exam
    //
    function getCritItemForExam($examId, $critLabel, $dataArray=array()){
        if (empty($dataArray)){
            $dataArray=$this->getExamResults($examId);
        }
        
        foreach ($dataArray as $row){
            if ($row['ExamId']==$examId){
                foreach ($row['criteriaarray'] as $mapVal=>$crit){
                    if (strtolower($crit['label'])==strtolower($critLabel)){
                        // found criteria item for critLabel - return crit item
                        return ($crit);
                    }
                }
            }
        }
        
        return (false);
        
    }

    //
    // Function Purpose: Converts a flat criteria data string to an array with map values (or labels) as keys and values containing setId, assessId, val, pts
    //
    function flatCriteriaToCompArr(&$critRow=array(), &$critData='', $assessId='', $hashbymap=true){
    
        $critArr=$this->assessCrits[$assessId];
        $cpArr=$this->assessCritPVs[$assessId];
        $cdescArr=$this->assessCritDescs[$assessId];
    
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
                
                $comment=false;
                if ($this->getComments){                   
                
                    $critcomment=false;                    
                    // Is current criteria a comment check by critcomp and critertype
                    if (isset($critArr['bymap'][$mapval]['critcomp'])){
                        $critcomp=$critArr['bymap'][$mapval]['critcomp'];
                        if ($critcomp=='comment'){
                            $critcomment=true;
                        }
                    }
                    if (isset($critArr['bymap'][$mapval]['critertype'])){
                        if ($critArr['bymap'][$mapval]['critertype']==5){ // I'm assuming 5 is a comment type
                            $critcomment=true;
                        }                        
                    }                    
                    
                    //if its a comment then get the comment
                    if ($critcomment){
                        $comment=$this->getCommentById($critVal);
                        if ($comment){
                            // replace critVal (comment id) with actual comment
                            $critVal=$comment;
                        }
                    }
                }

                // get criteria label
                $critLabel=critLabelForMapVal($assessId, $mapval, $critArr);
                
                // Should we try to convert the value to its full description?
                if (!$comment && $this->valsToDescs){       
                    if (isset($cdescArr[$critLabel][$critVal])){
                        $valdesc=$cdescArr[$critLabel][$critVal];                    
                        $critVal=$valdesc!='' ? $valdesc : $critVal;
                    }
                }
                
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
    
    //
    // Purpose: Get comment by comment id
    //
    function getCommentById($id){
        global $fdata;
        $id=intval($id);
        $sql='SELECT * FROM '.$fdata->prefix.'sturescomm WHERE commentid='.$id.' ORDER BY linenum';
        $rs=$fdata->db->execute($sql);
        $comment='';

        while ($row=$rs->fetchrow()){
            $row=array_change_key_case($row, CASE_LOWER);
            $comment.=$row['commdata'];
        }
        return ($comment);
    }
    
    //
    // Purpose: Forces string to start with an underscore
    //
    function forceUnderscore($srcStr){
        $retVal=substr($srcStr,0,1)!='_' ? '_'.$srcStr : $srcStr;
        return ($retVal);
    }
    
    //
    // Purpose: Removes first underscore from string
    //
    function removeFirstUnderscore($srcStr){
        return(substr($srcStr, 0, 1)!='_' ? $srcStr : substr($srcStr, 1, (strlen($srcStr)-1)));    
    }
    
    //
    // Purpose: Gets all student results for a specific Exam
    //
    function getExamResults($reqExamId, $outputArray=array(), $hashBy=array(), $hashLcase=true){
        // force requested exam id to be prefixed with an underscore (as thats how they are hashed).
        
        $reqExamId=$this->forceUnderscore($reqExamId);
        $primarySet=$this->forceUnderscore($this->primarySet);
        $blends=0; // number of blends carried out between datasets and groups
        
        foreach ($this->resultsArray as $examId=>$stuArray){
            if ($examId==$reqExamId){
                foreach ($stuArray as $stuId=>$modArray){
                    foreach($modArray as $moduleId=>$setArray){
                        // Try get result for primarySet - if it doesn't have data then try other data sets
                        if (isset($this->resultsArray[$examId][$stuId][$moduleId][$primarySet]) && is_array($this->resultsArray[$examId][$stuId][$moduleId][$primarySet])){
                            $resRow=current($this->resultsArray[$examId][$stuId][$moduleId][$primarySet]);
                            // Merge row with other datasets and groups if any criteria is blank
                            $blends+=$this->MergeExamModSetResult($resRow, $this->resultsArray[$examId][$stuId][$moduleId]);
    
                        } else if ($this->anyDataSet){
                            foreach ($this->resultsArray[$examId][$stuId][$moduleId] as $setId=>$row){
                                if (isset($this->resultsArray[$examId][$stuId][$moduleId][$setId])){
                                    foreach ($this->resultsArray[$examId][$stuId][$moduleId][$setId] as $groupId=>$row){                                   

                                        // * Note - there should only be one row per group in $this->resultsArray
                                        if (isset($this->resultsArray[$examId][$stuId][$moduleId][$setId][$groupId])){
                                            $resRow=$this->resultsArray[$examId][$stuId][$moduleId][$setId][$groupId];
                                            // Merge row with other datasets and groups if any criteria is blank
                                            $blends+=$this->MergeExamModSetResult($resRow, $this->resultsArray[$examId][$stuId][$moduleId]);
                                
                                            if ($this->groupOnModule){
                                                // don't bother getting results for any other groups
                                                break;
                                            }
                                        }
                                    }
                                    break;
                                }                        
                            }
                        }
                        
                        // set the hasharray to the values of variables in $hashBy array
                        $hashes=array();
                        foreach ($hashBy as $hb){
                            $hashes[]='\''.$$hb.'\'';
                        }
                        
                        // Add results row to output array.
                        $this->_addResultsRowToOutputArray($resRow, $outputArray, $hashes, $hashLcase);
                    }
                }
            }
        }
        $this->outputLog('Number of blends carried out ='.$blends);
        return ($outputArray);
    } 
    
    //
    // Purpose: Adds results row to output array and adds hashed key(s) if necessary
    //
    private function _addResultsRowToOutputArray(&$resRow, &$outputArray, $hashBy=array(), $hashLcase=true){
        if (empty($hashBy)){
            $outputArray[]=$resRow;
        } else {
            $hashStr='';
            foreach ($hashBy as $hash){
                // force case to upper on hash
                if ($hashLcase){
                    $hash=strtolower($hash);
                }
                $hashStr.='['.$hash.']';
            }                    
            $evalStr='$outputArray'.$hashStr.'[]=$resRow;';
            eval ($evalStr);
        }
        return ($outputArray);
    }
    
    //
    // Purpose: Gets all results
    //
    function getAllResults(){
        $outputArray=array();
        foreach ($this->examIds as $examId){
            $outputArray=$this->getExamResults($examId,$outputArray);
        }
        return ($outputArray);
    }
    
    //
    // Purpose: Gets all results and hashes by exam
    //
    function getAllResultsByExam(){
        $outputArray=array();
        foreach ($this->examIds as $examId){
            $outputArray=$this->getExamResults($examId,$outputArray, array('examId'));
        }

        return ($outputArray);
    }
    
    //
    // Purpose: Gets all results and hashes by student
    //
    function getAllResultsByStudent(){
        $outputArray=array();
        foreach ($this->examIds as $examId){
            $outputArray=$this->getExamResults($examId,$outputArray, array('stuId'));        
        }

        return ($outputArray);
    }
    
    
    //
    // Purpose: Merge empty exam criteria with other datasets and groups for the same exam
    //
    function MergeExamModSetResult(&$resRow, $setData){
        $assId=$resRow['assessid'];        
        $mapVal='';
        $examId='_'.strtolower($resRow['examid']);
        $moduleId='_'.$resRow['moduleid'];
        $resGroupId='_'.$resRow['groupid'];
        $resSetId='_'.$resRow['setid'];
        $stuId='_'.$resRow['studentid'];
        
        $blends=0; // number of blends that has taken place
        
        $primarySet=$this->forceUnderscore($this->dataSets[0]);
        
        foreach ($this->assessCrits[$assId]['bymap'] as $crit){
            $mapVal=$crit['mapvalue'];
            $mapLab=$crit['critlabel'];
            
            // make sure current criteria is ok to include (inclusion list empty and not excluded or specifically included)
            $mergeCrit=false;
            if (empty($this->anyDSCriteriaInc)){
                if (empty($this->anyDSCriteriaExc)){
                    $mergeCrit=true;
                } else {
                    $mergeCrit=!in_array($mapLab, $this->anyDSCriteriaExc);
                }
            } else if (in_array($mapLab, $this->anyDSCriteriaInc)){
                $mergeCrit=true;
            }

            if ($mergeCrit){
                $this->outputLog($mapVal.' ('.$mapLab.') can be merged with other data sets');
            }
 
            if ((!isset($resRow['criteriaarray'][$mapVal]['val']) || $resRow['criteriaarray'][$mapVal]['val']=='')){
                // map val is empty try other sets and groups               
                foreach ($setData as $setId=>$row){
                    if ($this->anyDataSet || $setId==$primarySet){
                        if ($this->anyDataSet){
                            $this->outputLog($mapVal.' ('.$mapLab.') is empty - searching other groups and datasets (Current dataset being searched is '.$setId.')');
                        } else {
                            $this->outputLog($mapVal.' ('.$mapLab.') is empty - searching other groups in '.$setId);
                        }
                        foreach ($this->resultsArray[$examId][$stuId][$moduleId][$setId] as $groupId=>$row){
                            // only check altrow if groupid or setid are different to resrows
                            if ($groupId!=$resGroupId OR $resSetId!=$setId){
                                if (isset($this->resultsArray[$examId][$stuId][$moduleId][$setId][$groupId])){
                                    // alternative row found
                                    $altRow=$this->resultsArray[$examId][$stuId][$moduleId][$setId][$groupId];
                                    
                                    //$this->outputLog($mapVal.' ('.$mapLab.') alternative row found in setid '.$setId.' and groupId '.$groupId.' (Primary set ='.$primarySet.' and mergeCrit='.$mergeCrit.')');                                    
                                    
                                    // if alternative row is in primary data set or criteria can be merged then do merge
                                    if ($setId==$primarySet || $mergeCrit){
                                        $this->outputLog($mapVal.' ('.$mapLab.') alternative row found in setid '.$setId.' and groupId '.$groupId.' (Primary set ='.$primarySet.')'); 
                                        // if it has a value for mapval then insert it into $resRows empty mapval
                                       
                                        if (isset($altRow['criteriaarray'][$mapVal]['val']) && $altRow['criteriaarray'][$mapVal]['val']!=''){
                                        
                                            $mergeVal=$altRow['criteriaarray'][$mapVal]['val'];
                                            $mergeSetId=$altRow['criteriaarray'][$mapVal]['setid'];
                                            $this->outputLog('Blending '.$examId.'>'.$stuId.'>'.$moduleId.'>_'.$resRow['setid'].'>_'.$resRow['groupid'].' with mapVal '.$mapVal.' ('.$mergeVal.') from '.$examId.'>'.$stuId.'>'.$moduleId.'>_'.$altRow['setid'].'>_'.$altRow['groupid']);
                                            
                                            
                                            // Blend CriteriaArray (by map values)
                                            $resRow['criteriaarray'][$mapVal]=$altRow['criteriaarray'][$mapVal];                                            
                                            // Blend CriteriaArray (by label values)
                                            $resRow['criteriaarraybylabel'][$mapLab]=$altRow['criteriaarraybylabel'][$mapLab];
                                            
                                            $this->outputLog('Blended criteria is '.print_r($resRow['criteriaarray'], true));
                                            $blends++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $this->outputLog($mapVal.' is not empty');            
            }
        }
        // return the number of blends which took place
        return ($blends);
    }
    

    
    //
    // Purpose: Sets empty class properties - e.g. if examIds not specified, it will set them according to results
    //
    private function _setEmptyClassProperties(){
    
        global $results_assessCrits, $results_assessCritPVs, $results_assessCritDescs;
    
        // If raw results doesn't have any records then exit
        if (!$this->_rawResults || count($this->_rawResults)==0){
            return false;
        }
        
        // If examIds not already set, extract them from rawResults
        $this->_setClassPropFromRawResults($this->examIds, 'examid');
        
        // abort if there are no exam ids - no exam ids means no results!
        if (empty($this->examIds)){
            return false;
        }
        
        // If assessIds not already set, extract them from rawResults       
        $this->_setClassPropFromRawResults($this->assessIds, 'assessid');     
        
        // If moduleIds not already set, extract them from rawResults       
        $this->_setClassPropFromRawResults($this->moduleIds, 'moduleid');
        
        // If groupIds not already set, extract them from rawResults
        $this->_setClassPropFromRawResults($this->groupIds, 'groupid');
        
        // If classGroupIds not already set, extract them from rawResults
        $this->_setClassPropFromRawResults($this->classGroupIds, 'classgroupid');        
        
        // Set assessment criteria if not already set
        foreach ($this->assessIds as $assId){
            // remove underscore prefix from assessment id
            $assId=substr($assId,0,1)=='_' ? substr($assId,1,strlen($assId)-1) : $assId;            
            if (!isset($this->assessCrits[$assId])){
                // try global assess criteria
                if (isset($results_assessCrits[$assId])){
                    $this->assessCrits[$assId]=$results_assessCrits[$assId];
                } else {
                    // Get assessment criteria and add to global
                    $this->assessCrits[$assId]=CriteriaForAssessment($assId);
                    $results_assessCrits[$assId]=$this->assessCrits[$assId];
                }
            } else {
                //$this->outputLog ("Skipping creation of $assId criteria");
            }
        }
        
        
        // Set assessment criteria point values if not already set
        foreach ($this->assessIds as $assId){
            // remove underscore prefix from assessment id
            $assId=substr($assId,0,1)=='_' ? substr($assId,1,strlen($assId)-1) : $assId;
            
            if (!isset($this->assessCritPVs[$assId]) || empty($this->assessCritPVs[$assId])){ 
                // get assessment criteria points
                $this->assessCritPVs[$assId]=AssessmentCriteriaPoints($assId);                
            } else {
                $this->outputLog ('Skipping creation of '.$assId.' criteria points');
            }
            
            if (!isset($this->assessCritDescs[$assId]) || empty($this->assessCritDescs[$assId])){
                // get assessment criteria value descriptions
                $this->assessCritDescs[$assId]=AssessmentCriteriaDescs($assId);
            } else {
                $this->outputLog ('Skipping creation of '.$assId.' criteria value descriptions');
            }
        }
    }
    

    
    //
    // Purpose: Set class property array from raw results (if not already set)
    // In:
    // class property BY REF
    // fieldname as string
    //
    private function _setClassPropFromRawResults(&$property, $fieldname){
        // If moduleIds not already set, extract them from RawResults
        if (empty($property)){
            foreach ($this->_rawResults as $row){
                // force row keys to lower case
                $row=array_change_key_case($row, CASE_LOWER);
                $resVal='_'.$row[$fieldname]; // force result value to be a string by prefixing with _            
                if (!in_array($resVal, $property)){                    
                    $property[]=$resVal;
                }
            }
        } else {
            // just force property members to be prefixed with _
            for ($p=0; $p<count($property); $p++){
                $property[$p]='_'.$property[$p];
            }
        }
    }
    
    //
    // Purpose: Generate raw results data
    //
    private function _generateRawResults(){
    
        global $fdata;
        
        $sql=$this->baseSQL;
       
        $tabs=array('ns');
        
        // refine to only include specific datasets
        $this->_refineWHERE($sql, 'setid', $this->dataSets, $tabs);
        $primarySetArr=array($this->primarySet);
        $this->_refineWHERE($sql, 'setid', $primarySetArr, array('stu'));

        // refine to only include specific yeargroups
        if (!empty($this->crsYrs)){
            $this->_refineWHERE($sql, 'courseyear', $this->crsYrs, array('stu'), true);
            // removed filter below because it stops you from looking at targets from last year if you use anyDataSet=true
            //$this->_refineWHERE($sql, 'crsyear', $this->crsYrs, $tabs, true);
        }        
        
        // refine to only include specific classgroups
        $this->_refineWHERE($sql, 'classgroupid', $this->classGroupIds, array('stu'));
        
        // refine to only include specific students
        $this->_refineWHERE($sql, 'studentid', $this->studentIds, $tabs);        

        // refine sql if specific assessments requested
        if (!empty($this->assessIds)){        
            $this->_refineWHERE($sql, 'assessid', $this->assessIds, $tabs);
        }        
        
        // refine sql if specific exams requested
        if (!empty($this->examIds)){        
            $this->_refineWHERE($sql, 'examid', $this->examIds, $tabs);
        }
       
        // refine sql if specific modules requested
        if (!empty($this->moduleIds)){
            $this->_refineWHERE($sql, 'moduleId', $this->moduleIds, $tabs);
        }
        
        // refine sql if specific group ids requested
        if (!empty($this->groupIds)){
            $this->_refineWHERE($sql, 'groupId', $this->groupIds, array('tg'), true);
        }
        
        // refine sql if specific group codes requested
        if (!empty($this->groupCodes)){
            $this->_refineWHERE($sql, 'groupCode', $this->groupCodes, array('tg'));
        }
        
        // append further filters to where
        $sql.=$this->sqlAppendWhere;
        
        // add ordering to sql
        $sql.=$this->sqlOrder;

        // add final sql to rawResultsSQL
        $this->_rawResultsSQL=$sql;          
        $this->outputLog('<p>SQL is:<br />'.$sql.'</p>');
        
        // execute final sql
        $result=$fdata->db->execute($sql);
        if (!$result){
            $this->outputLog('<p>sql executed but returned nothing</p>');
            return (array());
        }
        $this->outputLog ('<p>sql executed and returned '.$result->RecordCount().' rows</p>');
                        
                        
        if ($this->cfg->cmisDBType=='mssql'){
            $newResArr=$result->GetArray();
            $this->outputLog ('<p>Num of records in array is '.count($newResArr).'</p>');
        } else {
            $newResArr=$result;
        }
                        
        $this->_rawResults=$newResArr;     
    }
    
    //
    // Purpose: Create sql to refine WHERE filteria
    //
    private function _refineWHERE(&$sql, $fldName, &$filtArr, $tabs=array(), $asInt=false){
  
            // return null string if filter array is empty
            if (empty($filtArr)){
                return('');
            }        

            if (strpos(strtolower($sql),' where ')===false){
                $sql.=' WHERE ';
                $andStr=''; // no filtering has occurred yet, so don't bother with AND
            } else {
                $andStr=' AND '; // filtering has already occurred so use AND
            }
    
            $sqlFilter=count($filtArr)>1 ? $andStr.'(' : $andStr;
       
            $pass=0;
            $qtStr=$asInt ? '' : '\''; // if integer dont use quotes
            foreach ($filtArr as $filt){
                if ($filt!=''){
                    // remove underscore prefix from filter value
                    $filt=substr($filt,0,1)=='_' ? substr($filt,1,strlen($filt)-1) : $filt;
                    $sqlFilter.=$pass>0 ? ' OR ' : '';               
                    if (empty($tabs)){        
                        $sqlFilter.=$fldName.'='.$qtStr.$filt.$qtStr;                
                    } else {
                        $sqlFilter.='(';
                        $tabpass=0;
                        foreach ($tabs as $tab){                                    
                            $sqlFilter.=$tabpass>0 ? ' AND ' : '';

                            /* we don't need to prefix the table because the table should be a table alias- code below removed
                            if (preg_match('/^'.$this->fdata->prefix.'/i')==0){
                                $tabstr=$this->fdata->prefix.$tab;
                            } else {
                                $tabstr=$tab;
                            }
                            $sqlFilter.=$tabstr.'.'.$fldName.'='.$qtStr.$filt.$qtStr;
                            */

                            $sqlFilter.=$tab.'.'.$fldName.'='.$qtStr.$filt.$qtStr;
                            $tabpass++;
                        }
                        $sqlFilter.=')';
                    }
                    $pass++;
                }
            }
            $sqlFilter.=count($filtArr)>1 ? ')' : '';

            $sql.=$sqlFilter;

            return ($sqlFilter);
    }

    //
    // Override labels with database config   
    //
    private function _overrideCritLabels(){
        global $results_assessCrits;
        foreach ($this->assessIds as $assId){
            $assId=substr($assId,0,1)=='_' ? substr($assId,1,strlen($assId)-1) : $assId;
            foreach ($this->assessCrits[$assId]['bymap'] as $mapval=>$crit){
                $label=$crit['critlabel'];
                $critconf=db_mis::get_assessment_criteria($this->primarySet, $assId, $label);
                if (isset($critconf->labelcustom)){
                    $crit['critlabeldisp']=$critconf->labelcustom;
                    $this->assessCrits[$assId]['bymap'][$mapval]=$crit;
                    $results_assessCrits[$assId]['bymap'][$mapval]=$crit;
                    $this->assessCrits[$assId]['bylabel'][$crit['critlabel']]=$crit;
                    $results_assessCrits[$assId]['bylabel'][$crit['critlabel']]=$crit;
                }
            }
        }
    }
    
    //
    // Purpose - Generate array of students
    // NOTE: THIS WILL BE CALLED BY RESULT CLASSES THAT GENERATE A SEPARATE STUDENT ARRAY (IE NOT USE RESULTS ARRAY FOR STUDENT DETAILS)
    //
    function generateStudentArray($sql){
        global $fdata;
        $this->_studentsSQL=$sql; // sql used to generate a list of students
        $result=$fdata->db->execute($sql);
        while ($row=$result->fetchrow()){
            // make row hashes lower case first
            $row=array_change_key_case($row, CASE_LOWER);
            // add row to studentsbyid array (hashed by student id)
            $this->_studentsById[$row['studentid']]=$row;
        }
    }

    //
    // Purpose: return studentsById array
    // NOTE: THIS WILL ONLY WORK FOR RESULT CLASSES THAT GENERATE A SEPARATE STUDENT ARRAY (IE NOT USE RESULTS ARRAY FOR STUDENT DETAILS)
    //
    function getStudentsById(){
        return ($this->_studentsById);
    }

    //
    // Purpose - output all debug stuff
    //
    function outputLog($logentry){
        $this->debugLogId++;
        if ($this->debugOutToScreen){
            echo ('<p>#'.$this->debugLogId.': '.$logentry.'</p>');
        }
    }
}