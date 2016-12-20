<?php

/**
 * The State Model
 *
 * @author Shreyansh Goel
 */
namespace models;
class Company extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required
     * @label state name
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @label admin/Manager/employee
     */
    protected $_user_id;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @label mobile number
     */
    protected $_location;

}
