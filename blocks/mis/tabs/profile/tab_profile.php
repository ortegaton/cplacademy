 <?php
 	/**
	* (c) Alan Hardy and Guy Thomas - Frederick Gent School & Ossett School 2008
	* 
	* Licence - GNU GENERAL PUBLIC LICENSE - Version 3, 29 June 2007
	*           Refer to http://www.gnu.org/licenses/gpl.html for full terms
	*
	* Version - Alpha 
	*
	* Date    - 2008-07-02
	*
	* Project - MIS - Facility to Moodle integration
	*
	**/
	
    require_once(dirname(__FILE__).'/../../../../config.php');    
    require_once($CFG->dirroot.'/blocks/mis/cfg/config.php');
	require_once($CFG->dirroot.'/course/lib.php');    
	require_once($CFG->dirroot.'/blocks/mis/lib/lib_facility_db.php');     


    global $CFG, $fdata;
    $fdata=new facilityData();
    
 	class tab_profile extends tab_base{
		
		function init($name){
 			$this->setName($name);
 		}
 		
 		function getTitle(){
			$this->title = 'Account Info';
			return $this->title;
		}
        
        function getJs(){
            global $CFG;
            
            $stuname=$this->mdluser->firstname. " " . $this->mdluser->lastname;
            
            $this->js=parent::getJs();       
            $this->js.='<script type="text/javascript">
            var mis_stuname="'.$stuname.'";
            </script>
            ';
            $this->js.='<script type="text/javascript" src="'.$this->blockwww.'/js/profile.js"></script>';
            return ($this->js);
        }
		
		function getContent(){
            global $USER, $CFG, $fdata, $HTTPSPAGEREQUIRED;                    

            // Is the entire site https?
            /*
            $siteishttps=$CFG->mis->siteishttps;
                        
            if (!$siteishttps){
                // Override https setting of $CFG->wwwroot
                $CFG->wwwroot=get_mis_www();
                $HTTPSPAGEREQUIRED=isset($CFG->mis->https) && $CFG->mis->https;
            }
            */
            
            // force no-ssl
            $CFG->wwwroot=str_ireplace('https://', 'http://', $CFG->wwwroot);
            
            // Get facility student id
            if($this->mdluser === false) {
                $content=print_simple_box('Unable to locate this user', '', '', '', '', 'errorbox');
                return $content;
            }
            $studentid = $fdata->getStuAdminNo($this->mdluser->idnumber);
           
           
            $tmpurl=$CFG->wwwroot.'/course/user.php?id=1&sesskey='.$USER->sesskey.'&user='.$this->mdlstuid;            
            
            /*
            if (!$siteishttps && $HTTPSPAGEREQUIRED){
                $profileurl=$this->blockwww.'/tabs/profile/sslprofile.php?id=1&sesskey='.$USER->sesskey.'&user='.$this->mdlstuid;
            } else {
                $profileurl=$tmpurl;
            } 
            */            
            
            $profileurl=$tmpurl;
           
            $content = "<div id=\"misMain\">";
			$content .= html_writer::start_tag('div', array('id'=>'block_mis_wrapper'));
            $content.="<div style='text-align:center'>\n";
            $content.='<div id="mis_loadingstatus" class="ajaxloading">Please wait, loading account information</div>';
            $content.='<div><iframe name="mis_profileview" id="mis_profileview" style="display:none;" frameborder="0" src="'.$profileurl.'"></iframe></div>'."\n";
            $content.="</div>\n";
			$content .= html_writer::end_tag('div');
            $content.="\n".'</div>';             
                        
            return $content;
		}
 	}
 
 ?>
 