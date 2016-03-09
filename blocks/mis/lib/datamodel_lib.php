<?php

/**
 * Error constant for model / component object failing on instantiation 
 */
define('DMOD_ERR_OBJ_INST', 1000);

abstract class dmodel{
    
    /**
     * config object
     * @var object
     */
    var $cfg;
    
    /**
     * array of errors related to this model
     * @var array
     */
    var $errors;    
    
    /**
     * $provideslive - can this model provide live data
     * @var boolean
     */
    protected $provideslive=false;    
    
    /**
     * $livedata - if true then use methods to recover live data
     * @var boolean
     */
    protected $livedata=false;
    
    /**
     * constructor
     * set livedata property
     * @param $livedata
     * @return void
     */
    function __construct($livedata=false){
        global $CFG;
        $this->cfg=$CFG->mis;
        $this->livedata=$livedata;        
    }    
    
    private function _log_error($type, $objmethod, $message){
        $this->errors[]=(object) array(
            'type'=>$type,
            'objmethod'=>$objmethod,
            'message'=>$message
        );        
    }
}

/**
 * data model component base class
 * all data model components must extend eiter this class or a
 * class that is based on it at its inheritance root
 * @author gthomas
 *
 */
abstract class dmcomp extends dmodel{    
    
    /**
     * all model components based on this class must have a set_data function
     * @return void
     */
    abstract protected function set_data ();
}

/**
 * data model controller
 * loads and instantiates model
 * @author gthomas
 *
 */
class model {
    
    /**
     * model name
     * @var string
     */
    protected $modelname;
    
    /**     
     * model object
     * @var object
     */
    protected $model;
    
    /**
     * 
     * @param $modname
     * @return void
     */
    function __construct($livedata=null, $modelname=null){
        
        // set model
        if ($modelname===null){
            if (!isset($CFG->mis->model_default)){
                $modelname='local';
            }
        }
        $this->modelname=$modelname;
        $modelclass='dm_'.$modelname;
        
        // load master model file
        include_once($CFG->dirroot.'/blocks/mis/models/master.php');
        
        // load model file
        @include_once($CFG->dirroot.'/blocks/mis/models/'.$modelname.'.php');        
        if (!class_exists($modelclass, false)) {
            trigger_error('Unable to load class: '.$modelclass, E_USER_WARNING);
        }

        // if live data parameter has been passed in then use it 
        if ($livedata!==null){
            $this->livedata=$livedata;
            // instantiate and set model
            $this->model=new $modelclass($livedata);            
        } else {
            // instantiate and set model
            $this->model=new $modelclass();
            // use models default live data setting
            $this->livedata=$model->livedata;
        }
    }
}



?>