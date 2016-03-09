<?php

/**
 * File container for TxttoolsServerForm class
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
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <connecttxtsupport@blackboard.com>
 * @copyright Copyright &copy; 2014 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2015050101
 * @since 2015050101
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAbstractForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/TxttoolsServer.php');

/**
 * Server form - takes server details from user.
 * @package uk.co.moodletxt.forms
 * @author Andrew Kettle <connecttxtsupport@blackboard.com>
 * @copyright Copyright &copy; 2015 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2015050101
 * @since 2015050101
 */
class TxttoolsServerForm extends MoodletxtAbstractForm {

    /**
     * Sets up form for display to user
     * @global object $CFG Moodle global config
     * @version 2015050601
     * @since 2015050101
     */
    public function definition() {
        global $CFG;

        $installForm =& $this->_form;

        $defaultServerLocationList = array();
        $defaultServerLocationList[0] = get_string('adminlabelserverlocationUK'); 
        $defaultServerLocationList[1] = get_string('adminlabelserverlocationUSA'); 

        // Txttools account

        $installForm->addElement('select', 'serverLocation', get_string('adminlabelserverlocation', 'block_moodletxt'), $defaultServerLocationList);
        $installForm->setType('serverLocation', PARAM_INT);
        $txttoolsServer = new TxttoolsServer();
        $installForm->setDefault('serverLocation', $txttoolsServer->getServerLocationID());

    }

    /**
     * Validation routine for account form
     * @param array $formdata Submitted data from form
     * @param object $files File uploads from form
     * @return Array of errors, if any found
     * @version 2015050101
     * @since 2015050101
     */
    public function validation($formdata, $files = null) {
        
        $formdata['erverLocation']  = trim($formdata['serverLocation']);
        return array();
        
    }

}

?>