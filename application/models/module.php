<?php

/**
 * @author Shreyansh Goel
 */
namespace models;
class Module extends \Shared\Model {

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
