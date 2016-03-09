<?php

require_once('../../../../config.php');
require_once('../../cfg/config.php');
require_once('lib_extender_db.php');

$cacheid=isset($_GET['cacheid']) ? $_GET['cacheid'] : false;
$cacheid=$cacheid ? $cacheid : (isset($_POST['cacheid']) ? $_POST['cacheid'] : false);

$filename=isset ($_GET['filename']) ? $_GET['filename'] : false;
$filename=$filename ? $filename : (isset($_POST['filename']) ? $_POST['filename'] : 'export');

$sheetname=isset ($_GET['sheetname']) ? $_GET['sheetname'] : false;
$sheetname=$sheetname ? $sheetname : (isset($_POST['sheetname']) ? $_POST['sheetname'] : 'export');



$edb=connectExtenderDB($CFG->mis);
global $DB;

//$sql='SELECT * FROM '.$CFG->mis->eeDbTablePrefix.'report_cache WHERE id='.$cacheid;
$row=$DB->get_record('block_mis_report_cache',array('id'=>$cacheid));
$args=str_replace('~sq~', '\'', unserialize($row->args));
$repname=$row->reportname;
$argsstr=args_to_string($args, true);

require_once('class.'.str_replace('_', '.', $repname).'.php');

$evalstr="\$report=new $repname ($argsstr);";
eval ($evalstr);
$report->getCacheOrBuild(true);
echo($report->renderExcel($filename, $sheetname));  

function args_to_string($args){
    $argsstr='';
    foreach ($args as $arg){        
        $argsstr.=$argsstr=='' ? '' : ', ';          
        $ostr=var_export($arg, true);
        $argsstr.=$ostr;
    }
    return ($argsstr);
}


?>