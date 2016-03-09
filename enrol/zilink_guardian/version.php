<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2016020400;
$plugin->requires   = 2014111000.00;
$plugin->component  = 'enrol_zilink_guardian';
$plugin->release = 'v1.1.5';
$plugin->maturity   = MATURITY_STABLE;

$plugin->dependencies = array(
    'local_adminer'             => ANY_VERSION,
    'block_progress'            => ANY_VERSION,
    'local_zilink'              => ANY_VERSION,
    'enrol_zilink'              => ANY_VERSION,
    'enrol_zilink_cohort'       => ANY_VERSION,
    'enrol_zilink_guardian'     => ANY_VERSION,
);