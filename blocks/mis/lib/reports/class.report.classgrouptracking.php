<?php
//
// Author: Guy Thomas
// Date:2008 - 01 - 17
// Purpose: Gets tracking data for an entire class group
//

// Set memory limit to 256MBytes - this is essential or script will fail.
ini_set('memory_limit', '256M');

// Set time out to 20 minutes
set_time_limit(1200);


// class.report.super.php provides ../cfg , lib_results.php, class.propsbyparam.php
require_once("class.report.super.php");
require_once("class.results.classgroup.php");
require_once("lib_facility_db.php");


//
// Class report_classgrouptracking
//
class report_classgrouptracking extends superreport{

    //
    // Protected properties
    //
    protected $_reportName='report_cgtracking'; // name of report for caching purposes
    protected $_cgId; // class group id
    protected $_modArray; // array of module codes (subjects)
    protected $_crsYear;
    protected $_ks3criteria;
    protected $_ks3critPVs;
    protected $_ks4criteria;
    protected $_ks4critPVs;
    
    //
    // Public properties
    //
    var $anyDataSet=true; // this should be true otherwise year 11 stuff wont work
    var $columns;
    var $rowcatColumnTitle='Id';
    var $headerRowAtts=array();

    //
    // Purpose: Constructor
    //
    function report_classgrouptracking($params=array(), $cgId){
        $this->_cgId=$cgId;        
        parent::superreport($params, $cgId);
        $this->setYearGroup();
        $this->setDefaultColumns_nonsuper();
        $this->setAssessmentCriteria();
        
        // set table header row attributes
        $this->headerRowAtts=array('style'=>'min-height:40px; height:40px');
        
    }
    
    //
    // Purpose: Set year group for class group
    //
    protected function setYearGroup(){
        global $CFG, $fdata;
       // Get course year for class group
        $sql='SELECT crsyear FROM '.$fdata->prefix.'classgroup WHERE ClassGroupId=\''.$this->_cgId.'\' AND SetId=\''.$this->dataSets[0].'\'';
        
        $row=$fdata->getRowValues($sql);
        $this->_crsYear=$row['crsyear'];
    }    
    
    //
    // Purpose: Set modules for current dataset
    //
    protected function setModuleArray($results){

        $modArray=array();
    
        // add core subjects to module array first
		$modArray[]="EA"; // English
		$modArray[]="MA"; // Maths
		$modArray[]="SI"; // Science
       
        foreach ($results as $stu=>$modrow){
            foreach ($modrow as $modkey=>$row){
                $mod=str_replace('_','',$modkey);
                if (!in_array($mod,$modArray)){
                    $modArray[]=$mod;
                }
            }
        }
        
        $this->_modArray=$modArray;
                        
    }
    
    //
    // Purpose: Adds modules to columns array
    //
    protected function addModulesToColumns(){
        foreach ($this->_modArray as $mod){            
            $this->columns[$mod.'targ']=array(
                'code'=>$mod.'targ',
                'title'=>strtoupper($mod).' Targ',
                'cellatts'=>array(
                    'style'=>'width:40px; background-color:#0000ff'
                ),
                'colatts'=>array(
                    'style'=>'text-align:center; border-left:1px solid black; background-color:#eee'
                )
            );
            $this->columns[$mod.'level']=array(
                'code'=>$mod.'level',
                'title'=>strtoupper($mod).' Level',
                'cellatts'=>array(
                    'style'=>'width:40px; background-color:#3366ff'
                ),
                'colatts'=>array(
                    'style'=>'text-align:center; border-right:1px solid black'
                )
            );
        }            
    }
    
    
    //
    // Purpose: Setup columns for this report
    //
    protected function setDefaultColumns(){
        // Does nothing (use SetDefaultColumns_nonsuper instead)
    }
    
    //
    // Purpose: Setup columns for this report -- not called by super class
    //
    protected function setDefaultColumns_nonsuper(){
        $this->columns=array(
            'name'=>array('code'=>'name', 'title'=>'Name', 'cellatts'=>array('style'=>'width:200px'))
        );
        

    }
    
