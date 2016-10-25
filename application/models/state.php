<?php

/**
 * The State Model
 *
 * @author Shreyansh Goel
 */
namespace models;
class State extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required
     * @label state name
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type text
     *
     * @label country id
     */
    protected $_country_id = '';

}
