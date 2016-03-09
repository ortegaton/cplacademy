<?php
/**
 * local data wharehouse model
 * recovers data from data wharehouse
 * Does not recover live data - uses separate model to get live data for
 * wharehouse when model component timestamp expires
 * @author gthomas
 *
 */
class dm_local extends dmodel {
    
    var $school;
    
    /**
     * constructor
     * @param boolean $livedata - still have need this so that sub classes can set livedata property
     * @return void
     */
    function __construct($livedata=false){
        parent::__construct($livedata);
        $this->school=$this->get_school();        
    }
    
    /**
     * factory
     * @return void
     */
    function factory(){
        return new dm_local();
    }
    
    /**
     * get school
     * @return object
     */
    function get_school(){
        return (new dm_local_school());
    }
}



/**
 * school data model
 * recovers data from data wharehouse
 * @author gthomas
 *
 */
class dm_local_school extends dm_school {
    
    /**
     * constructor
     * @return void
     */
    function __construct($livedata=false){
        parent::__construct($livedata);
        $this->set_data();
    }
    
    /**     
     * set class properties according to data
     * @return void
     */
    protected function set_data(){
        $this->set_school_details();
    }
    
    /**
     * set school details
     * @return void
     */
    protected function set_school_details(){
		global $DB;
        $school=$DB->get_record('mis_m_schools',array('masterorg'=>1));
        if (!$school){
            // Log error - master organisation not found
            $this->_log_error(
                DMOD_ERR_OBJ_INST,
                'local_school->set_school_details',
                'error setting school details' //@todo lang str 
            );
        } else {
            // set class properties from school row fields
            foreach ($school as $key=>$val){
                if (substr($key,0,1!='_') && substr($key,0,7)!='addline'){
                    $this->$key=$val;
                }
            }
            // set address class property from each address line
            $address=array(
                $school->addline1,
                $school->addline2,
                $school->addline3,
                $school->addline4,
                $school->addline5
            );
            $this->address='';
            foreach ($address as $line){
                if ($line!=''){
                    $this->address.=$this->address!='' ? chr(10).chr(13) : '';
                    $this->address.=$line;
                }
            }
        }
    }
}

class dm_local_course extends dmcomp {
    
    /**
     * Is this course actually used by class groups / teaching groups
     * @var boolean
     */
    var $used;
    
    /**
     * Array of year groups applicable to this course
     * @var array
     */
    var $years;
    
    function __construct(){
        parent::__construct();
        $this->set_data();
    }
    
    /**     
     * set class properties according to data
     * @return void
     */    
    protected function set_data(){
        
    }
}
?>