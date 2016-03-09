<?php
function selfURL() {
    // this code originated at http://dev.kanngard.net/Permalinks/ID_20050507183447.html
	$s = empty($_SERVER["HTTPS"]) ? ''
		: ($_SERVER["HTTPS"] == "on") ? "s"
		: "";
	$protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
	$port = ($_SERVER["SERVER_PORT"] == "80") ? ""
		: (":".$_SERVER["SERVER_PORT"]);
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
}

function selfURLBase(){
    $url=selfURL();
    $protocol=substr($url,0,8)=='https://' ? 'https://' : 'http://';
    $url=str_replace($protocol, '', $url);
    if (strpos($url, '/')){
        $url=substr($url,0, strpos($url, '/'));
    }
    $url=$protocol.$url;
    return ($url);
}

function selfURLPath(){
	$sURL=selfURL();
	if (strpos($sURL, ".")){
		$URLpath=dirname($sURL); // if its got a dot in file name then remove file and just leave dir
	} else {
		$URLpath=$sURL;
	}
	return ($URLpath);
}

function strleft($s1, $s2) {
      // this code originated at http://dev.kanngard.net/Permalinks/ID_20050507183447.html
	return substr($s1, 0, strpos($s1, $s2));
}

function fileURL($file){
	// gets a files url
	$fileRoot=str_replace("\\", "/", $_SERVER['DOCUMENT_ROOT']);
	$file=str_replace("\\", "/", $file);
	return (str_replace($fileRoot, "", $file));
}


?>