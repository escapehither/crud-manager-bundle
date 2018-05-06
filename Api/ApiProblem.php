<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Api;

use Symfony\Component\HttpFoundation\Response;

/**
 * The Api problem
 * A wrapper for holding data to be used for a application/problem+json response
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
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

    /**
     * Api problem constructor
     *
     * @param int    $statusCode The status code
     * @param string $type       The problem type
     */
    public function __construct($statusCode, $type = null)
    {
        if (null === type) {
            $type = 'about:blank';
            $title = isset(Response::$statusTexts[$statusCode])
              ? Response::$statusTexts[$statusCode]
              : 'Unknown status code :(';
        } else {
            if (!isset(self::$titles[$type])) {
                throw new \InvalidArgumentException(sprintf('No title for type %s ', $type));
            }
            $title = self::$titles[$type];
        }
        $this->statusCode = $statusCode;
        $this->type = $type;
        $this->title = $title;
    }

    /**
     * Add information to The error Array
     *
     * @param string $name  The name of the extra data
     * @param mixed  $value The value of the extra data
     */
    public function set($name, $value)
    {
        $this->extraData[$name] = $value;
    }

    /**
     * A problem to extra data
     *
     * @return array
     */
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
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
