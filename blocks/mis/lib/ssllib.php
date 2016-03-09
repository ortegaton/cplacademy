<?php

require_once('../../../../config.php');
require_once('urllib.php');

/**
* Takes a webpage file - e.g. user.php but forces it to render as https
*/
function render_webpage_file_ssl($file, $reps){

    global $USER, $CFG;

    // stop urls from being loaded - we only want files on the local files system!
    $file=str_replace('://', '~~', $file);

    // get contents of file
    $page=file_get_contents($file);
    
    // convert page to array
    $lines=explode("\n", $page);
    
    $newlines=array();
    
    // Replace code and Insert https lines after config has been included
    foreach($lines as $line){
    
        if ($reps && !empty($reps)){
            foreach ($reps as $rep){
                $line=str_ireplace($rep['search'], $rep['replace'], $line);
            }
        }
       
        $linecheck=str_replace(' ', '', $line);
        $linecheck=str_replace('("/', '', $line);
        $linecheck=str_replace('(\'/', '', $line);
        $linecheck=str_replace('../', '', $linecheck);        

        if (trim($linecheck)!=''){

            $fixconf=false;
        
            if (
                stripos($linecheck, 'require_once("config.php")')!==false ||
                stripos($linecheck, 'include_once("config.php")')!==false ||
                stripos($linecheck, 'require_once(\'config.php\')')!==false ||
                stripos($linecheck, 'include_once(\'config.php\')')!==false
            ) {
                $fixconf=true;
            } else {
                $fixconf=false;
                if (stripos($linecheck, 'require_once')!==false ||
                    stripos($linecheck, 'require')!==false ||
                    stripos($linecheck, 'include_once')!==false ||
                    stripos($linecheck, 'include')!==false
                ) {                
                    $line_fixpath=line_include_insert_path($line, dirname($file));
                    $newlines[]=$line_fixpath;                
                } else {
                    $newlines[]=$line;
                }                  
            }
        
        
            
            if ($fixconf){
                $newlines[]='global $HTTPSPAGEREQUIRED;';
                $newlines[]='$HTTPSPAGEREQUIRED=true;';
                $newlines[]='$CFG->wwwroot=\''.get_mis_www(true).'\';';
            }
            
        }
    }
    

    // remove opening / closing php tags
    if (strpos(trim($newlines[0]),'<?')!==false){
        $newlines[0]='';
    }
    $nle=count($newlines)-1;
    if (trim($newlines[$nle])=='?>'){
        $newlines[$nle]='';
    }
    
    $evalstr=implode ("\n", $newlines);
        
    eval ($evalstr);  
}

function line_include_insert_path($line, $path){

    
    $path.='/';
    
    // work out require / include type
    if (stripos($line, 'require_once')!==false){
        $type='require_once';
    } else if (stripos($line, 'require')!==false){
        $type='require';
    } else if (stripos($line, 'include_once')!==false){
        $type='include_once';
    } else if (stripos($line, 'include')!==false){
        $type='include';
    }
    
    // remove space directly after require / include statement
    $line=str_ireplace($type.' ', '', $line);
    
    // work out quote type
    $qt='';
    if (stripos($line, $type.'(\'')!==false){
        $qt='\'';
    } else if (stripos($line, $type.'("')!==false){
        $qt='"';
    }
    
    // no quotes - return
    if ($qt==''){
        return ($line);
    }
    
    // if start of include / require is forward slash then convert to ../
    if (stripos($line, $type.'('.$qt.'/')!==false){
        $line=str_ireplace($type.'('.$qt.'/', $type.'('.$qt.'../', $line);
    }
    
    $line=str_ireplace($type.'('.$qt, $type.'('.'\''.$path.'\''.'.'.$qt, $line);
    
    return ($line);
    
}

?>