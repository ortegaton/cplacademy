<?php

function manage_assessments(){
    global $fdata;
    
    $datasets=$fdata->all_data_sets();

    $output='<ul id="manageassessments">';
    foreach ($datasets as $setid){
        // is this setid checked ?
        $misset=db_mis::get_set($setid);        
        $checked=isset($misset->display) && $misset->display==1 ? ' checked="checked"' : '';        
        $output.='<li id="sid~'.$setid.'" class="setid"><a class="setid collapsed" href="#" id="sid~'.$setid.'~click">'.$setid.'</a><label>display</label> <input class="setid_checkbox" type="checkbox" name="sid~'.$setid.'~checkbox" id="sid~'.$setid.'~checkbox"'.$checked.' /></li>';    
    }
    $output.='</ul>';
    $output.='<a id="saveassessmentconfig" class="button" href="#" onclick="return(false)">Update Assessment Settings</a>';
    return ($output);
}

function update_assessments($json){
    global $CFG, $USER;
    if ($json!=''){
        $assobj=json_decode($json);
        foreach ($assobj as $set){            
            // update set id config
            db_mis::update_set($set);
            
            // update criteria and exams for all assessments
            $assessconf=$set->assessconf;
            foreach ($assessconf as $assessment){
                db_mis::update_assessment($set, $assessment);
            }
        }
    }
    $message='Your assessment configuration has been updated';
    notice ($message, $CFG->wwwroot.'/blocks/mis/manageassessments.php?sesskey='.$USER->sesskey);
}

?>