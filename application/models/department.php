<?php

/**
 * The State Model
 *
 * @author Shreyansh Goel
 */
namespace models;
class Department extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label department name
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label department company
     */
    protected $_company_id;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @label department company
     */
    protected $_description;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label head of the department
     */
    protected $_head_id;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label head of the department
     */
    protected $_created_by;
}
