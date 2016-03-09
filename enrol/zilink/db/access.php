<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'enrol/zilink:enrol' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
        )
    ),
    'enrol/zilink:config' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
        )
    )
);

