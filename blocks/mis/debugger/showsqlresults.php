<?php
	require_once('../../../config.php');
    require_once($CFG->dirroot.'/blocks/mis/cfg/config.php');
    require_once($CFG->dirroot.'/blocks/mis/lib/lib_facility_db.php');
    require_once($CFG->dirroot.'/blocks/mis/lib/datatablelib.php');
    require_once($CFG->dirroot.'/lib/weblib.php');    
    require_once($CFG->dirroot.'/lib/moodlelib.php');    
    
    
    global $USER, $CFG, $fdata;
    $fdata=new facilityData(false);
    
    $navigation='SQL debugger';
    print_header_simple('SQL debugger', 'SQL debugger', $navigation);
    
    require_login();
    
    if (!isadmin()){
        print_error('error', 'onlyadmins'); 
        exit;
    }
    
    $id=optional_param('id', false, PARAM_TEXT);
    $sql=optional_param('sql', false, PARAM_TEXT);
    if (!$id && !$sql){  
        exit;
    }
    
    if (!$sql){
        $sql=$USER->mis->sqldebug[$id];
    } else {
        $sql=stripslashes($sql);
    }
    
    echo('<div style="text-align:center">
    
    <form name="sql_form" id="sql_form">
        <fieldset>
            <legend>sql</legend>
            <textarea style="width:100%; min-height:120px" name="sql" id="sql">'.$sql.'</textarea>
        </fieldset>
        <br />
        <input type="submit" name="submit" id="submit" value="refresh" />
    </form>
    
    </div>');
    
    // safe commands only    
    $sqldangers=$fdata->sqldangers();
      
    foreach ($sqldangers as $danger){
        if (stripos($sql, $danger)!==false){            
            print_error('sqldanger', 'block_mis', '', strtoupper($danger));
            die;
        }
    }
    
 
    
    $rs=false;
    @$rs=$fdata->doQuery($sql);
    

    
    if ($rs){
        $pludesc=count($rs)==1 ? 'record' : 'records';
        echo ('<p>SQL returned '.count($rs).' '.$pludesc.' in '.$fdata->lqryexectime.' second(s)</p>');    
        $dt=new datatable($rs);
        $dt->display();
    } else {
        echo ('<p>SQL failed!</p>');
    }
    
    
?>