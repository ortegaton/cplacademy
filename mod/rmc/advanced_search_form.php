<?php
require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/rmc/lib.php');
require_once($CFG->dirroot.'/mod/rmc/locallib.php');
require_once($CFG->libdir.'/filelib.php');
require_once("{$CFG->libdir}/formslib.php");

class advanced_search_form extends moodleform {

	function definition() {
		$id          = required_param('course', PARAM_INT);
		$section     = required_param('section', PARAM_INT);
		$type        = optional_param('type', '', PARAM_ALPHA);
		$returntomod = optional_param('return', 0, PARAM_BOOL);
		$sr = optional_param('sr', 0, PARAM_BOOL);
                $cmid = optional_param('cmid', 0, PARAM_INT);
		$mform = $this->_form;
		$publisher_options = array();
		$perpage = 5;
		//$mform->addElement('html', '<div class="smalltree"><img src="pix/logo3.png" align="absmiddle"/>'.get_string('add_new','rmc').'</div>');
		$mform->addElement("html", "<div id='search_bar'>".get_string('search_bar_text', 'rmc')."</div>");
		$mform->addElement('html', "<div class='search_area'>");
		$mform->addElement('html', '<div class="keyword">');
		$mform->addElement('html', '<div class="keyword_wrapper">');
		$mform->addElement('text', 'search_string', get_string('rmcname', 'rmc'), array('size'=>'64'));
		
		$mform->addElement('html', '</div>');
		$mform->addElement('html', '</div>');
		$mform->setType('adv_search_string', PARAM_TEXT);


		$mform->addElement('html', '<div class="keyword_2">');
        $publisher_options = rmc_helper::fetch_search_values("nvc:publisherID");
        $publisher_select = $mform->addElement('select', 'publisher', get_string('publisher_label', 'rmc'), $publisher_options);
		$publisher_select->setMultiple(true);

		$discipline_options = rmc_helper::fetch_search_values("nvc:classificationFutureDiscipline");
		$discipline_select = $mform->addElement('select', 'discipline', get_string('discipline_label', 'rmc'), $discipline_options);
		$discipline_select->setMultiple(true);
		
		
		$training_package_options = rmc_helper::fetch_search_values("nvc:trainingPackage");
		$training_package_select = $mform->addElement('select', 'training_package', get_string('training_package_label', 'rmc'), $training_package_options);
		$training_package_select->setMultiple(true);

		$resource_type_options = rmc_helper::fetch_search_values("nvc:resourceType");
		$resource_type_select = $mform->addElement('select', 'resource_type', get_string('resource_type_label', 'rmc'), $resource_type_options);
		$resource_type_select->setMultiple(true);
		$this->add_action_buttons(false, get_string('search_label', 'rmc'));
		$mform->addElement('html', '</div>');
		
		$search_type = $mform->addElement('hidden', 'search_type', 'advanced');

		$mform->addElement('hidden', 'add', 'rmc');
		$mform->addElement('hidden', 'type', $type);
		$mform->addElement('hidden', 'course', $id);
		$mform->addElement('hidden', 'section', $section);
		$mform->addElement('hidden', 'return', $returntomod);
		$mform->addElement('hidden', 'sr', $sr);
		$mform->addElement('hidden','perpage',$perpage);
		$mform->addElement('hidden', 'cmid', $cmid);
	}
}
