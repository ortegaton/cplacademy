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
 * Defines the settings for the ZiLink local
 *
 * @package     enrol_zilink_cohort
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

$plugin->version    = 2015101800;
$plugin->requires   = 2014111000.00;
$plugin->component  = 'enrol_zilink_cohort';
$plugin->release = 'v1.6.6';
$plugin->cron    = 60;
$plugin->maturity   = MATURITY_STABLE;

$plugin->dependencies = array(
    'local_adminer'             => ANY_VERSION,
    'block_progress'            => ANY_VERSION,
    'local_zilink'              => ANY_VERSION,
    'auth_zilink_guardian'      => ANY_VERSION,
    'enrol_zilink'              => ANY_VERSION,
    'enrol_zilink_guardian'     => ANY_VERSION,
);