<?php

/**
 * @author Shreyansh Goel
 */
namespace models;
class Comment extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label comment text
     */
    protected $_text;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label commented by
     */
    protected $_user_id;

}
