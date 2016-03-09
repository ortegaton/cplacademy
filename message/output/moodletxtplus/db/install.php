<?php

/**
 * Post-installation script for moodletxt+
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
 * @version 2012062301
 * @since 2012062301
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Runs post-installation database tasks for moodletxt+
 * @global moodle_database $DB Moodle database manager
 * @return boolean Success
 * @version 2012062301
 * @since 2012062301
 */
function xmldb_message_moodletxtplus_install() {
    
    global $DB;
 
    $result = true;
 
    // Register plugin as a message processor
    $processor = new stdClass();
    $processor->name  = 'moodletxtplus';
    $DB->insert_record('message_processors', $processor);
    
    return $result;
    
}

?>