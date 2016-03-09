 <?php
 	class tab_timetable extends tab_base{
		
		function init($name,$db){
			global $fdata;
 			$this->setName($name);
 			$this->db = $fdata;
 		}
 		
 		function getTitle(){
			$this->title = "Timetable";
			return $this->title;
		}
		
		function getContent(){
			require_once('tabs/timetable/lib/ttLibs.php');
			global $CFG, $userDetails;
            $this->content.=html_writer::start_tag('div', array('id'=>'block_mis_wrapper'));
			if (isset($userDetails->description)){
				$studentid = $this->db->getStuAdminNo($userDetails->idnumber);
			   
				// GT Mod - get the number of week definitions for the current month
				// This is now dynamic and should work for any school
				// I.e. If a school has 2 week definitions it will show 2 timetables, if they have 1 week definition it will show 1 timetable!
				// It can cope with up to 4 week definitions (obviously)
				$wdefs=num_week_defs();
				
				if ($wdefs){
					
					$this->content.='<div class="timetables">';
					for ($t=1; $t<=$wdefs; $t++){
						$tt=drawWeeksTimetable($studentid,$t,0);
						$block = new Block(get_string('ttTable'.$t,'block_mis') ,"ttTable".$t,"",$tt);
						$this->content .=$block->draw();
					}
					$this->content.='</div>';
				} else {
					$this->content .='<div class="error">No timetable data for this academic year ('.$CFG->mis->cmisDataSet.')!</div>';
				}
			} else {
				$this->content .='<div class="error">Staff do not have timetables within this system</div>';
			}
			$this->content.= html_writer::end_tag('div');
            return $this->content;
		}

        function getJs(){
            global $CFG;
            $this->js=parent::getJs();
            $this->js .="<script type=\"text/javascript\" src=\"".$CFG->wwwroot."/lib/gtlib_yui/lib.gt.ajax.js\"></script>\n";   
    		$this->js .="<script type=\"text/javascript\" src=\"".$this->blockwww."/js/timetable.js\"></script>\n";	
            return ($this->js);
        }

 	}
 
 ?>
 