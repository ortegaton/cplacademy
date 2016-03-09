<?php
	
    function drawTimeline(){
		$year = 10;

		$timeline = "<table border=\"0\" width=\"90%\" cellspacing=\"0\" cellpadding=\"0\" height=\"71\">";
		$timeline .= "  <tr>";
		$timeline .= "    <td width=\"60%\" align=\"center\" colspan=\"3\" height=\"20\"  style=\"border-left-style: solid; border-left-width: 1; border-right-style: solid; border-right-width: 1\"><b><font face=\"Verdana\" color=\"#000000\" size=\"1\">Key Stage 3</font></b></td>";
		$timeline .= "    <td width=\"40%\" align=\"center\" colspan=\"2\" height=\"20\"  style=\"border-left-style: solid; border-left-width: 1; border-right-style: solid; border-right-width: 1\"><b><font face=\"Verdana\" color=\"#000000\" size=\"1\">Key Stage 4</font></b></td>";
		$timeline .= "  </tr>";
		$timeline .= "	<tr>";
		for ($i = 7; $i <= 11; $i++ ){
			if($i <= $year){
				$class = "yearComplete";
			}else{
				$class = "yearIncomplete";
			}

			$timeline .= "    <td  class=\"" . $class . "\" >Year " .  $i . "</td>";
		}
		$timeline .= "  </tr>";
		$timeline .= "  <tr>";
		$timeline .= "    <td width=\"20%\" align=\"center\" height=\"15\">";
		$timeline .= "      <p align=\"left\"><font face=\"Verdana\" size=\"1\"><b> <img border=\"0\" src=\"pix/pointer.gif\" width=\"10\" height=\"10\"></b></font><b><font face=\"Verdana\" size=\"1\">KS2 results</font></b></td>";
		$timeline .= "    <td width=\"20%\" align=\"center\" height=\"15\">";
		$timeline .= "    </td>";
		$timeline .= "    <td width=\"20%\" align=\"center\" height=\"15\">";
		$timeline .= "      <p align=\"right\"><font face=\"Verdana\" size=\"1\"><b>SAT's <img border=\"0\" src=\"pix/pointer.gif\" width=\"10\" height=\"10\">&nbsp;";
		$timeline .= "      </b></font></td>";
		$timeline .= "    <td width=\"20%\" align=\"center\" height=\"15\">";
		$timeline .= "      <p align=\"right\"><font face=\"Verdana\" size=\"1\"><b>Year 10 Exams <img border=\"0\" src=\"pix/pointer.gif\" width=\"10\" height=\"10\">";
		$timeline .= "      </b></font></td>";
		$timeline .= "    <td width=\"10%\" align=\"center\" height=\"15\">";
		$timeline .= "      <p align=\"right\"><font face=\"Verdana\" size=\"1\"><b>GCSE Mock <img border=\"0\" src=\"pix/pointer.gif\" width=\"10\" height=\"10\">";
		$timeline .= "      </b></font></td>";
		$timeline .= "    <td width=\"10%\" align=\"center\" height=\"15\">";
		$timeline .= "      <p align=\"right\"><font face=\"Verdana\" size=\"1\"><b>GCSE</b></font><font face=\"Verdana\" size=\"1\"><b>";
		$timeline .= "      <img border=\"0\" src=\"pix/pointer.gif\" width=\"10\" height=\"10\">";
		$timeline .= "      </b></font></td>";
		$timeline .= "  </tr>";
		$timeline .= "</table><br><br>";
		return $timeline;
	}
?>