<?php
//
// Author: Guy Thomas
// Date:2008 - 01 - 17
// Purpose: Gets tracking data for an entire teaching group
//

// Set memory limit to 256MBytes - this is essential or script will fail.
ini_set('memory_limit', '256M');

// Set time out to 20 minutes
set_time_limit(1200);


// class.report.super.php provides ../cfg , lib_results.php, class.propsbyparam.php
require_once("class.report.super.php");
require_once("class.results.teachinggroup.php");
require_once("lib_facility_db.php");


//
// Class report_teachinggrouptracking
//
class report_teachinggrouptracking extends superreport{

    //
    // Protected properties
    //
    protected $_reportName='report_tgtracking'; // name of report for caching purposes
    protected $_tgId; // teaching group id
    protected $_modArray; // array of module codes (subjects)
    protected $_crsYear;
    protected $_ks3criteria;
    protected $_ks3critPVs;
    protected $_ks4criteria;
    protected $_ks4critPVs;
    protected $_tgYear;
    
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
    function report_teachinggrouptracking($params=array(), $tgId){
        $this->_tgId=$tgId;        
        parent::superreport($params, $tgId);                
        $this->setAssessmentCriteria();        
        $this->_tgYear=TeachingGroupYearFromCode($this->_tgId, $this->primarySet);
        $this->setDefaultColumnsThisClass();

       
        // set table header row attributes
        $this->headerRowAtts=array('style'=>'min-height:40px; height:40px');
    }
    

    
    //
    // Purpose: Setup columns for this report(STUB - NOT USED)
    //
    protected function setDefaultColumns(){                 
          
    }
    
