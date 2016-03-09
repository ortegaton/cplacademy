<?php

    ini_set('display_errors', 'stdout');

        
    include_once('../class.chart.3dpie.php');
    
    // example with simple data and colour array overriden and shadow height changed and outputted as a jpeg instead of png
    
    $newcolors=array('BB4400', 'AA00CC', 'FFAA00','00FF00');    
    $data=array(88,123.5,32.4,100);
    $label=array('coats', 'gloves', 'hats', 'socks');
    $params=array('colors'=>$newcolors, 'shadow_height'=>30, 'shadow_dark'=>false, 'imagetype'=>'jpg');
    $pie=new chart_3dpie($data, $label, $params);
    
?>