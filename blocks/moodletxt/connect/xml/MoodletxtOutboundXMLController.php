<?php

/**
 * File container for the MoodletxtOutboundXMLController class
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
 * @see MoodletxtOutboundXMLController
 * @package uk.co.moodletxt.connect.xml
 * @author Greg J Preece <connecttxtsupport@blackboard.com>
 * @copyright Copyright &copy; 2014 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2015062901
 * @since 2010090301
*/

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/connect/MoodletxtOutboundController.php');

require_once($CFG->dirroot . '/blocks/moodletxt/connect/xml/MoodletxtXMLBuilder.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/xml/MoodletxtXMLParser.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/xml/MoodletxtXMLConnector.php');

/**
 * Controls transmissions to and from the txttools XML API
 * @package uk.co.moodletxt.connect.xml
 * @author Greg J Preece <connecttxtsupport@blackboard.com>
 * @copyright Copyright &copy; 2014 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2015062901
 * @since 2010090301
*/
class MoodletxtOutboundXMLController extends MoodletxtOutboundController {

    /**
     * Builds XML for sending to txttools
     * @var MoodletxtXMLBuilder
     */
    private $XMLBuilder;

    /**
     * Parses incoming XML from txttools
     * @var MoodletxtXMLParser
     */
    private $XMLParser;

    /**
     * Sends data to and from txttools system
     * @var MoodletxtXMLConnector
     */
    private $outboundConnector;

    /**
     * Constructor - sets up processing objects
     * @version 2011041501
     * @since 2010090301
     */
    public function __construct() {
        $this->XMLBuilder = new MoodletxtXMLBuilder();
        $this->XMLParser = new MoodletxtXMLParser();
        $this->outboundConnector = new MoodletxtXMLConnector(get_config('moodletxt', 'Use_Protocol'));
                
    }

    /**
     * Sends an outbound message via the txttools system
     * @param MoodletxtOutboundMessage $outboundMessage The message to send
     * @return MoodletxtOutboundSMS[] Sent message responses
     * @version 2015062901
     * @since 2010090301
     */
    public function sendMessage(MoodletxtOutboundMessage $outboundMessage) {
        $requests = $this->XMLBuilder->buildOutboundMessage($outboundMessage);
        $response = $this->outboundConnector->sendData($requests, $outboundMessage->getTxttoolsAccount());
        $this->XMLParser->setOutboundMessageObject($outboundMessage);
        return $this->XMLParser->parse($response);
    }

    /**
     * Updates given SMS messages with their latest status updates
     * @param MoodletxtOutboundSMS[] $sentMessages Sent messages
     * @param TxttoolsAccount $txttoolsAccount Account to check against
     * @return MoodletxtOutboundSMS[] Updated SMS messages
     * @version 2015062901
     * @since 2010090301
     */
    public function getSMSStatusUpdates($sentMessages, TxttoolsAccount $txttoolsAccount) {
        $requests = $this->XMLBuilder->buildStatusRequest($sentMessages, $txttoolsAccount);
        $response = $this->outboundConnector->sendData($requests, $txttoolsAccount);
        $this->XMLParser->setExistingSentMessages($sentMessages);
        return $this->XMLParser->parse($response);
    }

    /**
     * Updates given SMS opt out statuses for given numbers. Or according to message?
     * @param array() $phoneNumbers
     * @param TxttoolsAccount $txttoolsAccount
     * @return array of statuses keyed by phone numbers
     * @version 2015062901
     * @since 2015041501
     */
    public function getOptOutStatusUpdates($phoneNumbers, $txttoolsAccount) {
        $requests = $this->XMLBuilder->buildBuildOptOutStatusCheckRequest($txttoolsAccount, $phoneNumbers);
        $response = $this->outboundConnector->sendData($requests, $txttoolsAccount);
        return $this->XMLParser->parse($response);
    }

    /**
     * Returns credit information for a given txttools account
     * @param TxttoolsAccount $txttoolsAccount Account to check
     * @return TxttoolsAccount Updated account object
     * @version 2015062901
     * @since 2011040701
     */
    public function updateAccountInfo(TxttoolsAccount $txttoolsAccount) {
        $requests = $this->XMLBuilder->buildCreditInfo($txttoolsAccount);
        $response = $this->outboundConnector->sendData($requests, $txttoolsAccount);
        $this->XMLParser->setTxttoolsAccountObject($txttoolsAccount);
        $parsed = $this->XMLParser->parse($response);
        return $parsed[0];
    }

    /**
     * Fetches all inbound messages for given accounts (normally triggered via cron)
     * @param TxttoolsAccount[] $txttoolsAccounts The accounts to check
     * @return MoodletxtInboundMessage[] Inbound messages found
     * @version 2015062901
     * @since 2010090301
     */
    public function getInboundMessages($txttoolsAccounts = array()) {
        $responses = array();

        $lastUpdate = get_config('moodletxt', 'Inbound_Last_Update');
        
        foreach($txttoolsAccounts as $account) {
            try {
                $requests = $this->XMLBuilder->buildInboundMessageRequest($account, 0, 'ALL', $lastUpdate);
                $response = $this->outboundConnector->sendData($requests, $account);
                $this->XMLParser->setTxttoolsAccountObject($account); // Needed to set destination account
                $responses = array_merge($responses, $this->XMLParser->parse($response));
            } catch (Exception $ex){
                    file_put_contents('C:\\moodle.log', time() . ': cron inbound poll failed for account (' . $account->getUsername()
                        . '@' . $account->getUrl() . ') -> ' . $ex->getMessage() , FILE_APPEND);
            }
        }

        set_config('Inbound_Last_Update', time(), 'moodletxt');        
        
        return $responses;
    }

    /**
     * Fetches location info of the server this account is bound to (UK/US)
     * @param TxttoolsAccount $txttoolsAccount
     * @return mixed
     * @version 2015062901
     * @since 2015062403
     */
    function getServerLocation($txttoolsAccount) {
        try {
            $requests = $this->XMLBuilder->buildBuildOptOutStatusCheckRequest($txttoolsAccount, array('+199999999'));
            $this->outboundConnector->sendData($requests, $txttoolsAccount);

            // status code 200
            // the server does support opt-out checks
            return TxttoolsAccount::$US_LOCATION;
        }catch(Exception $ex){
            // status code 500 causes an exception
            // the server does not support opt-out checks
            return TxttoolsAccount::$UK_LOCATION;
        }
    }

}

?>