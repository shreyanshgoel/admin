<?php

/**
 * The User Model
 *
 * @author Shreyansh Goel
 */
namespace models;
class User extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required
     * @label full name
     */
    protected $_full_name;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required
     * @label mobile number
     */
    protected $_mobile;

    /**
     * @column
     * @readwrite
     * @type boolean
     *
     * @label email address
     */
    protected $_mobile_confirm = false;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @label logo extension
     */
    protected $_logo_ext;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @label mobile number
     */
    protected $_theme_color = 'default';

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * @uindex
     * 
     * @validate required
     * @label email address
     */
    protected $_email;

    /**
     * @column
     * @readwrite
     * @type boolean
     */
    protected $_email_confirm = false;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @label mobile number
     */
    protected $_email_confirm_string;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label password
     */
    protected $_password;

    /**
    * @column
    * @readwrite
    * @type boolean
    */
    protected $_admin = false;

}
