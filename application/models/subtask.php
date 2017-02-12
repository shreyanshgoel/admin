<?php

/**
 * @author Shreyansh Goel
 */
namespace models;
class Subtask extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label subtask title
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label country id
     */
    protected $_task_id;

}
