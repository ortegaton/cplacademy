<?php

/**
 * =================================================
 * This model is abstract
 * It is the master template for models
 * =================================================
 */



/**
 * master school data model
 * @author gthomas
 *
 */
abstract class dm_school extends dmcomp {
    
    /**
     * school id - e.g. 384/4023
     * @var string
     */
    var $idstr;
    
    /**
     * name of school
     * @var string
     */
    var $name;
    
    /**
     * address excluding city / county / country / postcode
     * @var string
     */
    var $address;
    
    /**
     * city
     * @var string
     */
    var $city;
    
    /**
     * county
     * @var string
     */
    var $county;
    
    /**
     * 3 digit country code - http://en.wikipedia.org/wiki/ISO_3166-1_alpha-3
     * e.g. GBR
     * @var string
     */
    var $country;
    
    /**
     * zip / postcode
     * @var string
     */
    var $postcode;
    
    /**
     * $accyear - current academic year
     * object ($accyear->name, $accyear->range)
     * @var object
     */
    var $accyear;
    
    /**
     * array of academic years listed in order of most current
     * @var array
     */
    var $accyears;
    
    /**
     * array of course objects
     */
    var $courses;
    
}
?>