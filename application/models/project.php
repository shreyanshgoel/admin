<?php

/**
 * The State Model
 *
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
     * @type text
     * 
     * @validate required
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
    protected $_deadline;

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
     * @type text
     * 
     * @validate required
     * @label project made by
     */
    protected $_user_id;
    
    /**
     * @column
     * @readwrite
     * @type array
     * 
     * @label team of the project excluding who made it
     */
    protected $_members[];

}
