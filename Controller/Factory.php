<?php
/**
 * Created by PhpStorm.
 * User: shorinmaru
 * Date: 30/07/16
 * Time: 23:23
 */

namespace EscapeHither\CrudManagerBundle\Controller;
use EscapeHither\CrudManagerBundle\Entity\ResourceInterface;


/**
 * Class Factory
 * @package EscapeHither\CrudManagerBundle\Controller
 * This Factory is use to create any Resource
 */
class Factory
{
    /**
     * @param $class_name
     *   the required class of your resource
     * @return ResourceInterface
     */

    public static function Create($class_name){
        return new $class_name();

    }

}