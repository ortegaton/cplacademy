<?php

global $CFG;

require_once($CFG->dirroot.'/blocks/mis/lib/urllib.php');
require_once($CFG->dirroot.'/lib/datalib.php');

/**
 * class tab_base
 */
class tab_base{
    var $name;
    var $title;
    var $js;
    var $path;
    var $content;
    var $current;
    var $enabled;
    var $mdlstuid; // GT mod 2008/07/02 - moodle student id (moodle user id for student)
    var $mdluser; // GT mod 2008/07/02 - moodle student user record
    var $blockwww;

    /**
     * @author Alan Hardy
     * @param string $name required
     * @param mixed $db optional
     */
    function __construct ($name,$DB){
        $this->_set_studentdetails(); // GT mod 2008/07/02 - set student details
        $this->_set_blockwww(); // GT MOD 2008/09/10 - set blockwww
        $this->init($name,$DB);
        $this->_log_activity();        
    }
    
    private function _set_blockwww(){    
        $this->blockwww=get_mis_blockwww(); // function in urllib.php
    }
    
    private function _log_activity(){
        global $CFG;
        // GT Note - There doesn't seem to be a way to make pages within blocks loggable as urls!
        // Maybe we need to recreate the pages in mis as a module?
        $url=url::current();
        $url=str_ireplace($CFG->wwwroot.'/blocks/mis/', '', $url);        
        $url=str_ireplace(str_replace('http', 'https', $CFG->wwwroot).'/blocks/mis/', '', $url);
        $url=str_ireplace(str_replace('https', 'http', $CFG->wwwroot).'/blocks/mis/', '', $url);
        add_to_log(1, 'mis', 'view', $url, $this->name.' -> '.$this->mdluser->firstname.' '.$this->mdluser->lastname);
    }
    
    /**
     * @author Guy Thomas
     * Date: 2008/07/02
     * purpose Set student details
     */
    private function _set_studentdetails(){
        global $USER, $DB;        
        
        // set the students moodle user id
        $this->mdlstuid = isset($USER->mis_mdlstuid) ? $USER->mis_mdlstuid : false; 
                
        if (!$this->mdlstuid){
            if (isset($USER->mis_mdlstuid)){
                $this->mdlstuid=$USER->mis_mdlstuid;
            } else {
                $this->mdlstuid = optional_param('userid', '', PARAM_INT);
                $USER->mis_mdlstuid = $this->mdlstuid;
            }
        }
        
        // set the students moodle user record
        $this->mdluser = $DB->get_record('user',array('id'=>$this->mdlstuid));
        if (!$this->mdluser){
            error('User ID was incorrect');        
        }
    }

    function setName($name){
        $this->name = $name;
    }
    function setTabPath($path){
        $this->path = $path;
    }
    
    function getJs(){
        global $CFG, $USER, $PAGE;
             
        $this->js ="
        <script type=\"text/javascript\">
            var mdlsessid='{$USER->sesskey}';
            var misblockbase='{$this->blockwww}';
        </script>
        ";
        $this->js .="<link rel=\"stylesheet\" type=\"text/css\" href=\"css/style.css\" />\n";
        $this->js .="<!--[if lt IE 7]><link rel=\"stylesheet\" type=\"text/css\" href=\"css/style_ie6.css\" />\n<![endif]-->";
		$this->js .="<script type=\"text/javascript\">GTLib_ExportFuncs=true;</script>\n";
		$gtl2 = new moodle_url($CFG->wwwroot.'/blocks/mis/js/gtlib_yui/lib.gt_all.js');
		$PAGE->requires->js($gtl2);
        $this->js .="<link rel=\"stylesheet\" type=\"text/css\" href=\"".$CFG->wwwroot."/lib/gtlib_yui/widgets/dialog/themes/standard/dialog.css\" />\n";
        $this->js .="<link rel=\"stylesheet\" type=\"text/css\" href=\"".$CFG->wwwroot."/lib/gtlib_yui/widgets/dialog/themes/standard/dialog_ie6.css\" />\n";
        return $this->js;
    }
    
    function setContent($content){
        $this->content = $content;
    }

    function setEnabled($enabled){
        $this->enabled = $enabled;
    }

    
    function getTitle(){
        $this->title = "Untitled";
    }
    
    /**
    * GT MOD 2008/09/08 - get name of tab
    */
    function getName(){        
        return ($this->name);        
    }
    

    function getContent(){
        if ($this->current){
            // This should be implemented by the derived class.
            return NULL;
        }
    }
    
    /**
    * GT Mod 2008/09/08 - new render function - security improvement: also checks capibilities before rendering
    * Purpose: Render Tab
    */
    function render(){
        global $CFG;
        if ($this->check_caps()){
            echo ($this->getJs());
            echo ($this->getContent());
        } else {
            notice ('<div class="error" style="text-align:center">You do not have access to this students data.</div>', $link=$CFG->wwwroot);            
        }
    }
    
    function check_caps(){
        global $USER;        
        // check access control
        if ($this->mdluser->id != $USER->id) {
            // teachers, parents, etc.
            $personalcontext = get_context_instance(CONTEXT_USER, $this->mdluser->id);    
            return(has_capability('block/mis:viewstudent', $personalcontext));            
        }
        
        // The actual moodle user should be able to see things about themselves
        return (true);
    }
    
}
?>