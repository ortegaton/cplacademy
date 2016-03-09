<?php

/**
 * File container for message_output_moodletxtplus class
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
 * @package uk.co.moodletxtplus
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012101801
 * @since 2012062301
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot.'/message/output/lib.php');

/**
 * Main plugin class for moodletxt+
 * @package uk.co.moodletxtplus
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012101801
 * @since 2012062301
 */
class message_output_moodletxtplus extends message_output {
    
    /**
     * Sends a message by taking what it receives from the Moodle
     * system and throwing it as an event, which moodletxt classic
     * can then catch and send. A bit roundabout, but saves on
     * a lot of potential duplication of effort.
     * @param object $message Message being sent
     * @version 2012101801
     * @since 2012062301
     */
    public function send_message($message) {
                
        // Check that the moodletxt block is set up for this kind of sending
        // (Called explicity as this method only appeared as of Moodle 2.1)
        if (! $this->is_system_configured())
            return false; // Moodle will store the message for re-processing if config changes
        
        // If the moodletxt block is set up to receive these events, throw one
        $SMSEvent = clone $message;
        $SMSEvent->component = 'message_moodletxtplus';
        $SMSEvent->from_id = $SMSEvent->userfrom->id;
        $SMSEvent->to_id = $SMSEvent->userto->id;
        unset($SMSEvent->userfrom);
        unset($SMSEvent->userto);
        unset($SMSEvent->fullmessagehtml);
        
        events_trigger('message_moodletxtplus_message_send', $SMSEvent);
        
        return $message->savedmessageid; // This is silly - Moodle needs to differentiate between processors
    }

    /**
     * Defines the config form fragment used on user
     * messaging preferences interface (message/edit.php)
     * @param object $preferences Preferences form to modify
     * @return string Preference fields to add
     * @version 2012062301
     * @since 2012062301
     */
    public function config_form($preferences) {
       // Nothing to do yet
    }
 
    /**
     * Processes the data from the config form fragment
     * (used in message/edit.php)
     * @param object $form Form object
     * @param array $preferences Preference set
     * @version 2012062301
     * @since 2012062301
     */
    public function process_form($form, &$preferences) {
       // Nothing to do yet
    }

    /**
     * Loads initial config data from the database to
     * populate the form with
     * @param array $preferences Preference set
     * @param int $userid ID of user to get preferences for
     * @version 2012062301
     * @since 2012062301
     */
    public function load_data(&$preferences, $userid) {
       // Nothing to do yet
    }
 
    /**
     * Returns whether all the necessary config settings 
     * have been set to allow this plugin to be used
     * @return bool True if system is ready
     * @version 2012101801
     * @since 2012062301
     */
    public function is_system_configured() {
        
        // Check that the moodletxt block is set up for this kind of sending
        // @TODO: Possibly make an API class on the moodletxt classic side of things
        // to abstract this away from the possibility of config options/values changing
        $defaultAccount = (int) get_config('moodletxt', 'Event_Messaging_Account');
        
        return (is_int($defaultAccount) && $defaultAccount > 0);
    }
    
    /**
     * Returns whether the user has completed all the necessary settings
     * in their profile to allow this plugin to be used
     * @param object $user The user, defaults to $USER.
     * @return bool True if user is configured
     * @version 2012062301
     * @since 2012062301
     */
    public function is_user_configured($user = null) {
        return true; // No user settings required
    }

    /**
     * Default message output settings for this output, for
     * message providers that do not specify what the settings should be for
     * this output in the messages.php file
     * @return int Message settings mask
     * @version 2012062301
     * @since 2012062301
     */
    public function get_default_messaging_settings() {
        return MESSAGE_PERMITTED;
    }
    
}


?>