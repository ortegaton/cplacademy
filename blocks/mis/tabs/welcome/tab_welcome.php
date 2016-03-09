 <?php
     class tab_welcome extends tab_base{
        
        function init($name){
             $this->setName($name);
         }
         
         function getTitle(){
            $this->title = "Welcome";
            return $this->title;
        }
        
        function getContent(){            
                global $CFG, $USER, $userDetails, $fdata;
                require_once($CFG->dirroot.'/calendar/lib.php');
				require_once($CFG->dirroot.'/blocks/moodleblock.class.php');                
                require_once($CFG->dirroot.'/blocks/calendar_month/block_calendar_month.php');
                if ($CFG->mis->tabs['attendance']){require_once($CFG->dirroot.'/blocks/mis/tabs/attendance/lib/attLibs.php');}
                if ($CFG->mis->tabs['timetable']){require_once($CFG->dirroot.'/blocks/mis/tabs/timetable/lib/ttLibs.php');}
                require_once($CFG->dirroot.'/blocks/mis/lib/block.php');
                require_once($CFG->dirroot.'/blocks/mis/tabs/welcome/lib/welcomeLibs.php');
				require_once($CFG->dirroot.'/blocks/mis/tabs/welcome/lib/timelib.php');
                require_once($CFG->dirroot.'/blocks/mis/lib/chart/FusionCharts.php');
				require_once($CFG->dirroot.'/blocks/mis/lib/lib_facility_db.php');
                                
                $absAuthCount = 0;
                $absNotAuthCount = 0;
                $lateCount = 0;                                                                              
                $this->prefix = $CFG->mis->cmisTabPrefix;
				$stuUnId = getStuUpn($this->mdlstuid);
                $stuDetails = getStuDetails($stuUnId);
                
                $content = "<script type=\"text/javascript\">";
                $content .= "var newwindow;";
                $content .= "function poptastic(url){";
                $content .= "var windowWidth = 800;";
                $content .= "var windowHeight = 600;";    
                $content .= "var centerWidth = (window.screen.width - windowWidth) / 2;";
                $content .= "var centerHeight = (window.screen.height - windowHeight) / 2;";
                $content .= "    newwindow=window.open(url,'name','height='+ windowHeight +',width='+ windowWidth +',left=' + centerWidth + ',top=' + centerHeight);";
                $content .= "    if (window.focus) {newwindow.focus()}";
                $content .= "}";
                $content .= "</script>";             
                $content .= html_writer::start_tag('div', array('class'=>'mislogo'));
				$content .= html_writer::end_tag('div');
				$content .= html_writer::start_tag('div', array('class'=>'mis_welcome','id'=>'misMain'));
				$content .= html_writer::start_tag('div', array('id'=>'block_mis_wrapper'));
				$content .= html_writer::start_tag('div', array('class'=>'blockcontainer'));
				$content .= html_writer::start_tag('div', array('class'=>'mis_blockcol','id'=>'blockcol1'));
                $content .= printStuDetails($stuDetails);                        
                $content .= printAttOverview();
				$content .= html_writer::end_tag('div');    
				$content .= html_writer::start_tag('div', array('class'=>'mis_blockcol','id'=>'blockcol2'));
                $content .= printTTOverview();                     
				if ($CFG->mis->tabs['calendar']){                        
					$content .= printcalendarblock($this->mdluser);
				}
                        //$content .= printNotifications();
                        //$content .= printLetters();
                        //$content .= printAssOverview();
                $content .= html_writer::end_tag('div');
				$content .= html_writer::end_tag('div');
				$content .= html_writer::end_tag('div');
				$content .= html_writer::end_tag('div');
				$content .= html_writer::start_tag('div', array('class'=>'clearfix'));
				$content .= html_writer::end_tag('div');
            return $content;
        }
     }
 ?>