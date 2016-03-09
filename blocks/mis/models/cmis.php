<?php
/**
 * cmis data model
 * recovers data from facility cmis database
 * @author gthomas
 *
 */
class dm_cmis extends dm_local {
    
    /**
     * $provideslive - this model is capable of providing live data
     * @var boolean
     */
    protected $provideslive=true;    
    
    /**
     * $livedata - if true then use methods to recover live data
     * @var boolean
     */
    protected $livedata=false;    
    
    
    /**
     * constructor
     * @return void
     */
    function __construct($livedata=false){
        parent::__construct($livedata);       
    }
    
    /**
     * factory
     * @return void
     */
    function factory(){
        return new dm_cmis();
    }
    
    /**
     * get school
     * @return object
     */
    function get_school(){
        return (new dm_cmis_school());
    }
}

/**
 * school data model
 * recovers data from data wharehouse
 * @author gthomas
 *
 */
class dm_cmis_school extends dm_local_school {

    /**
     * $provideslive - this model is capable of providing live data
     * @var boolean
     */
    protected $provideslive=true;    
    
    /**
     * constructor
     * @return void
     */
    function __construct($livedata){
        parent::__construct($livedata);
        $this->set_data();
    }
    
    /**     
     * set class properties according to data
     * @return void
     */
    protected function set_data(){
        if ($this->livedata){
            return($this->set_data_live()); 
        }
        $this->set_school_details();
    }
    
    protected function set_data_live(){
    }
    
    /**
     * set school details
     * @return void
     */
    protected function set_school_details(){

    }
    
    protected function set_school_details_live(){
        
    }
    
    
}

?>