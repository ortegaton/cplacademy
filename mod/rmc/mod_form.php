<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/rmc/lib.php');
require_once($CFG->dirroot . '/mod/rmc/locallib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir.'/resourcelib.php');


class mod_rmc_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $DB;
        $mform = & $this->_form;
        
        $add = optional_param('add', '',PARAM_TEXT);
        $purchase_id = optional_param('purchase_id', '0', PARAM_INT);
        $section = optional_param('section', '0', PARAM_INT);
        if($add == 'rmc') {
        	$id = required_param('course', PARAM_INT);
			$section = required_param('section', PARAM_INT);
			redirect("$CFG->wwwroot/mod/rmc/search.php?add=$add&course=$id&section=$section");
        }
        
        $mform->addElement('text', 'name', get_string('rmcname', 'rmc'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        
        //-------------------------------------------------------
        //either we have been passed a purchase id from the search page (add new), or we have one in the db (update)
        if (isset($this->current->purchase_id)) {
            //update
            $purchase_id = $this->current->purchase_id;
        }
        if($purchase_id != 0){
            $rmc_purchase_detail = rmc_get_purchase_detail($purchase_id);
        
            if ($rmc_purchase_detail) {
            	global $PAGE;
            	$PAGE->requires->js('/mod/rmc/js/jquery-1.9.0.min.js', TRUE);
            	echo "<script>var base_path = '". $CFG->wwwroot ."';</script>";
            	$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/rmc/js/embed_url.js'), TRUE);
            	$PAGE->requires->js("/mod/rmc/js/jquery.fancybox.js");
            	$PAGE->requires->css("/mod/rmc/css/jquery.fancybox.css");
                $client = new cmis_client();
                $node_obj = $client->get_item_info($rmc_purchase_detail->node_id);
                $base_url = substr(Decryption::decrypt(ALFRESCO_URL), 0, strpos(Decryption::decrypt(ALFRESCO_URL), '/alfresco/s/cmis'));
                if (isset($node_obj->properties["nvc:thumbURL"])) {
                    $thumbnail_url = $base_url . $node_obj->properties["nvc:thumbURL"];
                } else {
                    $thumbnail_url = $client->get_andresco_thumbnail_url($node_obj);
                }
                $html = "<table><tr><td>";
                $html .= "<img src='$thumbnail_url' style='max-width: 120px !important; max-height: 200px !important' />";
                $html .= "</td><td valign='top' style='padding : 5px!important;'>";
                $html .= "<h1>" . $node_obj->properties["cm:title"] . "</h1>";
                $html .= "<p>" . $node_obj->properties["cm:description"] . "</p>";

                $html .= "<a href='" . $rmc_purchase_detail->alfresco_share_url . "' target='_blank'>" . get_string('view_rmc_file', 'mod_rmc') . "</a><br>";
                if(isset($this->_cm->course)){
                    $html .= "<a href='" . $CFG->wwwroot . "/mod/rmc/search.php?add=rmc&type=&course=" . $this->_cm->course . "&section=" . $this->_cm->section . "&cmid=" . $this->_cm->id . "' >" . get_string('change_rmc_file', 'mod_rmc') . "</a><br>";
                }
                else{
                    if(isset($cmid)){
                        $html .= "<a href='" . $CFG->wwwroot . "/mod/rmc/search.php?add=rmc&type=&course=" . $rmc_purchase_detail->course_id . "&section=$section&cmid=" . $cm_id . "' >" . get_string('change_rmc_file', 'mod_rmc') . "</a><br>";
                    }
                    else{
                        $html .= "<a href='" . $CFG->wwwroot . "/mod/rmc/search.php?add=rmc&type=&course=" . $rmc_purchase_detail->course_id . "&section=$section' >" . get_string('change_rmc_file', 'mod_rmc') . "</a><br>";
                    }
                }
                if(strstr($node_obj->properties['cmis:contentStreamMimeType'], 'image')) {
                	$html .= "<a href='javascript:void(0);' data-id='". $rmc_purchase_detail->node_id ."' id='rmc_generate_url' class='embed_gen_url' >" . get_string('gen_embed_url_lbl', 'rmc') . "</a>";
                	$html .=  "<div id='embed_popup' style='display: none;'></div>";
                }
                $html .= "</td></tr></table>";
            }
            else {
                if (isset($this->_cm->id)) {
                    $cm_param = "&cmid=" . $this->_cm->id;
                } else {
                    $cm_param = "";
                }
                $html = "<a href='" . $CFG->wwwroot . "/mod/rmc/search.php?add=rmc&type=&course=" . $_REQUEST['course'] . "&section=" . $_REQUEST['section'] . "$cm_param' >" . get_string('add_rmc_file', 'mod_rmc') . "</a>";
            }
        }
        else {
            if (isset($this->_cm->id)) {
                $cm_param = "&cmid=" . $this->_cm->id;
            } else {
                $cm_param = "";
            }
            $html = "<a href='" . $CFG->wwwroot . "/mod/rmc/search.php?add=rmc&type=&course=" . $_REQUEST['course'] . "&section=" . $_REQUEST['section'] . "&return=1&sr=0$cm_param' >" . get_string('add_rmc_file', 'mod_rmc') . "</a>";
        }
        $mform->addElement('header', 'content', get_string('contentheader', 'url'));
        $mform->addElement('html', $html);

        //-------------------------------------------------------
        $mform->addElement('header', 'optionssection', get_string('optionsheader', 'resource'));
        $config = get_config('resource');
        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }
       	unset($options[RESOURCELIB_DISPLAY_DOWNLOAD]);
       	unset($options[RESOURCELIB_DISPLAY_NEW]);
       	unset($options[RESOURCELIB_DISPLAY_FRAME]);
        
        //$options = array('Automatic','Embed','New window','Force download','Open','In pop-up');
        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'resource'), $options);
            $mform->setDefault('display', $config->display);
            $mform->setAdvanced('display', $config->display_adv);
            $mform->addHelpButton('display', 'displayselect', 'resource');
        }
        
        $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'resource'), array('size'=>3));
        if (count($options) > 1) {
        	$mform->disabledIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
        }
        $mform->setType('popupwidth', PARAM_INT);
        $mform->setDefault('popupwidth', $config->popupwidth);
        //$mform->setAdvanced('popupwidth', $config->popupwidth_adv);
        
        $mform->addElement('text', 'popupheight', get_string('popupheight', 'resource'), array('size'=>3));
        if (count($options) > 1) {
        	$mform->disabledIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
        }
        $mform->setType('popupheight', PARAM_INT);
        $mform->setDefault('popupheight', $config->popupheight);
        //$mform->setAdvanced('popupheight', $config->popupheight_adv);
        
        $mform->disabledIf('popupwidth', 'display', 'eq', RESOURCELIB_DISPLAY_EMBED);
        $mform->disabledIf('popupwidth', 'display', 'eq', RESOURCELIB_DISPLAY_AUTO);
        $mform->disabledIf('popupwidth', 'display', 'eq', RESOURCELIB_DISPLAY_OPEN);
        
        $mform->disabledIf('popupheight', 'display', 'eq', RESOURCELIB_DISPLAY_EMBED);
        $mform->disabledIf('popupheight', 'display', 'eq', RESOURCELIB_DISPLAY_AUTO);
        $mform->disabledIf('popupheight', 'display', 'eq', RESOURCELIB_DISPLAY_OPEN);

        /* $mform->addElement('checkbox', 'showsize', get_string('showsize', 'resource'));
        $mform->setDefault('showsize', $config->showsize);
        $mform->setAdvanced('showsize', $config->showsize_adv);
        $mform->addHelpButton('showsize', 'showsize', 'resource');
        $mform->addElement('checkbox', 'showtype', get_string('showtype', 'resource'));
        $mform->setDefault('showtype', $config->showtype);
        $mform->setAdvanced('showtype', $config->showtype_adv);
        $mform->addHelpButton('showtype', 'showtype', 'resource'); 

        if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'resource'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);
            $mform->setAdvanced('popupwidth', $config->popupwidth_adv);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'resource'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
            $mform->setAdvanced('popupheight', $config->popupheight_adv);
        }*/

        /* if (array_key_exists(RESOURCELIB_DISPLAY_AUTO, $options) or
          array_key_exists(RESOURCELIB_DISPLAY_EMBED, $options) or
          array_key_exists(RESOURCELIB_DISPLAY_FRAME, $options)) {
            $mform->addElement('checkbox', 'printheading', get_string('printheading', 'resource'));
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_POPUP);
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_DOWNLOAD);
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_OPEN);
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_NEW);
            $mform->setDefault('printheading', $config->printheading);
            $mform->setAdvanced('printheading', $config->printheading_adv);

            $mform->addElement('checkbox', 'printintro', get_string('printintro', 'resource'));
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_POPUP);
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_DOWNLOAD);
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_OPEN);
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_NEW);
            $mform->setDefault('printintro', $config->printintro);
            $mform->setAdvanced('printintro', $config->printintro_adv);
        } */

        $options = array('0' => get_string('none'), '1' => get_string('allfiles'), '2' => get_string('htmlfilesonly'));
        $mform->addElement('select', 'filterfiles', get_string('filterfiles', 'resource'), $options);
        $mform->setDefault('filterfiles', $config->filterfiles);
        $mform->setAdvanced('filterfiles', $config->filterfiles_adv);

        // add legacy files flag only if used
        if (isset($this->current->legacyfiles) and $this->current->legacyfiles != RESOURCELIB_LEGACYFILES_NO) {
            $options = array(RESOURCELIB_LEGACYFILES_DONE   => get_string('legacyfilesdone', 'resource'),
                             RESOURCELIB_LEGACYFILES_ACTIVE => get_string('legacyfilesactive', 'resource'));
            $mform->addElement('select', 'legacyfiles', get_string('legacyfiles', 'resource'), $options);
            $mform->setAdvanced('legacyfiles', 1);
        }
        //-------------------------------------------------------
        if (isset($_REQUEST['uuid']) && $_REQUEST['uuid'] != '') {
            //no wasteful calls to cmis
            if(!isset($client)){
                $client = new cmis_client();
            }
            if(!isset($node_obj)){
                //should only be set if we are adding
                $node_obj = $client->get_item_info($_REQUEST['uuid']);
            }
            $mform->setDefault('name', $node_obj->properties["cm:title"]);
            $mform->addElement("hidden", "uuid", $_REQUEST['uuid']);
        } else {
            $mform->setDefault('name', $this->_cm->name);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addElement("hidden", "purchase_id", $purchase_id);
        
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
    function data_preprocessing(&$default_values) {
//        if ($this->current->instance and !$this->current->tobemigrated) {
//            $draftitemid = file_get_submitted_draft_itemid('files');
//            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_resource', 'content', 0, array('subdirs'=>true));
//            $default_values['files'] = $draftitemid;
//        }
        if (!empty($default_values['displayoptions'])) {
            $displayoptions = unserialize($default_values['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $default_values['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printheading'])) {
                $default_values['printheading'] = $displayoptions['printheading'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $default_values['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $default_values['popupheight'] = $displayoptions['popupheight'];
            }
            if (!empty($displayoptions['showsize'])) {
                $default_values['showsize'] = $displayoptions['showsize'];
            } else {
                // Must set explicitly to 0 here otherwise it will use system
                // default which may be 1.
                $default_values['showsize'] = 0;
            }
            if (!empty($displayoptions['showtype'])) {
                $default_values['showtype'] = $displayoptions['showtype'];
            } else {
                $default_values['showtype'] = 0;
            }
        }
    }

}

?>
