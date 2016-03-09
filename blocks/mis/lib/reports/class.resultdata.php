<?php

//
// Author: Guy Thomas
// Date:2007 - 02 - 20
// Purpose: Returns list of students with results if exam codes and assessIds specified
//

require_once('class.propsbyparam.php');
require_once('class.columnAliasMap.php');

class resultdata extends propsbyparam{

    var $examCodes=array();
    var $assessIds=array();
    var $aliasMap; // alias map object
    var $cgIds=array(); // class group filter
    var $tgIds=array(); // year group filter
    var $examCodesAssIds; // exam code keys with assessment ids as values    
    var $dataSet;
    var $distinct=true; // SQL distinct - true or false
    var $cfg;
    var $fdata;
    var $_columns=array(); // mandatory param (this should be protected but wont work as protected with sub class resultreport for some reason)

    //
    // Class Constructor
    //
    function resultdata($columns, $params){

        global $CFG;

        parent::propsbyparam($params);

        $this->_columns=$columns;
        $this->lcaseFieldExps();
        $this->aliasMap=new columnAliasMap();

        // If config hasn't been set then try global variable
        if(!isset($this->cfg)){
            $this->cfg=$CFG->mis;
        }
        // If msdb hasn't been set then try global variable
        if(!isset($this->fdata)){
            $this->fdata=$GLOBALS['fdata'];
        }

        $fdata=$this->fdata;

        // set defaults for non-set class properties
        if (!isset($this->dataSet)){
            // default data set to config data set (if not passed in parameters array)
            $this->dataSet=$this->cfg->cmisDataSet;
        }

        // Set exam codes and exam code keys with assessment ids (exit constructor if any probs)
        if (!$this->GenExamCodesAssIds()){            
            return (false);
        }

        // Convert teaching group codes to ids (will only do this if member of $tgIds is not integer - i.e. teaching group code as string)
        $this->convertTeachGroupCodes();

        // do query
        $this->RunQuery();
    }

    //
    // Convert field expressions to lower case
    //
    function lcaseFieldExps(){
        foreach ($this->_columns as &$col){
            $col['field']=strtolower($col['field']);
        }
    }

    //
    // Generates Exam Codes and Exam Codes keys with assessment ids inside
    // Returns false if any problems
    //
    function GenExamCodesAssIds(){
        $fdata=$this->fdata;

        $examCodes=$this->examCodes;
        $assessIds=$this->assessIds;

        // Make sure either examCodes or assessIds passed in (can be one or the other)
        if (empty($examCodes) && empty ($assessIds)){
            return (true); // no exam codes or assessment ids passed in (thats ok - wont be able to produce results though)
        }

        // If both exam codes and assessids passed in then convert to one array (both must have equal elements)
        if (!empty($examCodes) && !empty($assessIds)){
            if (count($examCodes)>count($assessIds)){
                // splice exam codes
                array_splice($examCodes, (count($assessIds)+1));
            } else if (count($examCodes)<count($assessIds)){
                // splice assessment ids
                array_splice($assessIds, (count($examCodes)+1));
            }
            $this->examCodesAssIds=array_combine($examCodes, $assessIds);            
        } else if (empty($examCodes) && !empty($assessIds)){
            // Get exam codes for assessment ids
            foreach ($assessIds as $assId){
                $facQry='SELECT examid FROM '.$fdata->prefix.'examinations WHERE assessid=\''.$assId.'\'';
                $result=$fdata->db->execute($facQry);
                if ($result){
                    while ($row=$result->fetchrow()){
                        $examCode=$row['examid'];
                        $this->examCodes[]=$examCode;
                        $this->examCodesAssIds[$examCode]=$assId;
                    }
                }
            }
        } else if (!empty($examCodes) && empty($assessIds)){
            // Get assessment ids for exam codes
            $this->examCodes=$examCodes;
            foreach ($examCodes as $examCode){
                $facQry='SELECT assessid FROM '.$fdata->prefix.'examinations WHERE examid=\''.$examCode.'\'';
                $result=$fdata->db->execute($facQry);
                if ($result){
                    while ($row=$result->fetchrow()){
                        $assId=$row['assessid'];
                        $this->examCodesAssIds[$examCode]=$assId;
                    }
                }
            }
        }

        return (true);
    }

