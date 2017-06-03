<?php
/**
 * Created by PhpStorm.
 * User: shorinmaru
 * Date: 30/07/16
 * Time: 23:23
 */

namespace StarterKit\CrudBundle\Controller;
use StarterKit\CrudBundle\Entity\ResourceInterface;


/**
 * Class Factory
 * @package StarterKit\CrudBundle\Controller
 * This Factory is use to create any Resource
 */
class Factory
{
    /**
     * @param $class_name The required class of your resource
     * @return ResourceInterface
     */

    public static function Create($class_name){
        return new $class_name();

    }

}