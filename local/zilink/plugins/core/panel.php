<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Defines the capabilities for the ZiLink block
 *
 * @package     block_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)).'/core/interfaces.php');

class ZiLinkPanel implements iZiLinkPanel
{
    private $title;
    private $type;
    private $contents;
    private $columns = 2;
    private $width = '40%';
    private $css = 'generaltable';
    
    function __construct(){
    
    }
    
    public function SetType($type)
    {
        $this->type = $type;
    }
    
    public function SetTitle($title)
    {
        $this->title = $title;
    }
    
    public function SetContent($contents)
    {
       $this->contents = $contents;
    }
    
    public function SetWidth($value)
    {
        $this->width = $value;
    }
    
    public function Display()
    {
        $table              = new html_table();
        $table->cellpadding = '5px';    
        $table->width       = $this->width;
        $table->headspan    = array(2);
        $table->head        = array($this->title);
        $table->align       = array('center','center');
        $table->border      = '2px'; 
        //$table->tablealign  = 'left';
        
        $table->attributes['class'] = $this->css;
        
        $table->data = $this->contents;
        return html_writer::table($table);
    }
    
    public function SetColumns($count)
    {
        $this->columns = $count;
    }
    
    public function SetCSS($classes)
    {
        $this->css = $classes;
    }
    
}
