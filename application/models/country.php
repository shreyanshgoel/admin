<?php

/**
 * The State Model
 *
 * @author Shreyansh Goel
 */
namespace models;
class Country extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label country name
     */
    protected $_name;

}
