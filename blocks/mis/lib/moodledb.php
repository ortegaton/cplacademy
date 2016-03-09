<?php
global $DB;
/**
* Not to be instantiated - simply for name spacing functions
*/
class db_mis {
	
    function db_mis(){
        return false;
    }
    static function get_set($setid){
		global $DB;
        return ($DB->get_record('block_mis_assessment_sets',array('setid'=>$setid)));
    }
    static function get_exam($setid, $examid){
		global $DB;
        return ($DB->get_record('block_mis_assessment_exa',array('setid'=>$setid,'examid'=>$examid)));
    }    

    static function update_set($set){ 
		global $DB;       
        $setid=addslashes($set->id); // character based set id
        $display=$set->checked ? 1 : 0;
        $row=$DB->get_record('block_mis_assessment_sets',array('setid'=>$setid));
        $do=new stdClass();
        if ($row){
            $do->id=$row->id;
        }
        $do->setid=$setid;
        $do->display=$display;
        if ($row){
            $DB->update_record('block_mis_assessment_sets', $do);
        } else {
            $DB->insert_record('block_mis_assessment_sets', $do);
        }
    }
    static function update_assessment($set, $assessment){        
        for ($a=0; $a<count($assessment); $a++){
            db_mis::update_assessment_criteria($set, $assessment);
            db_mis::update_assessment_exams($set, $assessment);
        }
    }
    
    static function get_assessment_criteria($setid, $assessid, $label){
		global $DB;
        // Note: Get criteria by label intead of map val.
        return ($DB->get_record('block_mis_assessment_crit',array('setid'=>$setid,'assessid'=>$assessid,'label'=>$label)));
    }
    
    static function get_exam_year($setid, $examid, $year){
		global $DB;
        return ($DB->get_record('block_mis_assessment_exyrs',array('setid'=>$setid,'examid'=>$examid,'year'=>$year)));
    }
    
    static function update_assessment_criteria($set, $assessment){  
		global $DB;     
        $setid=addslashes($set->id);
        $assessid=addslashes($assessment->assessid);
        $criteria=$assessment->criteria;
        foreach ($criteria as $crit){
            $mapval=addslashes($crit->mapval);
            $label=addslashes($crit->label);
            $labelcustom=addslashes($crit->labelcustom);
            $display=$crit->display ? 1 : 0;
            // get assessment criteria record from moodle database
            $row=db_mis::get_assessment_criteria($setid, $assessid, $label);
            $do=new stdClass();
            if ($row){
                $do->id=$row->id;
            }
            $do->setid=$setid;
            $do->assessid=$assessid;
            $do->mapval=$mapval;
            $do->label=$label;
            if ($labelcustom!=$label){
                // only add custom label to row if its different to the criteria label
                $do->labelcustom=$labelcustom;
            }
            $do->display=$display;
            if ($row){
                $DB->update_record('block_mis_assessment_crit', $do);
            } else {
                $DB->insert_record('block_mis_assessment_crit', $do);
            }            
        }
    }
    
    
 
    
    static function update_assessment_exams($set, $assessment){
		global $DB;
        $setid=addslashes($set->id);    
        $assessid=addslashes($assessment->assessid);
        $exams=$assessment->exams;
        foreach ($exams as $exam){
            $examid=addslashes($exam->examid);
            $name=addslashes($exam->name);
            $namecustom=addslashes($exam->namecustom);
            $row=$DB->get_record('block_mis_assessment_exa',array('setid'=>$setid,'assessid'=>$assessid,'examid'=>$examid));
            $do=new stdClass();            
            if ($row){
                $do->id=$row->id;
            }           
            $do->setid=$setid;
            $do->assessid=$assessid;
            $do->examid=$examid;
            $do->name=$name;
            if ($namecustom!=$name){
                // only add custom name to row if its different to the exam name
                $do->namecustom=$namecustom;
            }
            if ($row){
                $DB->update_record('block_mis_assessment_exa', $do);
            } else {
                $DB->insert_record('block_mis_assessment_exa', $do);
            }

            // update exam years
            db_mis::update_assessment_exam_years($set, $assessment, $exam);
            
        }
    }
    
    static function update_assessment_exam_years($set, $assessment, $exam){
		global $DB;
        $setid=addslashes($set->id);    
        $assessid=addslashes($assessment->assessid);
        $examid=addslashes($exam->examid);
        $examyears=$exam->examyearconf;        
        foreach ($examyears as $examyear){
            $displayfrom=0;
            $displayto=0;
            $year=intval($examyear->year);
            $display=$examyear->display ? 1 : 0;
            $fromarr=explode('-',addslashes($examyear->displayfrom));
            $toarr=explode('-',addslashes($examyear->displayto));
            if (count($fromarr)==3){                
                $displayfrom=mktime(0,0,0,intval($fromarr[1]),intval($fromarr[0]),intval($fromarr[2]));                
            }
            if (count($toarr)==3){
                $displayto=mktime(23,59,59,intval($toarr[1]),intval($toarr[0]),intval($toarr[2]));
            }
            $row=$DB->get_record_select('block_mis_assessment_exyrs',array('setid'=>$setid.' AND','assessid'=>$assessid.' AND','examid'=>$examid.' AND','year'=>$year));
            $do=new stdClass();            
            if ($row){
                $do->id=$row->id;
            }
            $do->setid=$setid;
            $do->assessid=$assessid;
            $do->examid=$examid;            
            $do->year=$year;
            $do->display=$display;
            $do->displayfrom=$displayfrom;
            $do->displayto=$displayto;
            if ($row){
                $DB->update_record('block_mis_assessment_exyrs', $do);
            } else {
                $DB->insert_record('block_mis_assessment_exyrs', $do);
            }               
            
        }
    }
    
    static function get_student_users($lf='', $ln='', $filter=''){
        global $CFG, $DB;

        $sql='SELECT {user}.id AS id, {user}.firstname AS firstname, {user}.lastname AS lastname, CONCAT({user}.firstname, {user}.lastname) AS fullname, {user}.idnumber AS idnumber, {user}.username as username FROM {user} LEFT JOIN {role_assignments} ON {user}.id={role_assignments}.userid LEFT JOIN {role} ON {role_assignments}.roleid={role}.id WHERE {role_assignments}.contextid=1 AND {user}.deleted=0';
        $sql.=' AND {role}.shortname=\'site_student\'';
        if($filter!=''){$sql.=$filter;}
		//$sql.= 'ORDER BY lastname ASC';
		$calc=$lf*$ln;
        return ($DB->get_recordset_sql($sql,array(),$calc,$ln));
    }
}
?>