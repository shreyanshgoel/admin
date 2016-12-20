<?php

/**
 * The Contact Model
 *
 * @author Shreyansh Goel
 */
namespace models;
class Contact extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label contact added by
     */
    protected $_first_id;

    /**
     * @column
     * @readwrite
     * @type mongoid
     * 
     * @validate required
     * @label this contact is requested
     */
    protected $_second_id;

    /**
     * @column
     * @readwrite
     * @type boolean
     * 
     * @validate required
     * @label acceptance of first contact
     */
    protected $_first_id_status = true;

    /**
     * @column
     * @readwrite
     * @type boolean
     * 
     * @label acceptance of second contact
     */
    protected $_second_id_status = false;

}
