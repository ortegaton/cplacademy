<?php

defined('MOODLE_INTERNAL') || die();

class enrol_zilink_guardian_plugin extends enrol_plugin {

    public function get_name()
    {
        return 'zilink_guardian';
    }
    
    public function get_info_icons(array $instances) {
        foreach ($instances as $instance) {
            return array(new pix_icon('icon', get_string('pluginname', 'enrol_zilink_guardian'), 'enrol_zilink_guardian'));
        }
    }
    
    public function enrol_user(stdClass $instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null) {
        // no real enrolments here!
        return;
    }

    public function unenrol_user(stdClass $instance, $userid) {
        // nothing to do, we never enrol here!
        return;
    }

    public function try_guestaccess(stdClass $instance) {
        global $USER, $DB, $CFG;

        $guardian_role = $DB->get_record('role',array('shortname' => 'zilink_guardians'),'*',MUST_EXIST );
        $guardian_resticted_role = $DB->get_record('role',array('shortname' => 'zilink_guardians_restricted'),'*',MUST_EXIST );
        
        $sql = "SELECT c.instanceid, c.instanceid, u.id, u.idnumber, u.firstname, u.lastname
                                         FROM {role_assignments} ra,
                                              {context} c,
                                              {user} u
                                         WHERE ra.userid = $USER->id
                                         AND   ra.roleid IN ($guardian_role->id,$guardian_resticted_role->id)
                                         AND   ra.contextid = c.id
                                         AND   c.instanceid = u.id
                                         AND   c.contextlevel = ".CONTEXT_USER;

        $students = $DB->get_records_sql($sql,null);
        
        if(count($students) == 0)
            return false;
        
        foreach($students as $student) {
            
            
            $courses = enrol_get_all_users_courses($student->id);

            if(is_array($courses)) {
                
                foreach($courses as $course) {
                       
                    if ($course->id == $instance->courseid) {
                         
                        $context = context_course::instance($instance->courseid);
                        
                        if(function_exists('load_temp_course_role')) {
                            load_temp_course_role($context, $guardian_role->id);
                        } else {
                            $USER->access = load_temp_role($context, $guardian_role->id, $USER->access);
                        }
                        return ENROL_REQUIRE_LOGIN_CACHE_PERIOD + time();
                    }
                }    
            }
        }    
 
        return false;
    }

    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        global $DB;

        $context = context_course::instance( $courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/zilink_guardian:config', $context)) {
            return NULL;
        }

        if ($DB->record_exists('enrol', array('courseid'=>$courseid, 'enrol'=>'zilink_guardian'))) {
            return NULL;
        }

