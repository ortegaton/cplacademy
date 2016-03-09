<?php

//
// Author: Guy Thomas
// Date:2007 - 09 - 18
// Purpose: Returns student results object
//
// Requires:
//    class.propsbyparam.php
//    lib_results.php
//    cfg.php (config file)
//
//

require_once("class.propsbyparam.php");

class studentresults extends propsbyparam{

    //
    // Prviate Class Properties
    //
    var $_rawResults; // raw results array from sql query
    var $_rawResultsSQL; // sql used to generate raw results
    var $_resultsBlock=array(); // current results block - this is reset at the start of every new block

    //
    // Mandatory Class Properties
    //
    var $studentId="";

    //
    // Public Class Properties (Set by class)
    //
    var $resultsArray=array(); // array of results data with stupid Facility multi criteriadata lines collapsed into 1 line
    var $numResults=0;
    
    //
    // Public Optional Class Properties (Set by params)
    //
    var $dataSets=array(); // empty OR datasets to use when recovering results data (ordered by most relevant first) * system will only return maximum of 1 result row regardless of how many data sets there are - it will however merge them if not all values are complete.
    var $anyDataSet=true; // if true, will check all datasets for results data
    var $examIds=array(); // array of exams (filters results if passed as param)
    var $assessIds=array(); // array of assessids (filters results if passed as param)
    var $assessCrits=array(); // array of assessment criteria values
    var $assessCritPVs=array(); // array of assessment criteria points values
    var $crsYrs=array(); // array of course years (filters results if passed as param)
    var $moduleIds=array(); // array of moduleIds (filters results if passed as param)
    var $groupIds=array(); // array of groupIds (filters results if passed as param)
    var $groupOnModule=true; // if true, groups all results into one module
    var $cfg;
    var $fdata;

	//
	// Class Constructor
	//
	function studentresults($studentId, $params=array()){
	
        global $CFG;
    
        // Set private properties
        $this->studentId=$studentId;
    
        // Set properties by parameters
		parent::propsbyparam($params);
        
		// If config hasn't been set then try global variable
		if(!isset($this->cfg)){
			$this->cfg=$CFG->mis;
		}
		// If msdb hasn't been set then try global variable
		if(!isset($this->fdata)){
			$this->fdata=$GLOBALS['fdata'];
		}
        
        // If dataSets is empty, set it to current dataset or all datasets before and including current (if anyDataSet is true)
        if (empty($this->dataSets)){
            // If any data set is relevant, build array of all datasets before and including current dataset
            if ($this->anyDataSet){
                $this->dataSets=array_merge(array($this->cfg->cmisDataSet), DataSetsBeforeCurrent());
            } else {
                // Else, set dataSets to current data set
                $this->dataSets=array($this->cfg->cmisDataSet);
            }
        } else {     
            if ($this->anyDataSet){
                $this->dataSets=array_unique(array_merge($this->dataSets, DataSetsBeforeSpecific($this->dataSets[0])));
            }
        }
        
        $this->generateResultsArray();
    }
    
