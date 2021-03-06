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

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label country name
     */
    protected $_company_id;

}
