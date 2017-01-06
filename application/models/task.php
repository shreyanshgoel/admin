<?php

/**
 * The State Model
 *
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
     * @validate required
     * @label project id
     */
    protected $_project_id;
}