        return new moodle_url('/enrol/zilink_guardian/addinstance.php', array('sesskey'=>sesskey(), 'id'=>$courseid));
    }

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(stdClass $instance) {
        global $CFG, $OUTPUT, $SESSION, $USER;

        if (empty($instance->password)) {
            return null;
        }

        require_once("$CFG->dirroot/enrol/zilink_guardian/locallib.php");
        $form = new enrol_zilink_guardian_enrol_form(NULL, $instance);
        $instanceid = optional_param('instance', 0, PARAM_INT);

        if ($instance->id == $instanceid) {
            if ($data = $form->get_data()) {
                // set up primitive require_login() caching
                unset($USER->enrol['enrolled'][$instance->courseid]);
                $USER->enrol['tempguest'][$instance->courseid] = time() + 60*60*8; // 8 hours access before asking for pw again

                $context = context_course::instance($instance->courseid);
                        
                if(function_exists('load_temp_course_role')) {
                    load_temp_course_role($context, $guardian_role->id);
                } else {
                    $USER->access = load_temp_role($context, $guardian_role->id, $USER->access);
                }

                // go to the originally requested page
                if (!empty($SESSION->wantsurl)) {
                    $destination = $SESSION->wantsurl;
                    unset($SESSION->wantsurl);
                } else {
                    $destination = "$CFG->httpswwwroot/course/view.php?id=$instance->courseid";
                }
                redirect($destination);
            }
        }

        ob_start();
        $form->display();
        $output = ob_get_clean();

        return $OUTPUT->box($output, 'generalbox');
    }

    /**
     * Adds enrol instance UI to course edit form
     *
     * @param object $instance enrol instance or null if does not exist yet
     * @param MoodleQuickForm $mform
     * @param object $data
     * @param object $context context of existing course or parent category if course does not exist
     * @return void
     */
    public function course_edit_form($instance, MoodleQuickForm $mform, $data, $context) {

        $i = isset($instance->id) ? $instance->id : 0;
        $plugin = enrol_get_plugin('zilink_guardian');
        $header = $plugin->get_instance_name($instance);

        $mform->addElement('header', 'enrol_zilink_guardian_header_'.$i, $header);

        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
                         
        $mform->addElement('select', 'enrol_zilink_guardian_status_'.$i, get_string('status', 'enrol_zilink_guardian'), $options);
        $mform->addHelpButton('enrol_zilink_guardian_status_'.$i, 'status', 'enrol_zilink_guardian');
        $mform->setDefault('enrol_zilink_guardian_status_'.$i, $this->get_config('status'));
       
        // now add all values from enrol table
        if ($instance) {
            foreach($instance as $key=>$val) {
                $data->{'enrol_zilink_guardian_'.$key.'_'.$i} = $val;
            }
        }
    }

    /**
     * Validates course edit form data
     *
     * @param object $instance enrol instance or null if does not exist yet
     * @param array $data
     * @param object $context context of existing course or parent category if course does not exist
     * @return array errors array
     */
    public function course_edit_validation($instance, array $data, $context) {
        $errors = array();

        if (!has_capability('enrol/zilink_guardian:config', $context)) {
            // we are going to ignore the data later anyway, they would nto be able to fix the form anyway
            return $errors;
        }
        return $errors;
    }

    /**
     * Called after updating/inserting course.
     *
     * @param bool $inserted true if course just inserted
     * @param object $course
     * @param object $data form data
     * @return void
     */
    public function course_updated($inserted, $course, $data) {
        global $DB;

        $context = context_course::instance( $course->id);

        if (has_capability('enrol/zilink_guardian:config', $context)) {
            if ($inserted) {
                if (isset($data->enrol_zilink_guardian_status_0)) {
                    $fields = array('status'=>$data->enrol_zilink_guardian_status_0);
                    $this->add_instance($course, $fields);
                }
            } else {
                $instances = $DB->get_records('enrol', array('courseid'=>$course->id, 'enrol'=>'zilink_guardian'));
                foreach ($instances as $instance) {
                    $i = $instance->id;

                    if (isset($data->{'enrol_zilink_guardian_status_'.$i})) {
                        $instance->status       = $data->{'enrol_zilink_guardian_status_'.$i};
                        $instance->timemodified = time();

                        $DB->update_record('enrol', $instance);
                    }
                }
            }

        } else {
            if ($inserted) {
                if ($this->get_config('defaultenrol')) {
                    $this->add_default_instance($course);
                }
            } else {
                // bad luck, user can not change anything
            }
        }
    }

    /**
     * Add new instance of enrol plugin with default settings.
     * @param object $course
     * @return int id of new instance
     */
    public function add_default_instance($course) {
        $fields = array('status'=>$this->get_config('status'));

        return $this->add_instance($course, $fields);
    }

     /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/zilink_guardian:config', $context);
    }
    
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/zilink_guardian:config', $context);
    }
    
}

/**
 * Indicates API features that the enrol plugin supports.
 *
 * @param string $feature
 * @return mixed True if yes (some features may use other values)
 */
function enrol_zilink_guardian_supports($feature) {
    switch($feature) {
        case ENROL_RESTORE_TYPE: return ENROL_RESTORE_NOUSERS;

        default: return null;
    }
}
