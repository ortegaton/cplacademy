<?php
/**
 * File container for the IBlacklistDAO.php class
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
 * @see IBlacklistDAO.php
 * @package uk.co.moodletxt.dao
 * @author pvytykac <connecttxtsupport@blackboard.com>
 * @copyright Copyright &copy; 2014 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2015062901
 * @since 2015062901
 *
 */

/**
 *
 * @package uk.co.moodletxt.dao
 * @author pvytykac <connecttxtsupport@blackboard.com>
 * @copyright Copyright &copy; 2014 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2015062901
 * @since 2015062901
 */
interface IBlacklistDAO {

    /**
     *
     * @param string $phoneNumber
     * @param integer $accountId
     * @return boolean true if provided phoneNumber is blacklisted for the specific ConnectTxt account, false otherwise
     * @version 2015062901
     * @since 2015062901
     */
    public function isPhoneNumberBlacklistedForAccount($phoneNumber, $accountId);

    /**
     * @param string $phoneNumber
     * @return boolean true if provided phoneNumber is blacklisted for any ConnectTxt account, false otherwise
     * @version 2015062901
     * @since 2015062901
     */
    public function isPhoneNumberBlacklisted($phoneNumber);

    /**
     * @param integer $accountId
     * @param integer $is_blacklisted
     * @return BlacklistEntry[]
     * @version 2015062901
     * @since 2015062901
     */
    public function getBlacklistEntriesForAccount($accountId, $is_blacklisted);

    /**
     * @param integer $is_blacklisted
     * @return BlacklistEntry[]
     * @version 2015062901
     * @since 2015062901
     */
    public function getBlacklistEntries($is_blacklisted);

    /**
     * @param BlacklistEntry[] $blacklistEntries
     * @version 2015062901
     * @since 2015062901
     */
    public function saveBlacklistEntries($blacklistEntries);

    /**
     *
     * @param integer $accountId
     * @return void
     * @version 2015062901
     * @since 2015062901
     */
    public function removeAllBlacklistEntriesForAccount($accountId);

    /**
     * @param array $blacklistIds
     * @version 2015062901
     * @since 2015062901
     */
    public function removeBlacklistEntries($blacklistIds);

}