<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 10/05/17
 * Time: 20:10
 */

namespace EscapeHither\CrudManagerBundle\Api;
/**
 * A wrapper for holding data to be used for a application/problem+json response
 */
use Symfony\Component\HttpFoundation\Response;
class ApiProblem
{
    const TYPE_VALIDATION_ERROR = 'validation_error';
    const TYPE_INVALID_REQUEST_BODY_FORMAT = 'invalid_body_format';
    private static $titles = array(
      self::TYPE_VALIDATION_ERROR => 'There was a validation error',
      self::TYPE_INVALID_REQUEST_BODY_FORMAT => 'Invalid JSON format sent',
    );
    private $statusCode;
    private $type;
    private $title;
    private $extraData = array();
    public function __construct($statusCode, $type = null)
    {
        if ($type === null) {
            $type = 'about:blank';
            $title = isset(Response::$statusTexts[$statusCode])
              ? Response::$statusTexts[$statusCode]
              : 'Unknown status code :(';
        }
        else{
            if (!isset(self::$titles[$type])) {
            throw new \InvalidArgumentException('No title for type '.$type);
            }
            $title = self::$titles[$type];
        }
        $this->statusCode = $statusCode;
        $this->type = $type;
        $this->title = $title;
    }
    public function set($name, $value)
    {
        $this->extraData[$name] = $value;
    }
    public function toArray()
    {
        return array_merge(
          $this->extraData,
          array(
            'status' => $this->statusCode,
            'type' => $this->type,
            'title' => $this->title,
          )
        );
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getExtraData()
    {
        return $this->extraData;
    }


}