    //
    // Generate SQL
    //
    function GenSQL (){
        $fdata=$this->fdata;

        $distinct=$this->distinct ? 'DISTINCT ' : '';

        // Set student sql for retrieving student data (* note st.name always hardcoded for ordering)
        $studentSQL="";
        foreach ($this->_columns as $col){
            $fldExp=$col['field'];
            // do not add exam fields to student select SQL
            if ($this->aliasMap->isExamField($fldExp)==false){
                $fldSQL=$this->aliasMap->getFieldSQL($fldExp);
                $fldRef=$this->aliasMap->getFieldTableConcat($fldExp);
                $studentSQL.=$studentSQL!="" ? ", " : "";
                $studentSQL.=$fldSQL." as ".$fldRef;
            }
        }

        if (!empty($this->examCodesAssIds)){    
            // TO DO - think about not using st.* and pers.*
            $facQry='SELECT '.$distinct.' st.name,'.$studentSQL.', tg.groupcode, md.name as subject, nr.examid, nr.criteriadata,  nr.linenum, nr.groupid FROM ((('.$fdata->prefix.'nsturesults as nr LEFT JOIN '.$fdata->prefix.'teachinggroups AS tg on nr.GroupId=tg.GroupId) LEFT JOIN '.$fdata->prefix.'module as md on nr.ModuleId=md.ModuleId) LEFT JOIN '.$fdata->prefix.'students AS st on nr.studentid=st.studentid) LEFT JOIN '.$fdata->prefix.'nstupersonal AS pers on st.studentid=pers.studentid WHERE';

            $sqlFilters=$this->GenSQLFilters();
            if ($sqlFilters!=''){
                $facQry.=' '.$sqlFilters.' AND ';
            }

            $facQry.=' st.setid=\''.$this->dataSet.'\' AND tg.setid=\''.$this->dataSet.'\' AND md.setid=\''.$this->dataSet.'\' AND pers.setid=\''.$this->dataSet.'\' AND nr.setid=\''.$this->dataSet.'\' AND (';

            $examQry='';
            foreach($this->examCodes as $examCode){
                $examQry.=$examQry!='' ? ' OR ' : '';
                $examQry.='nr.examid=\''.$examCode.'\'';
            }

            $facQry.=$examQry.')';
            $facQry.=' ORDER BY st.name, nr.ExamId, md.name,  nr.GroupId, nr.LineNum';

        } else if (empty($this->tgIds)){
            $facQry='SELECT '.$distinct.' st.name,'.$studentSQL.' FROM '.$fdata->prefix.'students LEFT JOIN '.$fdata->prefix.'nstupersonal AS pers on st.studentid=pers.studentid WHERE';

            $sqlFilters=$this->GenSQLFilters();
            if ($sqlFilters!=''){
                $facQry.=' '.$sqlFilters.' AND ';
            }

            $facQry.=' st.setid=\''.$this->dataSet.'\' AND pers.setid=\''.$this->dataSet.'\' ORDER BY st.name';
        } else {
            $facQry='SELECT '.$distinct.' st.name,'.$studentSQL.' FROM (('.$fdata->prefix.'stugroups AS tg LEFT JOIN '.$fdata->prefix.'students as st on tg.studentId=st.studentId) LEFT JOIN '.$fdata->prefix.'nstupersonal AS pers on st.studentid=pers.studentid) WHERE';

            $sqlFilters=$this->GenSQLFilters();
            if ($sqlFilters!=''){
                $facQry.=' '.$sqlFilters.' AND ';
            }

            $facQry.=' tg.setid=\''.$this->dataSet.'\' AND st.setid=\''.$this->dataSet.'\' AND pers.setid=\''.$this->dataSet.'\' ORDER BY st.name';
        }
        return ($facQry);
    }

    //
    // Generate filter sql
    //
    function GenSQLFilters(){

        // generate sql filter for class group ids
        $cgFilter='';
        if (!empty($this->cgIds)){
            $cgFilter.='(';
            foreach ($this->cgIds as $cgId){
                $cgFilter.=$cgFilter!='(' ? ' OR ' : '';
                $cgFilter.='st.ClassGroupId=\''.$cgId.'\'';
            }
            $cgFilter.=')';
        }

        // generate sql filter for teaching group ids
        $tgFilter='';
        if (!empty($this->tgIds)){
            $tgFilter.='(';
            foreach ($this->tgIds as $tgId){
                $tgFilter.=$tgFilter!='(' ? ' OR ' : '';
                $tgFilter.='tg.groupId='.$tgId;
            }
            $tgFilter.=')';
        }

        $filter=$cgFilter;
        $filter.=$filter!='' && $tgFilter!='' ? ' AND ' : '';
        $filter.=$tgFilter;
        $filter='('.$filter.')';

        return ($filter);
    }

    //
    // Convert teaching group codes to ids
    //
    function convertTeachGroupCodes(){
        if (empty($this->tgIds)){return;}

        $newTgIds=array();

        foreach ($this->tgIds as $tgId){
            if (!is_int($tgId)){
                // if code past in, get ids
                $tgIdsForCode=$this->teachingGroupIds($tgId);
                foreach ($tgIdsForCode as $t){
                    $newTgIds[]=$t;
                }
            } else {
                $newTgIds[]=$tgId;
            }
        }

        $this->tgIds=$newTgIds;

    }

    //
    // Get Teaching Group Ids for teaching group code
    //
    function teachingGroupIds($tgCode){

        $fdata=$this->fdata;

        $sql='SELECT groupid FROM '.$fdata->prefix.'teachinggroups WHERE setid=\''.$this->dataSet.'\' AND groupcode=\''.$tgCode.'\'';

        $tgIds=array();

        $result=$fdata->db->execute($sql);
        while ($row=$result->fetchrow()){
            $tgIds[]=$row['groupid'];
        }

        return ($tgIds);
    }

    //
    // Run Query (returns result)
    //
    function RunQuery(){
        $fdata=$this->fdata;
        $sql=$this->GenSQL();
        $result=$fdata->db->execute($sql);
        return ($result);
    }

}
?>