<?php

/**
* This file simply sets standard variables required by the index.php page and each tab page
*/

global $USER;

$mdlstuid = optional_param('userid', '', PARAM_INT);
if ($mdlstuid!=''){
    $USER->mis_mdlstuid=$mdlstuid;
} else if (!isset($USER->mis_mdlstuid)){
    $USER->mis_mdlstuid='';
}

?>