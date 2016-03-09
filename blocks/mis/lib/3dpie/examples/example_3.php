<?php
        
    include_once('class.chart.3dpie.php');
    
    // example with simple data and colour array overriden
    
    $newcolors=array('FF0000', '00FF00', '0000FF','FFFF00');    
    $pie=new chart_3dpie(array(25,32,77,5), array('car', 'train', 'bus', 'walk'), array('colors'=>$newcolors));
    
?>