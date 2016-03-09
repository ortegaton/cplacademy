<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/rmc/lib.php');
require_once($CFG->libdir . '/filelib.php');
require_once("{$CFG->libdir}/formslib.php");

class full_text_search_form extends moodleform {

    //Full text search form definition
    function definition() {
        $id = required_param('course', PARAM_INT);
        $section = required_param('section', PARAM_INT);
        $type = optional_param('type', '', PARAM_ALPHA);
        $returntomod = optional_param('return', 0, PARAM_BOOL);
        $sr = optional_param('sr', 0, PARAM_BOOL);
        $cmid = optional_param('cmid', 0, PARAM_INT);
        $perpage = 5;
        $mform = $this->_form;
        $mform->addElement('html', '<div class="search_wrapper">');
        $mform->addElement('html', '<div class="smalltree"><img src="pix/logo3.png" align="absmiddle"/>' . get_string('add_new', 'rmc') . '</div>');
        $mform->addElement('html', '<div class="rmc_logo"></div>');
        $mform->addElement('hidden', 'search_type', 'FTS');
        $mform->addElement('hidden', 'course', $id);
        $mform->addElement('hidden', 'section', $section);
        $mform->addElement('hidden', 'type', $type);
        $mform->addElement('hidden', 'return', $returntomod);
        $mform->addElement('hidden', 'cmid', $cmid);
        $mform->addElement('hidden', 'sr', $sr);
        $mform->addElement('text', 'search_string', '', array('size' => '64', 'class' => 'full_search', 'placeholder' => get_string('place_holder', 'rmc')));
        $mform->addElement('html', '<div class="rmc_help">' . get_string('help_text', 'rmc') . '</div>');
        $mform->setType('search_string', PARAM_TEXT);
        $this->add_action_buttons(true, get_string('full_text_search_label', 'rmc'));
        $url = new moodle_url("/mod/rmc/advanced_search.php?add=rmc&type=$type&course=$id&section=$section&return=$returntomod&sr=$sr&cmid=$cmid");
        $advanced_link = html_writer::link($url, get_string('advanced_search_label', 'rmc'));
        $mform->addElement('html', '<div class="adv_but">' . $advanced_link . '</div>');
        $mform->addElement('html', '<div class="rmc_advanced_help">' . get_string('advanced_help', 'rmc') . '</div>');
        $mform->addElement('html', '</div>');
        $mform->addElement('hidden', 'add', 'rmc');
        $mform->addElement('hidden', 'type', $type);
        $mform->addElement('hidden', 'course', $id);
        $mform->addElement('hidden', 'section', $section);
        $mform->addElement('hidden', 'return', $returntomod);
        $mform->addElement('hidden', 'sr', $sr);
        $mform->addElement('hidden', 'perpage', $perpage);
    }

}
