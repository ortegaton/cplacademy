<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version    = 2016012000;
$plugin->requires   = 2014111000.00;
$plugin->component  = 'enrol_zilink';
$plugin->maturity   = MATURITY_STABLE;
$plugin->release    = 'v2.0.4';
$plugin->cron       = 300;

$plugin->dependencies = array(
    'local_adminer'             => ANY_VERSION,
    'block_progress'            => ANY_VERSION,
    'local_zilink'              => ANY_VERSION,
    'auth_zilink_guardian'      => ANY_VERSION,
    'enrol_zilink_cohort'       => ANY_VERSION,
    'enrol_zilink_guardian'     => ANY_VERSION,
);