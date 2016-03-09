<?php

//
// Purpose: sorts a table array
// In:
// table - table array
// sortcols - array of columns to sort with col code as key and boolean as val (true ASC and false as DESC)
//
function table_sort (&$table, $sortcols=array(), $firstrowheader=false){

    // Set up array based on $table and whether it has a header on the first row or not
    if ($firstrowheader){
        $usetable=clone ($table);
        array_shift($usetable); // remove first row from array
    } else {
        $usetable=&$table;
    }
    
        
    // create array of sorted columns
    $sortedCols=array();
    foreach ($usetable as $key=>$row){
        foreach ($sortcols as $colcode=>$direction){
            $sortedCols[$colcode][$key]=$row[$colcode];
        }
    }
    
    // create array sort string to be evaluated    
    $evalstr='';
    foreach ($sortcols as $colcode=>$direction){
        $dirstr=$direction ? 'SORT_ASC' : 'SORT_DESC';
        $evalstr.=$evalstr!='' ? ', ' : '';
        $evalstr.='$sortedCols[\''.$colcode.'\'], '.$dirstr; 
    }
    $evalstr='array_multisort('.$evalstr.=', $usetable);';
    
    eval ($evalstr);
    $table=$usetable;
    
    
    
} 

?>