<?php


function strToFileName($str){
	/**
	/* Purpose: Converts a string to a filename friendly string (note that str should not include the directory - it should only be the file portion of the path
	**/
	
	$bannedChars=array("*", "<", ">", "[", "]", "=", "+", "\"", "\\", "/", ",",".",":",";");
					
	foreach ($bannedChars as $banned){
		$str=str_replace($banned,"_", $str);
	}
	
	return ($str);
	
}

?>