<?php

//
// Author: Guy Thomas
// Date:2007 - 02 - 20
// Purpose: This class is a base class - should be extended by other classes
//          Any class that extends this class can set all parameters via an array
//          instead of having to pass each parameter in (useful for optional parameters)
//
class propsbyparam{


	//
	// Class Constructor
	//
	function propsbyparam($params){

		if (empty($params) || !is_array($params)){
			return;
		}
                
					
		// attach params to class properties (will not attach to vars prefixed with _)
		$className=get_class($this);
		foreach ($params as $param=>$val){
			//if (property_exists($className, $param)){
			if (property_exists($this, $param)){
				if (substr($param, 0, 1)!="_"){
					$this->$param=$val;
				}
			}
		}
	}
		


}
?>