<?php

/**
 * @author Shreyansh Goel
 */
namespace models;
class Chat extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label state name
     */
    protected $_message;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label state name
     */
    protected $_from_id;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label state name
     */
    protected $_to_id;



}
