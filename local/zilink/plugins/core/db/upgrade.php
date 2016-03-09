<?php

    $user = new xmldb_table('zilink_user_data');
    $global = new xmldb_table('zilink_global_data');
    $teacher = new xmldb_table('zilink_cohort_teachers');
    
    if(!$dbman->table_exists($user) && !$dbman->table_exists($global) && !$dbman->table_exists($teacher))
    {
        echo '<div class="notifysuccess">Installing ZiLink Core Plugin ('.$directory.') Database Tables'; 
        $dbman->install_from_xmldb_file($path.'/'.$directory.'/db/install.xml');
    }
    
    

?>