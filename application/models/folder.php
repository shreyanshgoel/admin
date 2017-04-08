<?php

/**
 * @author Shreyansh Goel
 */
namespace models;
class Folder extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label file name + extension
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @label project id /null means private 
     */
    protected $_department_id;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @label project id /null means private 
     */
    protected $_parent_folder_id = 'home';

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @label project id /null means private 
     */
    protected $_project_id;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label owner of file
     */
    protected $_user_id;

}
