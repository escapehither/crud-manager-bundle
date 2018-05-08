<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace EscapeHither\CrudManagerBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use EscapeHither\CrudManagerBundle\Api\ApiProblemException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use EscapeHither\CrudManagerBundle\Api\ApiProblem;
use Symfony\Component\HttpFoundation\Response;

/**
 * Transform All api Exception Headers in Json
 *
 * Api Exception Subscriber
 *
 *@author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class ApiExceptionSubscriber implements EventSubscriberInterface
{

    /**
     * @var
     */
    private $debug;

    /**
     * Event Subscriber Constructor
     *
     * @param [type] $debug The kernel debug
     */
    public function __construct($debug)
    {

        $this->debug = $debug;
    }

    /**
     * {@inheritDoc}
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // only reply to /api URLs
        if (strpos($event->getRequest()->getPathInfo(), '/api') !== 0) {
            return;
        }

        $e = $event->getException();
        $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

        //Symfony's normal exception handling in case of a 500 with debug mode activate
        // TODO add the logger
        if (500 === $statusCode && $this->debug) {
            return;
        }

        if ($e instanceof ApiProblemException) {
            $apiProblem = $e->getApiProblem();
        } else {
            $apiProblem = new ApiProblem(
                $statusCode
            );
        }

        if ($e instanceof HttpExceptionInterface) {
            $apiProblem->set('detail', $e->getMessage());
        }

        // add url to detail errors
        //check for the loader.
        $response = new Response(
            json_encode($apiProblem->toArray(), true),
            $apiProblem->getStatusCode()
        );
        $response->headers->set('Content-Type', 'application/problem+json');
        $event->setResponse($response);
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
          KernelEvents::EXCEPTION => 'onKernelException',
        );
    }
}
