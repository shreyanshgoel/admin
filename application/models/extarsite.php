<?php

/**
 * @author Shreyansh Goel
 */
namespace models;
class ExtraSite extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label department name
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label department company
     */
    protected $_url;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label head of the department
     */
    protected $_user_id;
}
