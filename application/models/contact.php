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
     * @type text
     * 
     * @validate required
     * @label category name
     */
    protected $_first_id;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     * @label short form
     */
    protected $_second_id;

    /**
     * @column
     * @readwrite
     * @type boolean
     * 
     * @validate required
     * @label short form
     */
    protected $_first_id_status = false;

    /**
     * @column
     * @readwrite
     * @type boolean
     * 
     * @validate required
     * @label short form
     */
    protected $_second_id_status = false;

}
