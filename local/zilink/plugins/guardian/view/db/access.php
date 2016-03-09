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
 * Defines capabilities for Parents' scheduler block
 *
 * @package block_parentseve
 * @author Mark Johnson <johnsom@tauntons.ac.uk>, Mike Worth <mike@mike-worth.com>
 * @copyright Copyright &copy; 2009, Taunton's College, Southampton, UK
 **/

defined('MOODLE_INTERNAL') || die();
     
if(file_exists($CFG->dirroot.'/local/zilink/plugins/guardian/view/interfaces/'.$CFG->zilink_guardian_view_interface.'/db/access.php'))
{
    include($CFG->dirroot.'/local/zilink/plugins/guardian/view/interfaces/'.$CFG->zilink_guardian_view_interface.'/db/access.php');
}