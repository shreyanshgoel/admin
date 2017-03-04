<?php

/**
 * @author Shreyansh Goel
 */
namespace models;
class Project extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label project name
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type array
     * 
     * @validate required
     * @label team of the project excluding who made it
     */
    protected $_team = [];

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label head of the project
     */
    protected $_head;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @label details of the project
     */
    protected $_details;

    /**
     * @column
     * @readwrite
     * @type datetime
     * 
     * @label deadline
     */
    protected $_due_date;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label state name
     */
    protected $_department_id;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label project made by
     */
    protected $_created_by;

    /**
     * @column
     * @readwrite
     * @type array
     * 
     * @validate required
     * @label normal/inprogress or urgent or completed
     */
    protected $_status = [];

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label normal/inprogress or urgent or completed
     */
    protected $_progress = 0;

}
