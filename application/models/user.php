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
     * @type array
     * 
     * @validate required
     * @label key is company id and points to permission (1=Admin, 2=Manager, 3=user)
     */
    protected $_permissions = [];

    /**
     * @column
     * @readwrite
     * @type array
     * 
     * @label key is company id and points to array of designation
     */
    protected $_designations = [];

    /**
     * @column
     * @readwrite
     * @type array
     * 
     * @label company id
     */
    protected $_company_ids = [];

    /**
    * @column
    * @readwrite
    * @type boolean
    */
    protected $_admin = false;

}
