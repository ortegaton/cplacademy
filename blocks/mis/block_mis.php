<?php
/**
* UK MIS Integration Block
* Currently supports Facility CMIS only
* Original @author Alan Hardy , Guy Thomas
* Original @copyright (c) Alan Hardy, Guy Thomas 2008
* Updated @author Marc Coyles (for Moodle 2.2+)
* Updated @copyright (c) Marc Coyles / Ossett Academy 2012
* @licence http://www.gnu.org/licenses/gpl.html
*
* Versioning
* 2012021610
* - Rewritten to support Moodle2.2+ with Facility-to-Moodle block of same version
*
* 2009051900
* - Added capability for students to view their own details
*
* 2009040200
* - Fixed last_date_of_month bug - was returning last day, note UTS
* - Modified attendance to not show non-statistical absences
* 
* 2009032500
* - Replaced PHP 5 usage DateTime - only works with PHP 5.2.0 + 
* 
* 2009031000
* - Fixed bug with attendance calendar not displaying correctly
* 
* 2009021200
* - Language string improvements by Eddie Mclafferty
* - Date improvements suggested by Eddie Mclafferty implemented by Guy Thomas
* - Added calendar block wrapper for welcome page
* 2009012200
* - Display sql results
* - Debug only for admin option in config $cfg->debugadminonly
* 2009011600
* - DB connection mode detected better according to config file.
* - DB sql execution in debug mode now reports seconds taken to execute sql
* - Attendance getAttDetails() sql optimised for mssql 2005
* - Added moodle logging for tab view actions.
* 2009011400
* - fixed assessment comment bug
* 2009010500
* - fixed previous month date bug for any date containing January as the month
* - fixed bug with ukstustats alias being incorrect (reported and fix suggestion by Allan Kealey)
* 2008112806
* - introduced view any student option (to be used by teachers, etc)
* - fixed timetable bug
* - made sure all cmis sql uses apostrophies ' instead of speech marks " to open and close sql (faster and moodle recommended for coding)
* - made sure all cmis tables are prefixed with configurable table prefix $cfg->cmisTabPrefix ($fdata->prefix). Essential for compatiblility with schools who can't connect to CMIS db using stud_admin user.
*
* 2008091000 - GThomas
* - Replaced $facilityData global variable with fdata.
* - Replaced $cfg global variable with property of Moodle config - $CFG->mis.
* - Added https config option.
* - Added blockwww property to tab
* - Tabs now have title component used on each tab - this is important because you might have a class 'tab_profile' but actually want to title it 'account info'
*
* 2008070200 - GThomas
* - Removed all use of $SESSION and replaced with native Moodle session handling ($USER->)
* - Modified base tab class to provide current students user record and id as a property
*/

require_once($CFG->dirroot.'/blocks/mis/lib/urllib.php');
require_once($CFG->dirroot.'/blocks/mis/cfg/config.php');
global $DB;
class block_mis extends block_list {

    var $blockcontext;
    var $blockwww;
    var $blockdir;
	
    function init() {
        global $CFG;
        $this->title = get_string('ParentPortal', 'block_mis');
        
        $this->blockwww=$CFG->wwwroot.'/blocks/mis/';
        $this->blockdir=$CFG->dirroot.'/blocks/mis/';
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function specialization() {
        if (!empty($this->config->title)) {    
			$this->title = $this->config->title;  
		} else {    
			$config = new stdClass();
			$this->config = $config;
			$this->config->title = get_string('configtitle', 'block_mis');  
		}   
		if (empty($this->config->text)) {    
			$this->config->text = get_string('pluginname', 'block_mis');  
		}    
    }

    function instance_allow_multiple() {
        return true;
    }

    function get_content() {
        
        global $CFG, $USER, $DB;
        
        // return content if already set
        if ($this->content !== NULL) {
            return $this->content;
        }   

        // init content
        $this->content=new stdclass();        
        
        // set block context
        $this->blockcontext = get_context_instance(CONTEXT_BLOCK, $this->instance->id);     
        
        // make sure user should be able to see block
        if (!$this->caps_view()){
            return null;
        }        

        $usercontexts = $DB->get_records_sql('SELECT c.instanceid, u.firstname, u.lastname
         FROM {role_assignments} ra LEFT JOIN
              {role} r ON r.id=ra.roleid LEFT JOIN
              {context} c ON c.id=ra.contextid LEFT JOIN
              {user} u ON u.id=c.instanceid
         WHERE ra.userid = ?
         AND   r.shortname = \'parent\'
         AND   c.contextlevel = ? ',array($USER->id,CONTEXT_USER)); 
                                         
                                    
        // Show title if user is admin or can manage assessments                  
        if ($usercontexts && (isadmin() || $this->caps_manageassessments())){            
			$randomchunk=html_writer::tag('div','Children',array('class'=>'title'));                
            $this->content->items[] = html_writer::tag('div',$randomchunk,array('class'=>'header'));
            $this->content->icons[] = '';                                        
        }
        
        if ($usercontexts){
            // List children
            foreach ($usercontexts as $usercontext) {
                $this->content->items[] = html_writer::tag('a',fullname($usercontext),array('href'=>get_mis_blockwww().'?userid='.$usercontext->instanceid.'&amp;course=1&amp;sesskey='.$USER->sesskey));
                $this->content->icons[] = html_writer::empty_tag('img',array('src'=>$this->blockwww.'pix/child.png','class'=>'icon')); 
            }
        }
		
		// if user has capability to see their own details then provide link
		if ($this->caps_view_own()){
                $this->content->items[] = html_writer::tag('a',get_string('mis:viewmydetails','block_mis'),array('href'=>get_mis_blockwww().'?userid='.$USER->id.'&amp;course=1&amp;sesskey='.$USER->sesskey));
                $this->content->icons[] = html_writer::empty_tag('img',array('src'=>$this->blockwww.'pix/details.png','class'=>'icon')); 
		}
        
        if ((has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))) || $this->caps_manageassessments() || $this->caps_viewanystudent()){
			$randomchunk2=html_writer::tag('div','Admin Options',array('class'=>'mis_septitle'));
            $this->content->items[] = html_writer::tag('div',$randomchunk2,array('class'=>'mis_header'));
            $this->content->icons[] = '';
            if ($this->caps_manageassessments()){
                $this->content->items[] = html_writer::tag('a','Manage Assessments',array('href'=>$this->blockwww.'manageassessments.php?sesskey='.$USER->sesskey));
                $this->content->icons[] = html_writer::empty_tag('img',array('src'=>$this->blockwww.'pix/assessment.png','class'=>'icon'));
            }
            if ($this->caps_viewanystudent()){
                $this->content->items[] = html_writer::tag('a','View Any Student',array('href'=>$this->blockwww.'viewstudents.php?sesskey='.$USER->sesskey));
                $this->content->icons[] = html_writer::empty_tag('img',array('src'=>$this->blockwww.'pix/students.png','class'=>'icon'));
            }            
        }
        
        $this->content->footer = '';

        return $this->content;
		}
    
    function caps_view(){
        return (has_capability('block/mis:viewblock', $this->blockcontext));
    }
	
    function caps_view_own(){
        return (has_capability('block/mis:viewown', $this->blockcontext));
    }	
    
    function caps_manageassessments(){
        return (has_capability('block/mis:manageassessments', $this->blockcontext));
    }
    
    function caps_viewanystudent(){
        return (has_capability('block/mis:viewanystudent', $this->blockcontext));
    }
    function has_config() {return true;}
}
?>