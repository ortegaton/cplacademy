<?php

/**
 * File container for the BlacklistDAO.php class
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
 * @see BlacklistDAO.php
 * @package uk.co.moodletxt.dao
 * @author pvytykac <connecttxtsupport@blackboard.com>
 * @copyright Copyright &copy; 2014 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2015062901
 * @since 2015062901
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/IBlacklistDAO.php');

/**
 * Provides db operations on BlacklistEntry entity
 * @package uk.co.moodletxt.dao
 * @author pvytykac <connecttxtsupport@blackboard.com>
 * @copyright Copyright &copy; 2014 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2015062901
 * @since 2015062901
 */
class BlacklistDAO implements IBlacklistDAO {

    private static $TABLE_NAME          = 'block_moodletxt_blacklist';
    private static $ID_COL              = 'id';
    private static $ACCOUNT_ID_COL      = 'account_id';
    private static $PHONE_NUMBER_COL    = 'phone_number';
    private static $IS_BLACKLISTED_COL  = 'is_blacklisted';

    public static $BLACKLISTED          = 1;
    public static $WHITELISTED          = 0;

    /**
     *
     * @param string $phoneNumber
     * @return boolean true if provided phoneNumber is blacklisted for any ConnectTxt account, false otherwise
     * @version 2015062901
     * @since 2015062901
     */
    public function isPhoneNumberBlacklisted($phoneNumber) {
        global $DB;

        $resultSet = $DB->get_records(BlackListDAO::$TABLE_NAME,
            array(BlacklistDAO::$PHONE_NUMBER_COL   => $phoneNumber,
                  BlacklistDAO::$IS_BLACKLISTED_COL => 1));

        return sizeof($resultSet) > 0;
    }

    /**
     *
     * @param string $phoneNumber
     * @param integer $accountId
     * @return boolean true if provided phoneNumber is blacklisted for the specific ConnectTxt account, false otherwise
     * @global moodle_database $DB Moodle database manager
     * @version 2015062901
     * @since 2015062901
     */
    public function isPhoneNumberBlacklistedForAccount($phoneNumber, $accountId) {
        global $DB;

        $resultSet = $DB->get_records(BlackListDAO::$TABLE_NAME,
                array(BlacklistDAO::$ACCOUNT_ID_COL   => $accountId,
                    BlacklistDAO::$PHONE_NUMBER_COL   => $phoneNumber,
                    BlacklistDAO::$IS_BLACKLISTED_COL => 1));

        return sizeof($resultSet) > 0;
    }

    /**
     *
     * @param integer $accountId
     * @param integer $is_blacklisted
     * @return BlacklistEntry[]
     * @version 2015062901
     * @since 2015062901
     */
    public function getBlacklistEntriesForAccount($accountId, $is_blacklisted) {
        global $DB;

        $results = $DB->get_records(
            BlacklistDAO::$TABLE_NAME,
            array(
                BlacklistDAO::$ACCOUNT_ID_COL     => $accountId,
                BlacklistDAO::$IS_BLACKLISTED_COL => $is_blacklisted)
        );

        return $this->resultSetToEntity($results);
    }

    /**
     *
     * @param integer $is_blacklisted
     * @return BlacklistEntry[]
     * @version 2015062901
     * @since 2015062901
     */
    public function getBlacklistEntries($is_blacklisted) {
        global $DB;

        $resultSet = $DB->get_records(
            BlacklistDAO::$TABLE_NAME,
            array(
                BlacklistDAO::$IS_BLACKLISTED_COL => $is_blacklisted));

        return $this->resultSetToEntity($resultSet);
    }

    /**
     *
     * @param BlacklistEntry[] $blacklistEntries
     * @version 2015062901
     * @since 2015062901
     */
    public function saveBlacklistEntries($blacklistEntries) {
        global $DB;

        $converted = $this->entitiesToStdClass($blacklistEntries);
        $DB->insert_records(BlacklistDAO::$TABLE_NAME, $converted);
    }

    /**
     *
     * @return void
     * @param array $blacklistIds
     * @version 2015062901
     * @since 2015062901
     */
    public function removeBlacklistEntries($blacklistIds) {
        global $DB;

        $DB->delete_records_list(BlacklistDAO::$TABLE_NAME, BlacklistDAO::$ID_COL, $blacklistIds);
    }

    /**
     * @param integer $accountId
     * @return void
     * @version 2015062901
     * @since 2015062901
     */
    public function removeAllBlacklistEntriesForAccount($accountId) {
        global $DB;

        $DB->delete_records_list(BlacklistDAO::$TABLE_NAME, BlacklistDAO::$ACCOUNT_ID_COL, array($accountId));
    }

    /**
     * @param $resultSet
     * @return BlacklistEntry
     * @version 2015062901
     * @since 2015062901
     */
    private function resultSetToEntity($resultSet) {
        $entities = array();

        foreach($resultSet as $id => $result){
            $entity = new BlacklistEntry();
            $entity->setId($id);
            $entity->setAccountId($result->accountId);
            $entity->setPhoneNumber($result->phone_number);
            $entity->setIsBlacklisted($result->is_blacklisted);

            $entities[$id] = $entity;
        }

        return $entities;
    }


    /**
     * @param BlacklistEntry[] $entities
     * @return stdClass[]
     * @version 2015062901
     * @since 2015062901
     */
    private function entitiesToStdClass($entities){
        $objects = array();

        foreach($entities as $entity){
            $object = new stdClass();
            $object->id = $entity->getId();
            $object->account_id = $entity->getAccountId();
            $object->phone_number = $entity->getPhoneNumber();
            $object->is_blacklisted = $entity->getIsBlacklisted();

            $objects[] = $object;
        }

        return $objects;
    }

}
