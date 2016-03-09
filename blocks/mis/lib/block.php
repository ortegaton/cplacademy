<?php
/**
* (c) Alan Hardy - Frederick Gent School 2008
* 
* Licence - GNU GENERAL PUBLIC LICENSE - Version 3, 29 June 2007
*           Refer to http://www.gnu.org/licenses/gpl.html for full terms
*
* Version - Alpha 
*
* Date    - 03-03-2008
*
* Project - MIS - Facility to Moodle integration
*
**/
class Block
{
	var $Block;
	var $title;
	var $name;
	var $content;
	// The constructor function
	function Block($title,$name,$collapse,$content)
	{

		$this->Block = '';
		$this->title = $title;
		$this->name = $name;
		$this->collapse = $collapse;
		$this->content =$content;

	}

	function draw()
	{
		global $CFG;
		// GT Mod- simplified this. Less mark up
        // GT Mod - 02/07/2008 - changed block title class to 'header' - we should probably use the same theme elements that are used throughout our moodle install
		$blockdata='';
		$blockdata .= "<div class=\"mis_block_wrapper\" id=\"mis_" . $this->name ."\">\n";
        $blockdata .= "\t<div class=\"mis_block\">\n";
        $blockdata .= "\t\t<div class=\"header\" id=\"mis_" . $this->name ."_title\">" . $this->title;
		if (file_exists($CFG->dirroot . "/blocks/mis/help/" . $this->name . ".htm")){
			// get ssl pix path if necessary for this block (otherwise IE complains about non-secure items!)
			//$pixpath=$CFG->mis->blockroot.'pix/';
			if ($CFG->mis->https){
				//$pixpath=str_ireplace('http://','https://',$pixpath);
			}
			$blockdata .= "<div id=\"" . $this->name . "_help_icon\" class=\"help_icon\"><a href=\"#\" onclick=\"javascript:helpDraw('".  $this->name . "'); return false;\" title=\"Click here for Help.\"><img src=\"pix/help.gif\"></a></div>\n";
		}
        $blockdata .= "</div>\n";
		// GT Mod The id for the div was causing problems when paging through months (suspect the id was already used somewhere). Symptoms were, title for block was not removed when viewing next or previous month. Solution - prefixing block id with misblock_
		$blockdata .= "\t\t".'<div id="misblock_' . $this->name . '" >'."\n";
		$blockdata .= $this->content;
		$blockdata .= "\t\t".'</div>'."\n";
        $blockdata .= "\t".'</div>'."\n";
        $blockdata .= '</div>'."\n";
		
		return $blockdata;
	}

}
?>