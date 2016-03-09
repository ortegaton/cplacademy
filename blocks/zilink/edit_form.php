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
 * Defines the class for the ZiLink block sub plugin timetable_week
 *
 * @package     block_zilink
 * @subpackage    timetable_week
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_zilink_edit_form extends block_edit_form {

    public function __construct($actionurl, $block, $page) {

        if ($block->add_content_region()) {
            $block->instance->defaultregion = 'content';
            $block->instance->region = 'content';
        }
        parent::__construct($actionurl, $block, $page);
    }

    protected function specific_definition($mform) {

        global $CFG, $DB, $OUTPUT, $COURSE, $USER;

        if (!isset($this->block->config->type) || empty($this->block->config->type)) {

            require_once($CFG->dirroot . '/local/zilink/plugins/core/security.php');

            $security = new ZiLinkSecurity();

            $mform->addElement('header', 'configheader', get_string('zilink_block_type', 'block_zilink'));

            $path = dirname(__FILE__) . '/plugins';

            $list = array();
            foreach (new DirectoryIterator($path) as $file) {
                if ($file->isDot()) {
                    continue;
                }

                if (is_dir($path . '/' . $file->getFilename())) {

                    if ($file->getFilename() != 'core') {

                        if ($security->IsAllowed('block/zilink:' . $file->getFilename() . '_addinstance')) {
                            $list[$file->getFilename()] = get_string($file->getFilename(), 'block_zilink');
                        }

                    }
                }
            }
            ksort($list);
            if (empty($list)) {
                $mform->addElement('static',    'description',
                                                get_string('zilink_block_type', 'block_zilink'),
                                                get_string('zilink_plugin_installation_prohibited', 'block_zilink'));
            } else {
                $mform->addElement('select', 'config_type', get_string('zilink_block_type', 'block_zilink'), $list);
            }
        } else {
            $mform->addElement('header', 'configfooter',
                                         get_string('zilink_block_settings',
                                         'block_zilink',
                                         get_string($this->block->config->type, 'block_zilink')));

            if (file_exists(dirname(__FILE__) . '/plugins/' . $this->block->config->type . '/form.php')) {
                include(dirname(__FILE__) . '/plugins/' . $this->block->config->type . '/form.php');
            } else {
                $mform->addElement('html', get_string('zilink_no_settings_required', 'block_zilink'));
            }
        }

        $mform->addElement('hidden', 'config_courseid', $COURSE->id);
        $mform->setType('config_courseid', PARAM_INT);
    }

    public function set_data($defaults) {
        if (!empty($this->block->config) && is_object($this->block->config)) {
            $defaults->config_text['title'] = (isset($this->block->config->title)) ? $this->block->config->title : '';
        } else {
            $text = '';
        }
        parent::set_data($defaults);
    }

}
