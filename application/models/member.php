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
     * @label admin/Manager/employee
     */
    protected $_designation;

}
    