<?php
defined('MOODLE_INTERNAL') || die();

$handlers = array (
    'zilink_cohort_member_added' => array (
        'handlerfile'      => '/enrol/zilink_cohort/locallib.php',
        'handlerfunction'  => array('enrol_zilink_cohort_handler', 'member_added'),
        'schedule'         => 'instant',
        'internal'         => 1,
    ),

    'zilink_cohort_member_removed' => array (
        'handlerfile'      => '/enrol/zilink_cohort/locallib.php',
        'handlerfunction'  => array('enrol_zilink_cohort_handler', 'member_removed'),
        'schedule'         => 'instant',
        'internal'         => 1,
    ),

    'zilink_cohort_deleted' => array (
        'handlerfile'      => '/enrol/zilink_cohort/locallib.php',
        'handlerfunction'  => array('enrol_zilink_cohort_cohort_handler', 'deleted'),
        'schedule'         => 'instant',
        'internal'         => 1,
    ),
);