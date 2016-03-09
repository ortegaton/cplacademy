<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines the class for the ZiLink block sub plugin timetable_week
 *
 * @package     block_zilink
 * @subpackage    timetable_week
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/core/class.php');
require_once(dirname(dirname(__FILE__)) . '/core/interfaces.php');

class papercut extends ZiLinkPluginBase implements iZiLinkBlockPlugin {

    public function __construct($courseid = null, $instanceid = null) {
        global $DB;

        parent::__construct($courseid, $instanceid,false);

    }

    public function GetBlockContent($block) {
        global $CFG, $OUTPUT, $PAGE, $USER, $COURSE, $DB;

        $content = $block->content;
        $courses = array();

        if (isloggedin() && $USER->auth <> 'zilink_guardian') {
            $text = '';
            $icon = '';
            $serverip = '';

            if (!isset($_SERVER['SERVER_ADDR']) || empty($_SERVER['SERVER_ADDR'])) {
                if (isset($_SERVER['LOCAL_ADDR']) && !empty($_SERVER['SERVER_ADDR'])) {
                    $serverip = $_SERVER['LOCAL_ADDR'];
                } else {
                    $serverip = '0.0.0.0';
                }
            } else {
                $serverip = $_SERVER['SERVER_ADDR'];
            }

            $serverip = explode('.', $serverip);
            $internal = address_in_subnet(getremoteaddr(), $serverip[0] . '.' . $serverip[1]);

            if (($CFG->zilink_papercut_external == '1' || $internal) && !empty($CFG->zilink_papercut_url)) {

                $text = '<script type="text/javascript" src="' . $CFG->zilink_papercut_url .
                        '/content/widgets/widgets.js"></script><script type="text/javascript"> var pcUsername = "' .
                         $USER->username . '"; var pcServerURL = \'' .
                         $CFG->zilink_papercut_url . '\'; pcGetUserDetails(); </script>';

                $text .= '<div id="widget" style="padding-left: 1.5em;">' .
                            $OUTPUT->pix_icon('papercut/balance_not_avaliable', '', 'block_zilink') .
                            '<!-- User Balance widget will be rendered here --></div>';

                if ($CFG->zilink_papercut_widget == 'balance') {
                    $text .= '<script type="text/javascript"> pcInitUserBalanceWidget(\'widget\'); </script>';
                } else if ($CFG->zilink_papercut_widget == 'environment') {
                    $text .= '<script type="text/javascript"> pcInitUserEnvironmentalImpactWidget(\'widget\');</script>';
                }
            } else if ($this->person->Security()->IsAllowed('moodle/site:config')) {
                $content->icons[] = $icon;
                $content->items[] = 'PaperCut server not accessible externally';
            }
            $content->icons[] = $icon;
            $content->items[] = $text;
        }
        return $content;
    }

    public function RequireContentRegion() {
        return false;
    }

    public function SetTitle() {
        return get_string('papercut', 'block_zilink');
    }

    public function HideHeader() {
        return true;
    }

    public function Cron() {
        return true;
    }

}
