<?php

/**
 * The State Model
 *
 * @author Shreyansh Goel
 */
namespace models;
class Member extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label admin/Manager/employee
     */
    protected $_designation;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label company id
     */
    protected $_company_id;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label member id
     */
    protected $_user_id;

}
    