    //
    // Purpose: Setup columns for this report - called by this class (not parent class)
    // This is so that it can be called after datasets initialised etc.
    //
    protected function setDefaultColumnsThisClass(){
$tgYearTxt='Y '.$this->_tgYear;        
        $this->columns=array(
            'name'=>array('code'=>'name', 'title'=>'Name', 'cellatts'=>array('style'=>'width:200px')),
            'form'=>array('code'=>'form', 'title'=>'Form', 'cellatts'=>array('style'=>'width:40px')),
            'sex'=>array('code'=>'sex', 'title'=>'Sex', 'cellatts'=>array('style'=>'width:40px')),
            'sen'=>array('code'=>'sen', 'title'=>'SEN', 'cellatts'=>array('style'=>'width:40px')),              
            'lstyle'=>array('code'=>'lstyle', 'title'=>'Learning Style', 'cellatts'=>array('style'=>'width:60px')), 
            'target'=>array('code'=>'target', 'title'=>$tgYearTxt.' Target', 'cellatts'=>array('style'=>'width:40px')),
            'asp'=>array('code'=>'asp', 'title'=>$tgYearTxt.' Aspired Grade', 'cellatts'=>array('style'=>'width:40px')),
            'aut'=>array('code'=>'aut', 'title'=>$tgYearTxt.' Aut Level', 'cellatts'=>array('style'=>'width:40px')),
            'aut_eff'=>array('code'=>'aut_eff', 'title'=>$tgYearTxt.' Aut Effort', 'cellatts'=>array('style'=>'width:40px')),            
            'spr'=>array('code'=>'spr', 'title'=>$tgYearTxt.' Spr Level', 'cellatts'=>array('style'=>'width:40px')),
            'spr_eff'=>array('code'=>'spr_eff', 'title'=>$tgYearTxt.' Spr Effort', 'cellatts'=>array('style'=>'width:40px')),            
            'sum'=>array('code'=>'sum', 'title'=>$tgYearTxt.' Sum Level', 'cellatts'=>array('style'=>'width:40px')),
            'sum_eff'=>array('code'=>'sum_eff', 'title'=>$tgYearTxt.' Sum Effort', 'cellatts'=>array('style'=>'width:40px'))            
        );          
    }

    
    //
    // Purpose: report engine - gets the data
    // This function is never directly called - it is called by the class superreport::getCacheOrBuild
    //
    protected function build(){   
        // set globals
        global $fdata;
        
        // connect to Facility database
        $fdata=new facilityData();
        
        
        $examIds=array('Year'.$this->_tgYear, 'Yr'.$this->_tgYear.'Asp', 'Yr'.$this->_tgYear.'ECOSM');
        
        // result parameters
        $resParams=array('anyDataSet'=>$this->anyDataSet, 'dataSets'=>$this->dataSets, 'examIds'=>$examIds, 'debugOutToScreen'=>false);
        
        // create teaching group results
        $res=new results_teachinggroup($resParams, $this->_tgId);
        $results=$this->getAllResultsAndHash($res);
        
        // get student data array (array of students hashed by id)
        $students=$res->getStudentsById();
        
        $reportdata=array();
        
        foreach ($res->studentIds as $studentId){
            $stuCourseYear=$students[$studentId]['CourseYear'];           
            $student=$students[$studentId];            
            $name=$student['Forename'].' '.$student['Surname'];
        
            $sen=hasSEN($studentId, $this->primarySet) ? 'Y' : '';
        
            // add student details to report data array                    
            $reportdata=array(
                'adminno'   => array('val'=>$studentId),
                'name'      => array('val'=>$name),
                'form'      => array('val'=>$student['ClassGroupId']),
                'sex'       => array('val'=>$student['StuSex']),
                'sen'       => array('val'=>$sen),
                'lstyle'    => array('val'=>$student['Preferredlearningstyle'])
            );
            
            $trackrow=$results['_'.$studentId]['_YEAR'.$this->_tgYear][0];
            
            // Autumn level
            $aut=$this->getLevel($trackrow['criteriaarraybylabel'], 'Aut');
            $aut=$aut!=null ? $aut : array('val'=>'', 'pts'=>0);
            
            // Spring Level
            $spr=$this->getLevel($trackrow['criteriaarraybylabel'], 'Spr');
            $spr=$spr!=null ? $spr : array('val'=>'', 'pts'=>0);
                    
            // Summer Level
            $sum=$this->getLevel($trackrow['criteriaarraybylabel'], 'Sum');
            $sum=$sum!=null ? $sum : array('val'=>'', 'pts'=>0);

            // Target                       
            $targ=$this->GetCriteriaValArray($trackrow['criteriaarraybylabel'],'Target');
        
            // Reset Aspired Grade
            $asp=array('val'=>'', 'pts'=>0);
            // Get aspired grade
            if (isset($results['_'.$studentId]['_YR'.$this->_tgYear.'ASP'])){
                $asprow=$results['_'.$studentId]['_YR'.$this->_tgYear.'ASP'][0];
                $asp=$this->GetCriteriaValArray($asprow['criteriaarraybylabel'],'Aspir');
            }
            
            // Reset ECOSMs
            $aut_eff=array('val'=>'', 'pts'=>0);
            $spr_eff=array('val'=>'', 'pts'=>0);
            $sum_eff=array('val'=>'', 'pts'=>0);

            
            if (isset($results['_'.$studentId]['_YR'.$this->_tgYear.'ECOSM'])){
                $ecosmrow=$results['_'.$studentId]['_YR'.$this->_tgYear.'ECOSM'][0];
                // Autumn Ecosm Effort
                $aut_eff=$this->GetCriteriaValArray($ecosmrow['criteriaarraybylabel'],'Aut Eff');  
                // Spring Ecosm Effort
                $spr_eff=$this->GetCriteriaValArray($ecosmrow['criteriaarraybylabel'],'Spr Eff');              
                // Summer Ecosm Effort
                $sum_eff=$this->GetCriteriaValArray($ecosmrow['criteriaarraybylabel'],'Sum Eff');
            }
            
            $reportdata['target']=$targ;
            $reportdata['asp']=$asp;
            $reportdata['aut']=$aut;
            $reportdata['aut_eff']=$aut_eff;
            $reportdata['spr']=$spr;
            $reportdata['spr_eff']=$spr_eff;
            $reportdata['sum']=$sum;
            $reportdata['sum_eff']=$sum_eff;
            
            // color autum if less or greater than aspired grade
            if ($aut['pts']!=$asp['pts'] && $aut['pts']!=null && $asp['pts']!=null){
                $bgCol=$aut['pts']>$asp['pts'] ? '#B5FF8D' : '#FF9595';
                $reportdata['aut']['cellatts']=array('style'=>'background-color:'.$bgCol);
            }               

            // color spr if less or greater than aspired grade
            if ($spr['pts']!=$asp['pts'] && $spr['pts']!=null && $asp['pts']!=null){
                $bgCol=$spr['pts']>$asp['pts'] ? '#B5FF8D' : '#FF9595';
                $reportdata['spr']['cellatts']=array('style'=>'background-color:'.$bgCol);
            }
            
            // color sum if less or greater than aspired
            if ($sum['pts']!=$asp['pts'] && $sum['pts']!=null && $asp['pts']!=null){
                $bgCol=$sum['pts']>$asp['pts'] ? '#B5FF8D' : '#FF9595';
                $reportdata['sum']['cellatts']=array('style'=>'background-color:'.$bgCol);
            }              

            // add report data row to output array (hashed by studentid
            $this->_outputArray[$studentId]=$reportdata;      

//global $results_assessCritPVs;
//var_dump($results_assessCritPVs);
            
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
    // Purpose: Gets specific criteria component or returns default empty array
    // In:
    // $critArr - Criteria Array By Label
    // $crit - Criteria Label
    //
    function GetCriteriaValArray($critArr, $crit){
        if (isset($critArr[$crit])){
            $valarr=$critArr[$crit];
        } else {
            $valarr=array('val'=>'', 'pts'=>0);
        }
        return ($valarr);
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
            $outputArray=$resultObj->getExamResults($examId,$outputArray, array('stuId', 'examId'));        
        }
        return ($outputArray);
    }
    
}