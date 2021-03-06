<?php

/**
 * @author Shreyansh Goel
 */
namespace models;
class Company extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * @length 30
     * 
     * @validate required
     * @label state name
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label company founder
     */
    protected $_founder_id;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @label company address
     */
    protected $_git_folder_name;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @label company address
     */
    protected $_location;

}
