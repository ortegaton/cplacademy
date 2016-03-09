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


defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)).'/core/class.php');
require_once(dirname(dirname(__FILE__)).'/core/interfaces.php');

class icon_navigation extends ZiLinkPluginBase implements iZiLinkBlockPlugin {

    public function __construct($courseid = null, $instanceid = null) {
        global $DB;

        parent::__construct($courseid, $instanceid,false);
    }

    public function SetTitle() {
        return '';
    }

    public function GetBlockContent($block) {

        global $CFG, $OUTPUT, $PAGE, $USER, $COURSE, $DB;

        $content = $block->content;
        $content->icons[] = '';
        $icons = '';

        $links = array();
        $subjects = array();

        $iconsize = (isset($CFG->zilink_icon_navigation_size)) ? $CFG->zilink_icon_navigation_size : 50;

        if (isloggedin()) {
            $courses = enrol_get_users_courses($USER->id, true);

            $notifications = array();
            if (!empty($courses)) {

                $mdlcourses = array();

                foreach ($courses as $course) {
                    $category = $DB->get_record('course_categories', array('id' => $course->category));

                    if (isset($category->ctxpath)) {
                        $tree = explode('/', $category->ctxpath);
                    } else {
                        $tree = explode('/', $category->path);
                    }

                    for ($i = 1; $i < count($tree); $i++) {

                        if ($tree[$i] == $CFG->zilink_category_root) {

                            $parentcategory = $DB->get_record('course_categories', array('id' => $tree[$i + 1]));
                            if ($course->visible == 1 || $this->person->Security()->IsAllowed('moodle/course:viewhiddencourses')) {
                                $subjects[str_replace(  '\'',
                                        '',
                                        str_replace(' ',
                                        '_',
                                        strtolower($parentcategory->name)))][$parentcategory->id][$category->id][] = $course->id;
                            }
                        } else if ($CFG->zilink_category_root == 0 && $i == 1) {
                            $parentcategory = $DB->get_record('course_categories', array('id' => $tree[$i]));
                            if ($course->visible == 1 || $this->person->Security()->IsAllowed('moodle/course:viewhiddencourses')) {
                                $subjects[str_replace(  '\'',
                                        '',
                                        str_replace(' ',
                                        '_',
                                        strtolower($parentcategory->name)))][$parentcategory->id][$category->id][] = $course->id;
                            }
                        }
                    }

                    $coursemodules = $DB->get_records_sql('SELECT cm.id, m.name, cm.instance '.
                                                          'FROM {course_modules} cm, {modules} m '.
                                                          'WHERE cm.module = m.id AND cm.course = ?', array($course->id));

                    foreach ($coursemodules as $mod) {

                        $module = $DB->get_record($mod->name, array('id' => $mod->instance));

                        if ($module->name <> 'News forum') {
                            $lastaccess = $DB->get_record('user_lastaccess',
                                                           array('courseid' => $course->id,
                                                           'userid' => $USER->id));

                            if (!is_object($lastaccess)) {
                                $lastaccess = new stdClass();
                                $lastaccess->timeaccess = $USER->lastaccess - strtotime("-24 hours");
                            }
                            if (isset($module->timemodified)) {
                                if (((int)$module->timemodified > (int)$lastaccess->timeaccess) && ($course->visible == 1)) {
                                    if (isset($CFG->{'zilink_icon_navigation_category_icon_'.$parentcategory->id})) {
                                        if (!isset(
                                            $notifications[$CFG->{'zilink_icon_navigation_category_icon_'.$parentcategory->id}])) {
                                            $notifications[$CFG->{'zilink_icon_navigation_category_icon_'.$parentcategory->id}] = 1;
                                        } else {
                                            $notifications[$CFG->{'zilink_icon_navigation_category_icon_'.$parentcategory->id}]++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $courses = array();
                ksort($subjects);

                foreach ($subjects as $subject => $parentcategories) {

                    foreach ($parentcategories as $category => $subcategories) {
                        if (count($subcategories) == 1) {
                            $key = key($subcategories);
                            $courses = array_shift($subcategories);

                            if (count($courses) == 1) {
                                $course = $DB->get_record('course', array('id' => $courses[0]));
                                $url = new moodle_url('/course/view.php',
                                                        array( 'sesskey' => sesskey(),
                                                        'id' => $course->id));

                                if (isset($CFG->{'zilink_icon_navigation_category_icon_'.$category})) {
                                    $subject = $CFG->{'zilink_icon_navigation_category_icon_'.$category};
                                    $file = $CFG->dirroot.'/blocks/zilink/pix/icon_navigation/'.
                                            $CFG->zilink_icon_navigation_iconset.'/subjects/'.$subject.'.*';

                                    $pix = 'icon_navigation/'.$CFG->zilink_icon_navigation_iconset.'/subjects/'.$subject;

                                    if (count(glob($file)) > 0 &&
                                        $CFG->{'zilink_icon_navigation_category_icon_'.$category} <> '0') {

                                        $link = $OUTPUT->pix_icon($pix,
                                                                    '', 'block_zilink',
                                                                    array('height' => $iconsize.'px',
                                                                         'width' => $iconsize.'px',
                                                                         'style' => 'margin: 1px; float: left',
                                                                         'title' => $course->shortname, 'class' => 'none'));
                                        if (isset($notifications[$subject])) {
                                            if ($notifications[$subject] > 9) {
                                                $pic = 'max';
                                            } else {
                                                $pic = $notifications[$subject];
                                            }
                                            $link .= $OUTPUT->pix_icon('icon_navigation/'.
                                                                $CFG->zilink_icon_navigation_iconset.'/numbers/'.$pic,
                                                                '', 'block_zilink',
                                                                array('height' => '25px',
                                                                      'width' => '25px',
                                                                       'style' => 'z-index: 2; float: left; left: '.
                                                                       ($iconsize - 18) . 'px; bottom: '.
                                                                       ($iconsize + 12) . 'px; position: relative;',
                                                                'title' => 'New Content' ));
                                        }
                                        $icons .= '<li style="float:left; width: '.($iconsize + 5).'px; height: '.
                                                    ($iconsize + 10).'px">'.html_writer::link($url, $link).'</li>';
                                    }
                                }
                            } else {
                                $cat = $DB->get_record('course_categories', array('id' => $key));
                                
                                if($CFG->version >= 2013111800) {
                                    $url = new moodle_url('/course/index.php', array( 'sesskey' => sesskey(), 'categoryid' => $cat->id));
                                } else {
                                    $url = new moodle_url('/course/category.php', array( 'sesskey' => sesskey(), 'id' => $cat->id));
                                }

                                if (isset($CFG->{'zilink_icon_navigation_category_icon_'.$category})) {
                                    $subject = $CFG->{'zilink_icon_navigation_category_icon_'.$category};
                                    $file = $CFG->dirroot.'/blocks/zilink/pix/icon_navigation/'.
                                            $CFG->zilink_icon_navigation_iconset.'/subjects/'.$subject.'.*';
                                    $pix = 'icon_navigation/'.$CFG->zilink_icon_navigation_iconset.'/subjects/'.$subject;

                                    if (count(glob($file)) > 0 &&
                                        $CFG->{'zilink_icon_navigation_category_icon_'.$category} <> '0') {

                                        $link = $OUTPUT->pix_icon($pix, '',
                                                                 'block_zilink',
                                                                 array( 'height' => $iconsize.'px',
                                                                        'width' => $iconsize.'px',
                                                                        'style' => 'margin: 1px; float: left',
                                                                        'title' => $cat->name,
                                                                        'class' => 'none' ));
                                        if (isset($notifications[$subject])) {
                                            if ($notifications[$subject] > 9) {
                                                $pic = 'max';
                                            } else {
                                                $pic = $notifications[$subject];
                                            }
                                            $link .= $OUTPUT->pix_icon('icon_navigation/'.
                                                    $CFG->zilink_icon_navigation_iconset.'/numbers/'.$pic,
                                                    '', 'block_zilink', array('height' => '25px',
                                                                             'width' => '25px',
                                                                             'style' => 'z-index: 2; float: left; left: '
                                                                             .($iconsize - 18).'px; bottom: '.
                                                                             ($iconsize + 12).'px; position: relative;',
                                                                             'title' => 'New Content' ));
                                        }
                                        $icons .= '<li style="float:left; width: '.($iconsize + 5).'px; height: '.
                                                    ($iconsize + 10).'px">'.html_writer::link($url, $link).'</li>';
                                    }
                                }
                            }
                        } else {
                            $cat = $DB->get_record('course_categories', array('id' => $category));
                            
                            if($CFG->version >= 2013111800) {
                                $url = new moodle_url('/course/index.php', array( 'sesskey' => sesskey(), 'categoryid' => $cat->id));
                            } else {
                                $url = new moodle_url('/course/category.php', array( 'sesskey' => sesskey(), 'id' => $cat->id));
                            }

                            if (isset($CFG->{'zilink_icon_navigation_category_icon_'.$category})) {

                                if ($CFG->{'zilink_icon_navigation_category_icon_'.$category} <> -1) {
                                    if (($cat->visible == 0 &&
                                        $this->person->Security()->IsAllowed('moodle/course:viewhiddencourses')) ||
                                        $cat->visible == 1) {

                                        $subject = $CFG->{'zilink_icon_navigation_category_icon_'.$category};
                                        $file = $CFG->dirroot.'/blocks/zilink/pix/icon_navigation/'.
                                                $CFG->zilink_icon_navigation_iconset.'/subjects/'.$subject.'.*';
                                        $pix = 'icon_navigation/'.$CFG->zilink_icon_navigation_iconset.'/subjects/'.$subject;

                                        if (count(glob($file)) > 0  &&
                                            $CFG->{'zilink_icon_navigation_category_icon_'.$category} <> '0') {
                                            $link = $OUTPUT->pix_icon($pix, '',
                                                                      'block_zilink',
                                                                      array(  'height' => $iconsize.'px',
                                                                                'width' => $iconsize.'px',
                                                                                'style' => 'margin: 1px; float: left',
                                                                                'title' => $cat->name,
                                                                                'class' => 'none'));
                                            if (isset($notifications[$subject])) {
                                                if ($notifications[$subject] > 9) {
                                                    $pic = 'max';
                                                } else {
                                                    $pic = $notifications[$subject];
                                                }
                                                $link .= $OUTPUT->pix_icon('icon_navigation/'.
                                                                $CFG->zilink_icon_navigation_iconset.'/numbers/'.$pic,
                                                                '',
                                                                'block_zilink', array('height' => '25px',
                                                                'width' => '25px',
                                                                'style' => 'z-index: 2; float: left; left: '.
                                                                ($iconsize - 18).'px; bottom: '.($iconsize + 12).
                                                                'px; position: relative;',
                                                                'title' => 'New Content' ));
                                            }
                                            $icons .= '<li style="float:left; width: '.($iconsize + 5).
                                                        'px; height: '.($iconsize + 10).'px">'.
                                                        html_writer::link($url, $link).'</li>';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $categories = $DB->get_records('course_categories', array('parent' => $CFG->zilink_category_root));

                foreach ($categories as $category) {

                    $file = str_replace('\'', '', str_replace(' ', '_', strtolower($category->name)));
                    
                    if($CFG->version >= 2013111800) {
                        $url = new moodle_url('/course/index.php', array( 'sesskey' => sesskey(), 'categoryid' => $category->id));
                    } else {
                        $url = new moodle_url('/course/category.php', array( 'sesskey' => sesskey(), 'id' => $category->id));
                    }

                    if (isset($CFG->{'zilink_icon_navigation_category_icon_'.$category->id})) {

                        $subject = $CFG->{'zilink_icon_navigation_category_icon_'.$category->id};
                        $file = $CFG->dirroot.'/blocks/zilink/pix/icon_navigation/'.
                                $CFG->zilink_icon_navigation_iconset.'/subjects/'.$subject.'.*';

                        $pix = 'icon_navigation/'.$CFG->zilink_icon_navigation_iconset.'/subjects/'.$subject;

                        if (count(glob($file)) > 0 &&
                            $CFG->{'zilink_icon_navigation_category_icon_'.$category->id} <> '0') {

                            if (($category->visible == 0  &&
                                $this->person->Security()->IsAllowed('moodle/course:viewhiddencourses')) ||
                                $category->visible == 1) {

                                $link = $OUTPUT->pix_icon($pix, '', 'block_zilink', array('height' => $iconsize.'px',
                                                                                         'width' => $iconsize.'px',
                                                                                         'style' => 'margin: 1px; float: left',
                                                                                         'title' => $category->name,
                                                                                         'class' => 'none' ));
                                $icons .= '<li style="float:left; width: '.($iconsize + 5).'px; height: '.
                                            ($iconsize + 10).'px">'.html_writer::link($url, $link).'</li>';
                            }
                        }
                    }
                }
            }
        }
        if (empty($icons)) {
            $content = null;
        } else {
            $content->items[] = '<ul>'.$icons.'</ul>'.'<div class="clearer"></div>';
        }
        return $content;
    }

    public function RequireContentRegion() {
        return true;
    }

    public function HideHeader() {
        return true;
    }

    public function Cron() {
        return false;
    }
}