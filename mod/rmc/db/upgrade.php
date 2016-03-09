<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_rmc_upgrade($oldversion) {
	global $CFG, $DB;

    $dbman = $DB->get_manager();
	if($oldversion  < 2014042802) {
		 // Define table rmc_embed_url_token to be created.
        $table = new xmldb_table('rmc_embed_url_token');

        // Adding fields to table rmc_embed_url_token.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('embed_token', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('node_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table rmc_embed_url_token.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for rmc_embed_url_token.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Rmc savepoint reached.
        upgrade_mod_savepoint(true, 2014042802, 'rmc');
	}
    
    return true;
}
