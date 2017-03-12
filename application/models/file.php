<?php

/**
 * @author Shreyansh Goel
 */
namespace models;
class File extends \Shared\Model {

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
     * @type text
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
