<?php

/**
 * The State Model
 *
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
     * @label state name
     */
    protected $_note_id;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label state name
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @label state name
     */
    protected $_text;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label country id
     */
    protected $_user_id;

}
