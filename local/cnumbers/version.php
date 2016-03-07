<?php
// This file is part of Moodle - http://moodle.org/
// This file is part of the Local cnumber plugin
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
 * This plugin generates a new number for each course based on type of the course (online or F2F). 
 * for each month it generates a new number
 *
 * @package    local
 * @subpackage cnumbers
 * @copyright  2015 Jack Bradley jack.bradley@cartelsystems.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$plugin->version  = 2015102901;
$plugin->requires = 2012120300;
$plugin->release = '1.1 (Build: 2015102900)';
$plugin->maturity = MATURITY_STABLE;
$plugin->component = 'local_cnumbers';
$plugin->cron = 1;