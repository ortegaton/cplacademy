<?php
    require_once(dirname(__FILE__).'/../../../../config.php');    
    require_once($CFG->dirroot.'/blocks/mis/lib/ssllib.php');

    $reps=array(
        array('search'=>$CFG->wwwroot.'/course/user.php', 'replace'=>$CFG->wwwroot.'/blocks/mis/tabs/profile/sslprofile.php')
    );
    
    render_webpage_file_ssl($CFG->dirroot.'/course/user.php', $reps);

   
    
?>