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


defined('MOODLE_INTERNAL') || die();

$plugin->version    = 2016020400;
$plugin->requires   = 2014111000.00;
$plugin->release    = 'v1.2.3';
$plugin->component  = 'auth_zilink_guardian';
$plugin->maturity   = MATURITY_STABLE; 

$plugin->dependencies = array(
    'local_adminer'             => ANY_VERSION,
    'block_progress'            => ANY_VERSION,
    'local_zilink'              => 2014112100,
    'enrol_zilink'              => ANY_VERSION,
    'enrol_zilink_cohort'       => ANY_VERSION,
    'enrol_zilink_guardian'     => ANY_VERSION,
);