    //
    // Purpose: Generate resultsArray
    //
    function generateResultsArray(){    
    
        // Generate raw results
        $this->_generateRawResults();
    
        // Set empty class properties from raw results
        $this->_setEmptyClassProperties();                      
        
        // Convert raw results to results array        
        $prevConcatKey='';
        foreach ($this->_rawResults as $row){           

            // force values to be a string by prefixing with _
            $examId='_'.$row['ExamId'];
            $moduleId='_'.$row['ModuleId'];
            $setId='_'.$row['SetId'];
            $groupId='_'.$row['GroupId'];
        
            $concatKey=$examId.$moduleId.$setId.$groupId;
            if ($concatKey!=$prevConcatKey && $prevConcatKey!=''){
                //$this->outputLog('new result block detected '.$concatKey);
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
    // Purpose: Parses and moves results within block into $resultsArray
    //
    private function _procResultsBlock(){
        // Go through results block and create criteria array
        $critData='';
        foreach ($this->_resultsBlock as $row){
            //$this->outputLog ("stu = ".$row['StudentId']." exam = ".$row['ExamId']." module = ".$row['ModuleId']." dataset = ".$row['SetId']." groupid = ".$row['GroupId']." line num = ".$row['LineNum']);
            $critData.=$row['CriteriaData'];            
        }

        // Convert flat criteria data to array
        if ($critData!=""){
            $critArr=flatCriteriaToCompArr($this->_resultsBlock[0], $this->assessCrits, $this->assessCritPVs, $critData);
        } else {
            return;
        }
        
        
        // Add criteria array to row
        $row['criteriaarray']=$critArr;
        
        // force values to be a string by prefixing with _
        $examId='_'.$row['ExamId'];
        $moduleId='_'.$row['ModuleId'];
        $setId='_'.$row['SetId'];
        $groupId='_'.$row['GroupId'];
        
        // Add row to resultsArray
        $this->resultsArray[$examId][$moduleId][$setId][$groupId]=$row;
  
        $this->_resultsBlock=array(); // empty block     
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
    // Purpose: Gets all student results for a specific Exam
    //
    function getExamResults($reqExamId, $outputArray=array()){
        $primarySet=$this->dataSets[0];
        foreach ($this->resultsArray as $examId=>$modArray){
            if ($examId=='_'.$reqExamId){
                foreach($modArray as $moduleId=>$setArray){
                    // Try get result for primarySet - if it doesn't have data then try other data sets
                    $resRow=array();
                    if (isset($this->resultsArray[$examId][$moduleId][$primarySet][0])){
                        $resRow=$this->resultsArray[$examId][$moduleId][$primarySet][0];
                    } else if ($this->anyDataSet){
                        foreach ($this->resultsArray[$examId][$moduleId] as $setId=>$row){
                            if (isset($this->resultsArray[$examId][$moduleId][$setId])){
                                foreach ($this->resultsArray[$examId][$moduleId][$setId] as $groupId=>$row){
                                    // * Note - there should only be one row per group in $this->resultsArray
                                    if (isset($this->resultsArray[$examId][$moduleId][$setId][$groupId])){
                                        $resRow=$this->resultsArray[$examId][$moduleId][$setId][$groupId];
                                        // Merge row with other datasets and groups if any criteria is blank
                                        if ($this->anyDataSet){
                                            $this->MergeExamModSetResult($resRow, $this->resultsArray[$examId][$moduleId]);
                                        }                                    
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
                }
                
                // Add results row to output array.
                $outputArray[]=$resRow;         
            }
        }
        return ($outputArray);
    }    
    
    //
    // Purpose: Gets all student results
    //
    function getAllResults(){
        $outputArray=array();
        foreach ($this->resultsArray as $examId=>$modArray){
            $outputArray=$this->getExamResults($examId,$outputArray);
        }
        return ($outputArray);
    }
    
    
    //
    // Purpose: Merge empty exam criteria with other datasets and groups for the same exam
    //
    function MergeExamModSetResult($resRow, $setData){
        $assId=$resRow['AssessId'];        
        $mapVal='';
        $examId='_'.$resRow['ExamId'];
        $moduleId='_'.$resRow['ModuleId'];
        $resGroupId='_'.$resRow['GroupId'];
        $resSetId='_'.$resRow['SetId'];
        
        foreach ($this->assessCrits[$assId] as $crit){
            $mapVal=$crit['MapValue'];
            if (!isset($resRow['criteriaarray'][$mapVal]['val']) || $resRow['criteriaarray'][$mapVal]['val']==''){
                //$this->outputLog($mapVal.' is empty');
                // map val is empty try other sets and groups               
                foreach ($setData as $setId=>$row){
                    foreach ($this->resultsArray[$examId][$moduleId][$setId] as $groupId=>$row){
                        // only check altrow if groupid or setid are different to resrows
                        if ($groupId!=$resGroupId OR $resSetId!=$setId){
                            if (isset($this->resultsArray[$examId][$moduleId][$setId][$groupId])){
                                // alternative row found
                                $altRow=$this->resultsArray[$examId][$moduleId][$setId][$groupId];
                                // if it has a value for mapval then insert it into $resRows empty mapval
                                if (isset($altRow['criteriaarray'][$mapVal]['val']) && $altRow['criteriaarray'][$mapVal]['val']!=''){
                                    $mergeVal=$altRow['criteriaarray'][$mapVal]['val'];
                                    $mergeSetId=$altRow['criteriaarray'][$mapVal]['setId'];
                                    //$this->outputLog('Blending '.$examId.'>'.$moduleId.'>_'.$resRow['SetId'].'>_'.$resRow['GroupId'].' with mapVal '.$mapVal.' ('.$mergeVal.') from '.$examId.'>'.$moduleId.'>_'.$altRow['SetId'].'>_'.$altRow['GroupId']);
                                    $resRow['criteriaarray'][$mapVal]=$altRow['criteriaarray'][$mapVal];
                                }
                            }
                        }
                    }
                }                
            } else {
                //$this->outputLog($mapVal.' is not empty');            
            }
        }
    }
    

    
    //
    // Purpose: Sets empty class properties - e.g. if examIds not specified, it will set them according to results
    //
    private function _setEmptyClassProperties(){
        // If raw results doesn't have any records then exit
        if (!$this->_rawResults || count($this->_rawResults)==0){
            return false;
        }
        
        // If examIds not already set, extract them from rawResults
        $this->_setClassPropFromRawResults($this->examIds, 'ExamId');
        
        // abort if there are no exam ids - no exam ids means no results!
        if (empty($this->examIds)){
            return false;
        }
        
        // If assessIds not already set, extract them from rawResults       
        $this->_setClassPropFromRawResults($this->assessIds, 'AssessId');     
        
        // If moduleIds not already set, extract them from rawResults       
        $this->_setClassPropFromRawResults($this->moduleIds, 'ModuleId');
        
        // If groupIds not already set, extract them from rawResults
        $this->_setClassPropFromRawResults($this->groupIds, 'GroupId');
        
        // Set assessment criteria if not already set
        foreach ($this->assessIds as $assId){
            // remove underscore prefix from assessment id
            $assId=substr($assId,0,1)=='_' ? substr($assId,1,strlen($assId)-1) : $assId;            
            if (!isset($this->assessCrits[$assId])){
                $this->assessCrits[$assId]=CriteriaForAssessment($assId);
            } else {
                $this->outputLog ("Skipping creation of $assId criteria");
            }
        }
        
        
        // Set assessment criteria point values if not already set
        foreach ($this->assessIds as $assId){
            // remove underscore prefix from assessment id
            $assId=substr($assId,0,1)=='_' ? substr($assId,1,strlen($assId)-1) : $assId;        
            if (!isset($this->assessCritPVs[$assId])){
                $this->assessCritPVs[$assId]=AssessmentCriteriaPoints($assId);
            } else {
                $this->outputLog ("Skipping creation of $assId criteria points");            
            }
        }        
        
    }
    
    function outputLog($logentry){
        echo ('<p>'.$logentry.'</p>');
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
                $resVal='_'.$row[$fieldname]; // force result value to be a string by prefixing with _            
                if (!in_array($resVal, $property)){                    
                    $property[]=$resVal;
                }
            }
        }    
    }
    
    //
    // Purpose: Generate raw results data
    //
    private function _generateRawResults(){
    
        global $fdata;
        
        $sql='SELECT  ns.*, nstu.surname, nstu.forename, nstu.forename2, nstu.calledname, nstu.dateofbirth, nstu.stusex, stu.classgroupid, tg.groupcode FROM (('.$fdata->prefix.'nsturesults AS ns LEFT JOIN '.$fdata->prefix.'students AS stu ON (stu.StudentId=ns.StudentId AND stu.SetId=ns.SetId)) LEFT JOIN '.$fdata->prefix.'teachinggroups AS tg ON (ns.groupid=tg.groupid AND ns.setId=tg.setId)) LEFT JOIN '.$fdata->prefix.'nstupersonal AS nstu ON (nstu.StudentId=ns.StudentId AND nstu.SetId=ns.SetId) WHERE ns.studentid=\''.$this->studentId.'\'';
       
        $tabs=array('ns');
        
        // refine to only include specific datasets
        $sql.=$this->_refineWHERE('setid', $this->dataSets, $tabs);

        // refine sql if specific exams requested
        if (!empty($this->examIds)){        
            $sql.=$this->_refineWHERE('examid', $this->examIds, $tabs);
        }

        // refine sql if specific course years requested
        if (!empty($this->crsYrs)){
            $sql.=$this->_refineWHERE('crsyear', $this->crsYrs, $tabs);
        }
        
        // refine sql if specific modules requested
        if (!empty($this->moduleIds)){
            $sql.=$this->_refineWHERE('moduleId', $this->moduleIds, $tabs);
        }
        
        // refine sql if specific groups requested
        if (!empty($this->groupIds)){
            $sql.=$this->_refineWHERE('groupId', $this->groupIds, $tabs);
        }
        
        // add ordering to sql
        $sql.=' ORDER BY ns.ExamId, ns.ModuleId, ns.SetId, ns.GroupId, ns.LineNum';
        
        $result=$fdata->db->execute($sql);
        
        // convert raw results to an array of results
        $newResArr=array();
        while ($row=$result->fetchrow()){
            // make row hashes lower case first
            $row=array_change_key_case($row, CASE_LOWER);
            $newResArr[]=$row;
        }                
        $this->_rawResults=$newResArr;
        
        $this->_rawResultsSQL=$sql;
   
    }
    
    //
    // Purpose: Create sql to refine WHERE filteria
    //
    private function _refineWHERE($fldName, &$filtArr, $tabs=array()){
                       
            // Code for multiple filters
            $sql=count($filtArr)>1 ? ' AND (' : ' AND ';        
            $pass=0;
            foreach ($filtArr as $filt){
                if ($filt!=''){
                    // remove underscore prefix from filter value
                    $filt=substr($filt,0,1)=='_' ? substr($filt,1,strlen($filt)-1) : $filt;
                    $sql.=$pass>0 ? ' OR ' : '';               
                    if (empty($tabs)){        
                        $sql.=$fldName.'=\''.$filt.'\'';                
                    } else {
                        $sql.='(';
                        $tabpass=0;
                        foreach ($tabs as $tab){                                    
                            $sql.=$tabpass>0 ? ' AND ' : '';
                            $sql.=$tab.'.'.$fldName.'=\''.$filt.'\'';                                                
                            $tabpass++;
                        }
                        $sql.=')';
                    }
                    $pass++;
                }
            }
            $sql.=count($filtArr)>1 ? ')' : '';
            return ($sql);
    }
    
}