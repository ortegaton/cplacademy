 <?php
 	class tab_attendance extends tab_base{
		
		function init($name){
 			$this->setName($name);
 		}
 		
 		function getTitle(){
			$this->title = "Attendance";
			return $this->title;
		}
		
		function getJs(){
            $js=parent::getJs();            			
			$this->js .="<script type=\"text/javascript\" src=\"js/calendar.js\"></script>\n";						
			return $this->js;
		}
		
		function getContent(){
			
			$content ="<div class=\"mislogo\"></div>"; // GT MOD- don't bother with image, set from CSS (easier to customize image across all tabs). Also, logos are pretty pointless as images for blind users.
			// GT Mod- changed align attribute to a style setting (xhtml compliance)
			$content .="<div style='text-align:center'>\n";
			$content .= html_writer::start_tag('div', array('id'=>'block_mis_wrapper'));
			// GT Mod - Added misMain div as css container
			$content .="<div id='misMain' style='margin:0 auto; width:90%'>\n";

			
			$content .="				<div style=\"width:47%; float:left\" id=\"calendar\"></div>";		
			
			$content .="				<div style=\"width:47%;  float:right\" id=\"graph\"></div>";

            $content .="				<div style=\"clear:both; height:1em;\"></div>";            
            
			$content .="				<div style=\"clear:both; width:100%\" id=\"attTable\"></div>";
			

			$content .="</div>\n";
			$content .= html_writer::end_tag('div');
			$content .="</div>\n";

			$content .="<script type=\"text/javascript\">\n";
			$content .="		monthDraw(\"\",\"\");\n";
			$content .="</script>\n"; 

			return $content;
		}
 	}
 
 ?>
 