<?php

/**
 * @author Shreyansh Goel
 */
namespace models;
class Notification extends \Shared\Model {

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
     * @type mongoid
     * 
     * @validate required
     * @label notification is entitled to
     */
    protected $_user_id;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @label custom notification generated by
     */
    protected $_sender_id;


}
