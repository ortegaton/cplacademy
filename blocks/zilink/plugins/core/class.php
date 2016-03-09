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


defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)).'/core/interfaces.php');
require_once(dirname(__FILE__) . '/../../../../local/zilink/plugins/core/base.php');
require_once(dirname(__FILE__) . '/../../../../local/zilink/plugins/core/interfaces.php');
require_once(dirname(__FILE__) . '/../../../../local/zilink/plugins/core/person.php');

class ZiLinkPluginBase extends ZiLinkBase{

    protected $security;
    protected $person;

    public function __construct ($courseid = null, $instanceid = null, $required = true) {

        global $CFG, $DB;

        $this->instance = new stdClass();
        $this->instance->id = $instanceid;

        parent::__construct($courseid,$required);
    }
}    