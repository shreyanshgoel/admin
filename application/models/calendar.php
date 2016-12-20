<?php

/**
 * The Payment Model
 *
 * @author Shreyansh Goel
 */
namespace models;

class Calendar extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required
     * @label short form
     */
    protected $_user_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required
     * @label category name
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required
     * @label short form
     */
    protected $_start_date;

    /**
     * @column
     * @readwrite
     * @type text
     *
     * @label short form
     */
    protected $_end_date = null;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required
     * @label short form
     */
    protected $_color = null;
}
