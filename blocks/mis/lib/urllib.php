<?php

class url {
    /**
     * Get url for current page
     */
    static function current() {
        // this code originated at http://dev.kanngard.net/Permalinks/ID_20050507183447.html
    	$s = empty($_SERVER["HTTPS"]) ? ''
    		: ($_SERVER["HTTPS"] == "on") ? "s"
    		: "";
        $pcol=$_SERVER["SERVER_PROTOCOL"];
    	$protocol = strtolower(substr($pcol,0, strpos($pcol,'/'))).$s;
    	$port = ($_SERVER["SERVER_PORT"] == "80") ? ""
    		: (":".$_SERVER["SERVER_PORT"]);
    	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
    }

    /**
     * Get url path for current page
     */
    function currentpath(){
    	$surl=url::current();
    	if (strpos($surl, ".")){
    		$urlpath=dirname($surl); // if its got a dot in file name then remove file and just leave dir
    	} else {
    		$urlpath=$surl;
    	}
    	return ($urlpath);
    }
}


/**
* This function returns the http or https version of the block wwwroot
*/
function get_mis_blockwww(){    
    global $CFG;

    // set url location of this block
    $blockwww=$CFG->wwwroot.'/blocks/mis';
    
    // force block www to https?        
    if (isset($CFG->mis->https) && $CFG->mis->https){
        $blockwww=str_replace('http://', 'https://', $blockwww);
        if (substr($blockwww, 0, 8)!='https://'){
            $blockwww='https://'.$blockwww;
        }
    }
    
    return ($blockwww);
}


/**
* This function simply returns the http or https version of $CFG->wwwroot
* It can be used to force page headers to source https images (otherwise page complains about partial encrypted content)
*/
function get_mis_www($forcehttps=null){    
    global $CFG;
    
    if ($forcehttps===null){
        $forcehttps=isset($CFG->mis->https) && $CFG->mis->https;
    }

    // set url location of this block
    $wwwroot=$CFG->wwwroot;
    
    // force block www to https?        
    if ($forcehttps){
        $wwwroot=str_replace('http://', 'https://', $wwwroot);
        if (substr($wwwroot, 0, 8)!='https://'){
            $wwwroot='https://'.$wwwroot;
        }
    }        
    return ($wwwroot);
}

/**
* Returns the contents of a url
*/
function get_url_contents($url){
    if (function_exists(curl_setopt)){
        // taken from http://www.php.net/manual/en/function.curl-init.php
        $ch = curl_init ($url) ;
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1) ;
        $res = curl_exec ($ch) ;
        curl_close ($ch) ;
        return ($res) ;
    } else {
        // no curl - try to get no https version of url
        return (file_get_contents(str_replace('https://', 'http://', $url)));
    }    
}        

?>