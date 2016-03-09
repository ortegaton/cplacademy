<?php

/**
 * Upgrade script for moodletxt+
 * 
 * moodletxt+ is distributed as GPLv3 software, and is provided free of charge without warranty. 
 * A full copy of this licence can be found @
 * http://www.gnu.org/licenses/gpl.html
 * In addition to this licence, as described in section 7, we add the following terms:
 *   - Derivative works must preserve original authorship attribution (@author tags and other such notices)
 *   - Derivative works do not have permission to use the trade and service names 
 *     "ConnectTxt", "txttools", "moodletxt", "moodletxt+", "Blackboard", "Blackboard Connect" or "Cy-nap"
 *   - Derivative works must be have their differences from the original material noted,
 *     and must not be misrepresentative of the origin of this material, or of the original service
 * 
 * Anyone using, extending or modifying moodletxt+ indemnifies the original authors against any contractual
 * or legal liability arising from their use of this code.
 * 
 * @package uk.co.moodletxtplus.db
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012102501
 * @since 2012062301
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Runs any database upgrades or other changes that need performing
 * on installation of a new version of moodletxt+
 * @global object $CFG Global Moodle config object
 * @global moodle_database $DB Moodle database manager
 * @param int $oldversion Plugin's old version number
 * @version 2012102501
 * @since 2012062301
 */
function xmldb_message_moodletxtplus_upgrade($oldversion) {
    
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    /**
     * Initial beta build
     * Registers the plugin with Moodle
     */
    if ($oldversion < 2012062301) {
 
        // Register plugin as a message processor
        $processor = new stdClass();
        $processor->name  = 'moodletxtplus';
        if (!$DB->record_exists('message_processors', array('name' => $processor->name))){
            $DB->insert_record('message_processors', $processor);
        }
    
        upgrade_plugin_savepoint(true, 2012062301, 'message', 'moodletxtplus');
        
    }
    
    /**
     * Final 1.0 release. No upgrades to make.
     */
    if ($oldversion < 2012101801) {
        
        upgrade_plugin_savepoint(true, 2012101801, 'message', 'moodletxtplus');
        
    }
        
}

?>