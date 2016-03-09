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


class block_zilink extends block_list {

    public function  init() {
        global $CFG;

        $this->title = get_string('pluginname', 'block_zilink');
        $this->location = dirname(__FILE__);
        $this->deprecated_plugins = array(  'iconbar',
                                            'openbadges_viewer',
                                            'school_picture',
                                            'student_reporting',
                                            'tutor_view',
                                            'current_view',
                                            'emailer',
                                            'report_writer');

    }

    public function  has_config() {
        return true;
    }

    public function  specialization() {

        if (isset($this->config) && $this->config <> null && isset($this->config->title)) {
            $this->title = $this->config->title;
        } else {
            $title = $this->block_method('SetTitle');
            if ($title) {
                $this->title = $title;
            } else {
                $this->title = format_string(get_string('pluginname', 'block_zilink'));
            }
        }
    }

    public function  add_content_region() {
        return $this->block_method('RequireContentRegion');
    }

    public function  applicable_formats() {
        return array('all' => true);
    }

    public function  hide_header() {
   
        if (isset($this->config->hide_title)) {
            if ($this->config->hide_title == '1') {
                return true;
            }
            return false;
        }
        return  $this->block_method('HideHeader'); 
    }

    public function  instance_allow_multiple() {
        return true;
    }

    public function  instance_allow_config() {
        if (has_capability('moodle:siteconfig')) {
            return true;
        }
        return false;
    }

    public function  get_content() {
        global $CFG, $COURSE, $OUTPUT, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        if (!isset($this->config->type) || $this->config->type == null) {
            if (isloggedin()) {
                $this->content->icons[] = '';
                $this->content->items[] = 'Please configure this block';
            }
        } else {
            $this->content = $this->block_method('GetBlockContent');
        }
        return $this->content;
    }

    public function  instance_config_save($data, $nolongerused = false) {
        global $DB;

        $config = clone($data);
        parent::instance_config_save($config, $nolongerused);
    }

    public function  instance_delete() {
        global $DB;
        return true;
    }

    public function  content_is_trusted() {
        global $SCRIPT;
        return true;
    }

    public function  block_method($method) {
        global $CFG, $COURSE;

        if (!isset($this->config->type)) {
            $this->_load_zilink_instance($this->instance, $this->page);
        }

        if (isset($this->config->type)) {
            if (in_array($this->config->type, $this->deprecated_plugins)) {
                $this->content->icons[] = '';
                $this->content->items[] = get_string('zilink_plugin_deprecated', 'block_zilink');
            }

            if (file_exists($this->location . '/plugins/' . $this->config->type . '/class.php')) {
                require_once(dirname(__FILE__) . '/plugins/' . $this->config->type . '/class.php');
                try {
                    $plugin = new $this->config->type($COURSE->id, $this->instance->id);
                    if (is_object($plugin)) {
                        if (is_callable(array($plugin, $method))) {
                            return call_user_func(array($plugin, $method), $this);
                        }
                    }
                } catch (Exception $e) {
                    if ($CFG->debug == DEBUG_DEVELOPER && is_siteadmin()) {
                        $this->content->icons[] = '';
                        $this->content->items[] = $e->getMessage();
                    } else {
                        $content = null;
                    }
                }
            } else {
                if ($method == 'GetBlockContents') {
                    $this->content->icons[] = '';
                    $this->content->items[] = get_string('zilink_plugin_missing', 'block_zilink');
                }
            }
        }
        return false;
    }

    public function  _load_zilink_instance($instance, $page) {
        if (!empty($instance->configdata)) {
            $this->config = unserialize(base64_decode($instance->configdata));
        }
        if (isset($instance->id)) {
            $this->context = context_block::instance($instance->id);
        }
    }

    public function  cron() {

        $this->block_method('Cron');
        return true;
    }

}
