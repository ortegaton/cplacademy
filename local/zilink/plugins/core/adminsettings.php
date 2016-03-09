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
 * @package     local_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot.'/lib/adminlib.php');
class  admin_setting_configmulticheckbox_zilink extends admin_setting_configmulticheckbox {
    
    public function output_html($data, $query ='')
    {
        if (!$this->load_choices() or empty($this->choices)) {
              return '';
        }
        
        $default = $this->get_defaultsetting();
        if (is_null($default)) {
            $default = array();
        }
        if (is_null($data)) {
            $data = array();
        }
        $options = array();
        $defaults = array();
        foreach ($this->choices as $key=>$description) {
            if (!empty($data[$key])) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }
            if (!empty($default[$key])) {
                 $defaults[] = $description;
            }
      
            $options[] = '<input type="checkbox" id="'.$this->get_id().'_'.$key.'" name="'.$this->get_full_name().'['.$key.']" value="1" '.$checked.' />'
                         .'<label style="margin-left:5px;" for="'.$this->get_id().'_'.$key.'">'.highlightfast($query, $description).'</label>';
        }
  
        if (is_null($default)) {
            $defaultinfo = NULL;
        } else if (!empty($defaults)) {
            $defaultinfo = implode(', ', $defaults);
        } else {
            $defaultinfo = get_string('none');
        }
  
        $return = '<div style="display:table;border-collapse:collapse;table-layout:fixed;width:80%;padding:0;margin-left:0%;margin-right:10%;border:1px solid;border-color:#CCCCCC;">';
        //$return = '<div class="form-multicheckbox">';
        $return .= '<input type="hidden" name="'.$this->get_full_name().'[]" value="1" />'; // something must be submitted even if nothing selected
        if ($options) {
            $return .= '<div style="display:table-row; margin:0px;">';
            $count = 0;
            foreach ($options as $option) {
                if($count == 5)
                {
                    $return .= '</div><div style="display:table-row;">';
                    $count = 0;
                }       
                $return .= '<div style="display:table-cell;padding: 5px;border: 1px solid #CCCCCC;width: 3em;text-align:center;">'.$option.'</div>';
                $count++;
                
            }
            $return .= '</div>';
        }
        $return .= '</div>';

        return format_admin_setting($this, $this->visiblename, $return, $this->description, false, '', $defaultinfo, $query);
    }
}

class  admin_setting_configselect_withoptiongroup extends admin_setting_configselect {
    
    public function output_select_html($data, $current, $default, $extraname = '') {
          if (!$this->load_choices() or empty($this->choices)) {
              return array('', '');
          }
  
          $warning = '';
          if (is_null($current)) {
          // first run
          } else if (empty($current) and (array_key_exists('', $this->choices) or array_key_exists(0, $this->choices))) {
              // no warning
          } else {
                  
                $match = false;
                foreach ($this->choices as $key=>$value) {
                    if (is_array($value)) {
                        if(array_key_exists($current, $value)) {
                            $match = true;
                        }
                    }
                    else {
                        if($current==$value)
                           $match = true;
                        elseif($current==$key)
                            $match = true;
                    }
                }
                
                if(!$match) {
                    $warning = get_string('warningcurrentsetting', 'admin', s($current));
                    //if (!is_null($default) and $data == $current) {
                    //    $data = $default; // use default instead of first value when showing the form
                    //}
                }
          }
  
          $selecthtml = '<select id="'.$this->get_id().'" name="'.$this->get_full_name().$extraname.'">';
          foreach ($this->choices as $key => $value) {
              if(is_array($value))
              {
                  $selecthtml .= '<optgroup label="'.$key.'"></optgroup>';
                  
                  foreach ($value as $subkey => $subvalue) {
                      //echo $subkey .' - ' .$data .' '.$current.'<br>';
                      $selecthtml .= '<option value="'.$subkey.'"'.((string)$subkey==$current ? ' selected="selected"' : '').'>'.$subvalue.'</option>';
                  }
                  
                  //$selecthtml .= '</optgroup>';
                  
              }
              else
                    $selecthtml .= '<option value="'.$key.'"'.((string)$key==$current ? ' selected="selected"' : '').'>'.$value.'</option>';
          }
          
          $selecthtml .= '</select>';
          
          $newchoices = array();
          foreach($this->choices as $key => $choice) {
              
              if(is_array($choice))
              {
                  foreach($choice as $subkey => $value)
                  {
                      $newchoices[$subkey] = $value;
                  }
              }
              else {
                   $newchoices[$key] = $choice;
              }
          }
          
          $this->choices = $newchoices; 
          
          return array($selecthtml, $warning);
      }
}

