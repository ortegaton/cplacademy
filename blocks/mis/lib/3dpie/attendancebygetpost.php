<?php
        
    include_once('class.chart.3dpie.php');
    
    // data must be passed into this via get / post
    
    $data=explode('~', $_GET['data']);
    $label=explode('~', $_GET['label']);
        
    $pie=new chart_3dpie($data, $label, array('legendunderchart'=>true, 'colors'=>array('00FF00','FF0000','0000FF','CEEF00')));
    
?>