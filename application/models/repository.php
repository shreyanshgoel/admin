<?php

/**
 * @author Shreyansh Goel
 */
namespace models;
class Repository extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label country name
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label country name
     */
    protected $_bare_name;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label country name
     */
    protected $_key;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @label country name
     */
    protected $_clone_name;

    /**
     * @column
     * @readwrite
     * @type integer
     * 
     * @validate required
     * @label 1=company, 2=personal
     */
    protected $_type;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label country name
     */
    protected $_user_id;

}
