<?php

/**
 * RMC mod: Search form
 *
 * @author      Daniel Morphett <dan@androgogic.com>
 * @version     17/06/2013
 * @copyright   2013+ Androgogic Pty Ltd <http://www.androgogic.com>
 *
 * Used by search page
 *
 * */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
require_once($CFG->libdir . '/formslib.php');

class search_form extends moodleform {

    function definition() {
        $mform = & $this->_form;
        
        //params
        $course         = required_param('course', PARAM_INT);
        $section        = required_param('section', PARAM_INT);
        $search_string  = optional_param('search_string', '', PARAM_RAW);
        $cmid           = optional_param('cmid', 0, PARAM_INT);
        
        
//search controls
        //$mform->addElement("html", "<div id='search_bar'>".get_string('search_bar_text', 'rmc')."</div>");
        $mform->addElement('html', "<div class='search_area'>");
        $mform->addElement('text', 'search_string', '', array('size'=>'50', 'class' => 'full_search', 'placeholder' => get_string('place_holder', 'rmc')));
        $mform->setDefault('search_string', $search_string);
        $mform->setType('search_string', PARAM_TEXT);
        $atts = array(
        		"style"=>"margin : 4px;background:#1B75BB; color:#fff; padding:11px 13px 5px 13px; height: 24px; border-radius: 4px; display:inline-block !important; text-decoration : none !important;width:auto!important;", 
        		"title" => get_string('adv_hover_text', 'rmc'),
        		"id" => 'fts_search_button');
        $advanced_url = new moodle_url("/mod/rmc/advanced_search.php?add=rmc&course=$course&section=$section");
        $mform->addElement('button', 'search_fts', get_string('search_label', 'rmc'), array('title' => get_string('fts_hover_text', 'rmc'), 'onclick' => 'javascript:fetch_search_results("FTS");'));
        $mform->addElement('html', '<div id="fitem_id_search_fts" class="fitem fitem_actionbuttons fitem_fsubmit"><div class="felement fsubmit">');
        //$mform->addElement('html',  html_writer::link($advanced_url,get_string('advanced_search_label','rmc'),$atts));
        //$mform->addElement('button', 'advanced_search', get_string('advanced_search_label','rmc'));
        $mform->addElement("html",'</div></div>');
        //$mform->addElement('html','&nbsp;');
        $mform->addElement('html', "</div>");
        $mform->addElement('header', 'advanced_search', get_string('advanced_search_label', 'rmc'), array('class' => 'coolfieldset'));
        /*$adv_search_html = "<fieldset class='rmc-advsearch-fieldset'><legend class='rmc-advsearch-legend'>". get_string('advanced_search_label', 'rmc') ."</legend>";
        $mform->addElement('html', $adv_search_html);*/
        $mform->addElement('html', '<div align="center">');
        $mform->addElement('html', '<div id="adv-search" class="keyword_2" style="display: none !important;">');
        $publisher_options = array();
        $params = array(
			'method' => 'get_publishers'
			);
		$ocurl = new Curl();
		$publisher_data = json_decode($ocurl->post(Decryption::decrypt(ALFRESCO_WEBFRONT_API_URL), $params));
		foreach($publisher_data->publist as $row) {
			$publisher_options[$row->name] = $row->name;
		}
		$options = array('' => 'None');
		$publisher_options = array_merge($options, $publisher_options);
		$publisher_select = $mform->addElement('select', 'publisher', get_string('publisher_label', 'rmc'), $publisher_options);
		$publisher_select->setMultiple(true);
		$mform->setDefault('publisher', '');

		$discipline_options = rmc_helper::fetch_search_values("nvc:classificationFutureDiscipline");
		$discipline_options = array_merge($options, $discipline_options);
		$discipline_select = $mform->addElement('select', 'discipline', get_string('discipline_label', 'rmc'), $discipline_options);
		$discipline_select->setMultiple(true);
		$mform->setDefault('discipline', '');
		
		
		$training_package_options = rmc_helper::fetch_search_values("nvc:trainingPackage");
		$training_package_options = array_merge($options, $training_package_options);
		$training_package_select = $mform->addElement('select', 'training_package', get_string('training_package_label', 'rmc'), $training_package_options);
		$training_package_select->setMultiple(true);
		$mform->setDefault('training_package', '');

		$resource_type_options = rmc_helper::fetch_search_values("nvc:pubResourceType");
		$resource_type_options = array_merge($options, $resource_type_options);
		$resource_type_select = $mform->addElement('select', 'resource_type', get_string('resource_type_label', 'rmc'), $resource_type_options);
		$resource_type_select->setMultiple(true);
		$mform->setDefault('resource_type', '');
		
		$mform->addElement('button', 'search_adv', get_string('advanced_search_button_label', 'rmc'), array('title' => get_string('adv_hover_text', 'rmc'), 'class' => 'view-con-button', 'onclick' => 'javascript:fetch_search_results("advanced");'));
		$mform->addElement('html', '</div></div>');
        
        
        
//hiddens
        $mform->addElement('hidden', 'add', 'rmc', array('id' => 'add'));
        $mform->setType('add', PARAM_TEXT);
        $mform->addElement('hidden', 'course', $course, array('id' => 'course'));
        $mform->setType('course', PARAM_INT);
        $mform->addElement('hidden', 'section', $section, array('id' => 'section'));
        $mform->setType('section', PARAM_INT);
        $mform->addElement('hidden', 'cmid', $cmid, array('id' => 'cmid'));
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'search_type', 'FTS', array('id' => 'search_type'));
        $mform->setType('search_type', PARAM_TEXT);
        $mform->addElement('hidden', 'submitbutton', 'Full Text Search', array('id' => 'submitbutton'));
        $mform->setType('submitbutton', PARAM_TEXT);

    }

}
