<?php

/**
 * File container for the TxttoolsAccount class
 * 
 * moodletxt is distributed as GPLv3 software, and is provided free of charge without warranty. 
 * A full copy of this licence can be found @
 * http://www.gnu.org/licenses/gpl.html
 * In addition to this licence, as described in section 7, we add the following terms:
 *   - Derivative works must preserve original authorship attribution (@author tags and other such notices)
 *   - Derivative works do not have permission to use the trade and service names 
 *     "ConnectTxt", "txttools", "moodletxt", "moodletxt+", "Blackboard", "Blackboard Connect" or "Cy-nap"
 *   - Derivative works must be have their differences from the original material noted,
 *     and must not be misrepresentative of the origin of this material, or of the original service
 * 
 * Anyone using, extending or modifying moodletxt indemnifies the original authors against any contractual
 * or legal liability arising from their use of this code.
 * 
 * @see TxttoolsAccount
 * @package uk.co.moodletxt.data
 * @author Andrew Kettle <connecttxtsupport@blackboard.com>
 * @copyright Copyright &copy; 2015 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2015050101
 * @since 2015050101
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Represents a txttools server within the system
 * @package uk.co.moodletxt.data
 * @author Andrew Kettle <connecttxtsupport@blackboard.com>
 * @copyright Copyright &copy; 2015 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2015050101
 * @since 2015050101
 */
class TxttoolsServer {

    /**
     * Represents a server location of UK
     * @var int
     */
    public static $SERVER_LOCATION_UK = 0;
    public static $SERVER_LOCATION_DEFAULT = 0;

    /**
     * Represents a server location of USA
     * @var int
     */
    public static $SERVER_LOCATION_USA = 1;
    
    /**
     * UK Server host address
     */
    public static $SERVER_HOST_UK = 'bbconnecttxt.com';
    
    /**
     * USA Server host address
     */
    public static $SERVER_HOST_USA = 'us.bbconnecttxt.com';
    
           
    /**
     * Returns the current server location ID
     * @return integer Server location ID
     * @version 2015050101
     * @since 2015050101
     */
    public function getServerLocationID() {
        $serverLocationID = intval(get_config('moodletxt', 'Server_Location'));
        if ($serverLocationID == self::$SERVER_LOCATION_USA) {
            return $serverLocationID;
        } else {
            return self::$SERVER_LOCATION_UK;
        }
    }

    /**
     * Set the current server location ID
     * @param integer Server location ID
     * @version 2015050101
     * @since 2015050101
     */
    public function setServerLocationID($serverLocationID) {
        if ($serverLocationID == self::$SERVER_LOCATION_USA) {
            set_config('Server_Location', strval($serverLocationID), 'moodletxt');
        } else {
            set_config('Server_Location', strval(self::$SERVER_LOCATION_UK), 'moodletxt');
        }
    }

    /**
     * Returns a text string of the server location specified by ID
     * @return string Server location name
     * @version 2015050101
     * @since 2015050101
     */
    public function getServerLocationName() {
        $serverLocationID = intval(get_config('moodletxt', 'Server_Location'));
        if ($serverLocationID == self::$SERVER_LOCATION_USA) {
            return get_string('adminlabelserverlocationUSA');; 
        } else {
            return get_string('adminlabelserverlocationUK');;
        }
    }
    
    /**
     * Returns server location host string, which is part of the server URL.
     * @return string Server location name
     * @version 2015050501
     * @since 2015050501
     */
    public function getServerLocationHost() {
        $serverLocationID = getServerLocationID();
        $serverLocationHost = null;
        if ($serverLocationID == self::$SERVER_LOCATION_USA) {
            $serverLocationHost = get_config('moodletxt', 'USA_Server_Location_Host');
        } else {
            $serverLocationHost = get_config('moodletxt', 'UK_Server_Location_Host');
        }
        
        if($serverLocationHost == null) {
            $serverLocationHost = self::$SERVER_HOST_UK;
        }
        
        return $serverLocationHost;
    }
    
    /**
     * Determines if server supports Opt-Out (blacklisting)
     * @return boolean Does server support Opt-Out?
     * @version 2015050501
     * @since 2015050501
     */
    public function doesServerSupportOptOut() {
        $serverLocationID = getServerLocationID();
        return ($serverLocationID === self::$SERVER_LOCATION_USA);
    }

}

?>
