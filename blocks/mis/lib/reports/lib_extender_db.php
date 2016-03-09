<?php

require_once(dirname(__FILE__).'../../../../../config.php');
require_once(dirname(__FILE__).'../../../../../lib/adodb/adodb.inc.php');

// Simply return the moodle database
function connectExtenderDB($CFG, $persistent=true){
    global $DB;
    return ($DB);
}
?>