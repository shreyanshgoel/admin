<?php

/**
 * @author Shreyansh Goel
 */
namespace models;
class Task extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label task title
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @label task title
     */
    protected $_details;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @label normal/urgent
     */
    protected $_type;

    /**
     * @column
     * @readwrite
     * @type datetime
     * 
     * @label task title
     */
    protected $_due_date;

    /**
     * @column
     * @readwrite
     * @type array
     * 
     * @validate required
     * @label project id
     */
    protected $_project_id;
}
