<?php

/**
 * Manual enrolment plugin main library file.
 *
 * @package    enrol
 * @subpackage manual
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class enrol_zilink_plugin extends enrol_plugin {

    var $tab;
    var $data;
    var $matched = false;
    var $matcheduser;
    var $userrole;
    
    public function get_name()
    {
        return 'zilink';    
    }
    
    public function roles_protected() {
        // users may tweak the roles later
        return true;
    }

    public function allow_enrol(stdClass $instance) {
        // users with enrol cap may unenrol other users manually manually
        return true;
    }

    public function allow_unenrol(stdClass $instance) {
        // users with unenrol cap may unenrol other users manually manually
        return true;
    }

    public function allow_manage(stdClass $instance) {
        // users with manage cap may tweak period and status
        return false;
    }

    public function add_default_instance($course) {
        $fields = array('status'=>$this->get_config('status'), 'enrolperiod'=>$this->get_config('enrolperiod', 0), 'roleid'=>$this->get_config('roleid', 0));
        return $this->add_instance($course, $fields);
    }
    
    /**
     * Add new instance of enrol plugin.
     * @param object $course
     * @param array instance fields
     * @return int id of new instance, null if can not be created
     */
    public function add_instance($course, array $fields = NULL) {
        global $DB;

        if ($DB->record_exists('enrol', array('courseid'=>$course->id, 'enrol'=>'zilink'))) {
            // only one instance allowed, sorry
            return NULL;
        }

        return parent::add_instance($course, $fields);
    }
    
    public function delete_instance($instance) {
         global $DB,$CFG;
         
         require_once($CFG->dirroot.'/lib/accesslib.php');
  
         $name = $this->get_name();
          if ($instance->enrol !== $name) {
              throw new coding_exception('invalid enrol instance!');
          }
  
          //first unenrol all users
          $participants = $DB->get_recordset('user_enrolments', array('enrolid'=>$instance->id));
          foreach ($participants as $participant) {
              $this->unenrol_user($instance, $participant->userid);
          }
          $participants->close();
  
          // now clean up all remainders that were not removed correctly
          $DB->delete_records('role_assignments', array('itemid'=>$instance->id, 'component'=>$name));
          $DB->delete_records('user_enrolments', array('enrolid'=>$instance->id));
  
          // finally drop the enrol row
          $DB->delete_records('enrol', array('id'=>$instance->id));
  
          // invalidate all enrol caches
          $context = context_course::instance($instance->courseid);
          $context->mark_dirty();
      }
    
    
    /**
    * Indicates API features that the enrol plugin supports.
    *
    * @param string $feature
    * @return mixed True if yes (some features may use other values)
    */
    function enrol_zilink_supports($feature) 
    {
        switch($feature) 
        {
            case ENROL_RESTORE_TYPE: return ENROL_RESTORE_EXACT;

            default: return null;
        }
    }
    
    /**
    * Is it possible to hide/show enrol instance via standard UI?
    *
    * @param stdClass $instance
    * @return bool
    */
    public function can_hide_show_instance($instance) {

         $context = context_course::instance($instance->courseid);
         return has_capability('enrol/zilink:config', $context);
    }
    
}

require_once($CFG->dirroot . "/enrol/locallib.php");

class zilink_course_enrolment_manager extends course_enrolment_manager {
    
    public function enrol_cohort($cohortid, $roleid) {
         
        global $CFG;
        //require_capability('moodle/course:enrolconfig', $this->get_context());
        require_once($CFG->dirroot.'/enrol/zilink_cohort/locallib.php');
        $roles = $this->get_assignable_roles();
        $cohorts = $this->get_cohorts();
        
        throw new moodle_exception($cohortid.$roleid);
        //FIXME check this
        //if (!array_key_exists($cohortid, $cohorts) || !array_key_exists($roleid, $roles)) {
        //  return false;
        //}

        $enrol = enrol_get_plugin('zilink_cohort');
        $enrol->add_instance($this->course, array('customint1'=>$cohortid, 'roleid'=>$roleid));
        enrol_cohort_sync($this->course->id);
        return true;
    }
}
/*
require_once(dirname(dirname(dirname(__FILE__))).'/lib/adminlib.php');
  
class admin_setting_configbutton extends admin_setting 
{
  
    public function __construct($name, $visiblename, $description, $defaultsetting=0, $paramtype=PARAM_RAW, $size=null,$url,$label) {
       parent::__construct($name, $visiblename, $description, $defaultsetting);
       $this->url = $url;
       $this->label = $label;
    }

    public function get_setting() {
        return $this->config_read($this->name);
    }

    public function write_setting($data) {
        if ($this->paramtype === PARAM_INT and $data === '') {
        // do not complain if '' used instead of 0
            $data = 0;
        }

        $validated = $this->validate($data);
        if ($validated !== true) {
            return $validated;
        }
        return ($this->config_write($this->name, $data) ? '' : get_string('errorsetting', 'admin'));
    }

    public function validate($data) {
        return true;
    }

    public function output_html($data, $query='') {
        global $CFG;
        
        $default = $this->get_defaultsetting();

        return format_admin_setting($this, $this->visiblename,
        '<div class="form-text defaultsnext"><input type="button" style="padding: 5px; height:35px" value="'.$this->label.'" onclick="document.location =\''.$this->url.'\'" /></div>',
        $this->description, true, '', $default, $query);
    }
}
*/


?>