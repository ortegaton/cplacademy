<?php

/**
 * File container for the BlacklistEntry class
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
 * @see BlacklistEntry
 * @package uk.co.moodletxt.data
 * @author Pavol Vytykáč <connecttxtsupport@blackboard.com>
 * @copyright Copyright &copy; 2014 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2015062901
 * @since 2015062901
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtBiteSizedUser.php');

/**
 * Represents a row in the block_moodletxt_blacklist table
 * @package uk.co.moodletxt.data
 * @author Pavol Vytykáč <connecttxtsupport@blackboard.com>
 * @copyright Copyright &copy; 2014 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2015062901
 * @since 2015062901
 */
class BlacklistEntry {

    private $id;
    private $accountId;
    private $phoneNumber;
    private $isBlacklisted;

    /**
     * @return mixed
     * @version 2015062901
     * @since 2015062901
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @version 2015062901
     * @since 2015062901
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     * @version 2015062901
     * @since 2015062901
     */
    public function getAccountId() {
        return $this->accountId;
    }

    /**
     * @param mixed $accountId
     * @version 2015062901
     * @since 2015062901
     */
    public function setAccountId($accountId) {
        $this->accountId = $accountId;
    }

    /**
     * @return mixed
     * @version 2015062901
     * @since 2015062901
     */
    public function getPhoneNumber() {
        return $this->phoneNumber;
    }

    /**
     * @param mixed $phoneNumber
     * @version 2015062901
     * @since 2015062901
     */
    public function setPhoneNumber($phoneNumber) {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return mixed
     * @version 2015062901
     * @since 2015062901
     */
    public function getIsBlacklisted() {
        return $this->isBlacklisted;
    }

    /**
     * @param mixed $isBlacklisted
     * @version 2015062901
     * @since 2015062901
     */
    public function setIsBlacklisted($isBlacklisted) {
        $this->isBlacklisted = $isBlacklisted;
    }

}

?>