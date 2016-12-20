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
     * @label state name
     */
    protected $_members[];

}
