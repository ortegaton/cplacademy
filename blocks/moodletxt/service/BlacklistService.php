<?php
/**
 * File container for the BlacklistService.php class
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
 * @see BlacklistService.php
 * @package
 * @author pvytykac <connecttxtsupport@blackboard.com>
 * @copyright Copyright &copy; 2014 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2015062901
 * @since 2015062901
 *
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/service/IBlacklistService.php');

require_once($CFG->dirroot . '/blocks/moodletxt/dao/IBlacklistDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/BlacklistDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsAccountDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtAddressbookDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtMoodleUserDAO.php');

require_once($CFG->dirroot . '/blocks/moodletxt/data/BlacklistEntry.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/TxttoolsAccount.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtBiteSizedUser.php');

require_once($CFG->dirroot . '/blocks/moodletxt/connect/MoodletxtOutboundControllerFactory.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/MoodletxtOutboundController.php');

/**
 *
 * @package
 * @author pvytykac <connecttxtsupport@blackboard.com>
 * @copyright Copyright &copy; 2014 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2015062901
 * @since 2015062901
 */
class BlacklistService implements IBlacklistService {

    /**
     * @var MoodletxtMoodleUserDAO
     * @version 2015062901
     * @since 2015062901
     */
    private $userDAO;

    /**
     * @var MoodletxtAddressbookDAO
     * @version 2015062901
     * @since 2015062901
     */
    private $addressbookDAO;

    /**
     * @var BlacklistDAO
     * @version 2015062901
     * @since 2015062901
     */
    private $blacklistDAO;


    /**
     * @var TxttoolsAccountDAO
     * @version 2015062901
     * @since 2015062901
     */
    private $accountDAO;


    /**
     * @var MoodletxtOutboundController
     * @version 2015062901
     * @since 2015062901
     */
    private $xmlController;

    /**
     * BlacklistService constructor.
     * @version 2015062901
     * @since 2015062901
     */
    public function __construct() {
        $this->blacklistDAO = new BlacklistDAO();
        $this->xmlController = MoodletxtOutboundControllerFactory::getOutboundController(
            MoodletxtOutboundControllerFactory::$CONTROLLER_TYPE_XML);
        $this->accountDAO = new TxttoolsAccountDAO();
        $this->userDAO = new MoodletxtMoodleUserDAO();
        $this->addressbookDAO = new MoodletxtAddressbookDAO();
    }


    /**
     * @return BlacklistEntry[]
     * @version 2015062901
     * @since 2015062901
     */
    public function getBlacklistedEntries() {
        return $this->blacklistDAO->getBlacklistEntries(1);
    }

    /**
     * @return void
     * @version 2015062901
     * @since 2015062901
     */
    public function pollForBlacklistedRecipients() {
        $usAccounts = $this->accountDAO->getAccountByLocation(TxttoolsAccount::$US_LOCATION);

        foreach($usAccounts as $account) {
            try {
                $phoneNumbers = $this->getAllPhoneNumbers();
                $blacklistedNumbers = $this->xmlController->getOptOutStatusUpdates($phoneNumbers, $account);
                $this->syncDbWithResponse($blacklistedNumbers, $account);
            }catch(Exception $ex){
                // XML request might throw exceptions in case of some errors
                // Ignored
                file_put_contents('C:\\moodle.log', time() . ': cron blacklist poll failed for account (' . $account->getUsername()
                    . '@' . $account->getUrl() . ') -> ' . $ex->getMessage() , FILE_APPEND);
            }
        }
    }

    /**
     * @return array
     * @version 2015062901
     * @since 2015062901
     */
    private function getAllPhoneNumbers() {
        $numbers = array();

        $contactNumbers = $this->addressbookDAO->getPhonenumbersOfAllContacts();
        $users = $this->userDAO->getAllUsers();

        foreach($contactNumbers as $number){
            $numbers[] = $number;
        }

        foreach($users as $user){
            if($user->getRecipientNumber() != NULL && $user->getRecipientNumber()->getPhoneNumber() != NULL)
                $numbers[] = $user->getRecipientNumber()->getPhoneNumber();
        }

        return array_unique($numbers);
    }


    /**
     * @param array $blacklistedNumbers
     * @param TxttoolsAccount $account
     * @version 2015062901
     * @since 2015062901
     */
    private function syncDbWithResponse($blacklistedNumbers, $account){
        $blacklistEntries = array();

        foreach($blacklistedNumbers as $number => $status){
            $blacklistEntry = new BlacklistEntry();
            $blacklistEntry->setPhoneNumber($number);
            $blacklistEntry->setIsBlacklisted(BlacklistDAO::$BLACKLISTED);
            $blacklistEntry->setAccountId($account->getId());

            $blacklistEntries[] = $blacklistEntry;
        }

        $this->blacklistDAO->removeAllBlacklistEntriesForAccount($account->getId());
        $this->blacklistDAO->saveBlacklistEntries($blacklistEntries);
    }


}