<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class zilink_icon_navigation_config_form extends moodleform {
    
    function definition() {

        global $CFG,$DB;
 
        $mform =& $this->_form;
        
        if (isset($this->_customdata['icon_navigation_iconset'])) {
            $icon_navigation_iconset = $this->_customdata['icon_navigation_iconset'];
        } else {
            $icon_navigation_iconset = 'default';
        }
        
        if (isset($this->_customdata['icon_navigation_size'])) {
            $icon_navigation_size = $this->_customdata['icon_navigation_size'];
        } else {
            $icon_navigation_size = 50;
        } 
        
        $list = array();
        $list['default'] = 'deafult';
        
        $path = $CFG->dirroot.'/blocks/zilink/pix/icon_navigation/';
        
        if(file_exists($path))
        {
    
            $ignore = array( '.', '..');
            $dh = @opendir( $path );
            $default = array();
            
            while( false !== ( $file = readdir( $dh ) ) )
            {
                if( !in_array( $file, $ignore ) )
                {
                        if(is_dir( "$path/$file" ) )
                        {
                            $default[$file] = $file;
                        }
                }
            }
            $list = array_merge($list,$default);
            
            $select = $mform->addElement('select', 'icon_navigation_iconset', get_string('icon_navigation_iconset', 'local_zilink'),$list);
            $select->setSelected($icon_navigation_iconset);
        }
        
        $list = array();
        $list[50] = get_string('icon_navigation_size_small','block_zilink');
        $list[70] = get_string('icon_navigation_size_large','block_zilink');
        $list[100] = get_string('icon_navigation_size_xlarge','block_zilink');
        
        $select = $mform->addElement('select', 'icon_navigation_size', get_string('icon_navigation_size', 'local_zilink'),$list);
        $select->setSelected($icon_navigation_size);
        
        $list = array();
        $default = array();
        $custom = array();
        
        $list[0] = 'None';
        
        $path = $CFG->dirroot.'/blocks/zilink/pix/icon_navigation/'.$CFG->zilink_icon_navigation_iconset.'/subjects';
        
        if(file_exists($path))
        {
        
            $ignore = array( '.', '..','core');
            $dh = @opendir( $path );
            
            while( false !== ( $file = readdir( $dh ) ) )
            {
                if( !in_array( $file, $ignore ) )
                {
                        if(!is_dir( "$path/$file" ) )
                        {
                            $name = explode('.',$file);
                            $default[$name[0]] = $name[0];
                        }
                }
            }
            closedir( $dh );
        }
        ksort($default);
        
        $categories = $DB->get_records('course_categories',array('parent' => $CFG->zilink_category_root),'name ASC');

        $list = array_merge($list,$default);
        
        foreach ($categories as $index => $category)
        {
            if(!isset($this->_customdata['icon_navigation_category_icon_'.$category->id]))
            {
                $this->_customdata['icon_navigation_category_icon_'.$category->id] = 0;
            }
            $select = $mform->addElement('select', 'icon_navigation_category_icon_'.$category->id, $category->name,$list);
            $select->setSelected($this->_customdata['icon_navigation_category_icon_'.$category->id]);
            
        }
        
        $this->add_action_buttons(false, get_string('savechanges'));
        /*
        $mform->addElement('html','</td></tr><tr><td colspan="1" style="text-align:center; border: 0; ">');
        $mform->addElement('html','<input name="submitbutton" value="Save changes" type="submit" id="id_submitbutton">');
        $mform->addElement('html','</td></tr>');
        $mform->addElement('html','</tbody></table>');
        $mform->addElement('html','<div><fieldset>');
         * 
         */
    }
    
    function Display()
    {
        return $this->_form->toHtml();
    }
    
}