    //
    // Purpose: report engine - gets the data
    // This function is never directly called - it is called by the class superreport::getCacheOrBuild
    //
    protected function build(){   
        // set globals
        global $CFG, $fdata;
        
        // connect to Facility database
        $fdata=new facilityData();
        
        
        $examId='Year'.$this->_crsYear;
        // For non-vertical class groups, set examids to class group year
        if ($this->_crsYear>0){
            $examIds=array($examId);
        } else {
            // for vertical class groups, set examids to Year7,Year8,Year9,Year10,Year11
            $examIds=array('Year7', 'Year8', 'Year9', 'Year10', 'Year11');
        }
        
        // result parameters
        $resParams=array('anyDataSet'=>$this->anyDataSet, 'dataSets'=>$this->dataSets, 'examIds'=>$examIds);
        
        // create class group results
        $cgres=new results_classgroup($resParams, $this->_cgId);
        $results=$this->getAllResultsAndHash($cgres);

        // Create module array based on results
        $this->setModuleArray($results);
        
        // Add modules to columns
        $this->addModulesToColumns();
        
        
        $reportdata=array();
        
        foreach ($cgres->studentIds as $studentId){
            // Get first result row for this student
            reset ($results['_'.$studentId]);
            $tmparr=current ($results['_'.$studentId]);
            $row1=$tmparr[0];
            $stuCourseYear=$row1['CourseYear'];
            $name=$row1['Forename'].' '.$row1['Surname'];
        
            // add student id and forename to report data array                    
            $reportdata=array(
                'adminno'    => array('val'=>$studentId),
                'name'    => array('val'=>$name)
            );
        
            foreach ($this->_modArray as $mod){
                if (isset($results['_'.$studentId]['_'.$mod])){
                    $resRows=$results['_'.$studentId]['_'.$mod];
                    //$row1=$resRows[0];
                    //$stuCourseYear=$row1['CourseYear'];
                    // Get exam data for this student
                    foreach ($resRows as $row){
                        // make sure exam corresponds to student course year
                        if ($row['ExamId']=='Year'.$stuCourseYear){
                            break;
                        }
                    }

                    $ks=$stuCourseYear<10 ? 3 : 4;
                    $levelArr=$this->getLevel($row['criteriaarraybylabel']);
                    if (isset($row['criteriaarraybylabel']['Target'])){ 
                        $targArr=$row['criteriaarraybylabel']['Target'];
                    } else {
                        $targArr=array('val'=>'', 'pts'=>0);
                    }
                    
                    // add level and target to this reportdata row
                    $reportdata[$mod.'level']=array('val'=>$levelArr['val']);
                    $reportdata[$mod.'targ']=array('val'=>$targArr['val']);

                    // color level if less or greater than target
                    if ($levelArr['pts']!=$targArr['pts'] && $levelArr['pts']!=null && $targArr['pts']!=null){
                        $bgCol=$levelArr['pts']>$targArr['pts'] ? '#B5FF8D' : '#FF9595';
                        $reportdata[$mod.'level']['cellatts']=array('style'=>'background-color:'.$bgCol);
                    }

                }
            }
            
            // add report data row to output array (hashed by studentid
            $this->_outputArray[$studentId]=$reportdata;
            
        }
    }
    
    //
    // Purpose: Gets level for a specific criteria array
    // In:
    // $critArr - criteria array (hashed by label) to get results
    // $levelType - if not passed it will get the most recent level for the student
    //
    protected function getLevel($critArr, $levelType='latest'){
        
        $curLevel = (object) NULL;
    
        if ($levelType=='latest'){
            // return most recent assessment level ($levelType)
            $asslabs=array ('Sum', 'Spr', 'Aut'); // assessment labels for levels
            foreach ($asslabs as $lab){
                if (isset($critArr[$lab])){
                    $curLevel=$critArr[$lab];
                    return ($curLevel);
                }
            }
            
        } else {
            // return specific assessment level ($levelType)
            if (isset($critArr[$levelType])){
                $curLevel=$critArr[$levelType];          
                return ($curLevel);
            }          
        }
        
        // could not get level
        return (null);
        
    }
    
    //
    // Purpose: Sets assessment criteria + points val arrays
    //
    protected function setAssessmentCriteria(){
        // Get ks3 criteria
        $this->_ks3criteria=CriteriaForAssessment('KS3Assess');
        $this->_ks3critPVs=AssessmentCriteriaPoints('KS3Assess');
                    
        // Get ks4 criteria
        $this->_ks4criteria=CriteriaForAssessment('KS4Assess');
        $this->_ks4critPVs=AssessmentCriteriaPoints('KS4Assess');
    }
    
    
    //
    // Purpose: Gets all results and hashes by student and then module and then exam
    //
    protected function getAllResultsAndHash($resultObj){
        $outputArray=array();
        foreach ($resultObj->examIds as $examId){
            $outputArray=$resultObj->getExamResults($examId,$outputArray, array('stuId', 'moduleId'));        
        }
        return ($outputArray);
    }
    
}