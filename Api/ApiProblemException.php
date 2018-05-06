<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * API Problem Exception
 * Custom Class exception to handle all Api Exection
 *
 * @author Georden Gaël LOUZAYADIO<georden.@escapehither.com>
 */
class ApiProblemException extends HttpException
{
    /**
     * The api problem
     *
     * @var ApiProblem
     */
    private $apiProblem;

    /**
     * The exception constructor
     *
     * @param ApiProblem $apiProblem The Api problem
     * @param \Exception $previous   The previous exception
     * @param array      $headers    Response Heders
     * @param integer    $code       The exception code
     */
    public function __construct(ApiProblem $apiProblem, \Exception $previous = null, array $headers = array(), $code = 0)
    {
        $this->apiProblem = $apiProblem;
        $statusCode = $apiProblem->getStatusCode();
        $message = $apiProblem->getTitle();
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    /**
     * Get the Api Problem
     *
     * @return ApiProblem
     */
    public function getApiProblem()
    {
        return $this->apiProblem;
    }
}
