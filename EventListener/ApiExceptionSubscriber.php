<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 10/05/17
 * Time: 22:13
 */

namespace StarterKit\CrudBundle\EventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use StarterKit\CrudBundle\Api\ApiProblemException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use StarterKit\CrudBundle\Api\ApiProblem;
use Symfony\Component\HttpFoundation\Response;

class ApiExceptionSubscriber implements EventSubscriberInterface
{

    /**
     * @var
     */
    private $debug;

    public function __construct($debug){

        $this->debug = $debug;
    }
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // only reply to /api URLs
        if (strpos($event->getRequest()->getPathInfo(), '/api') !== 0) {
            return;
        }

        $e = $event->getException();
        $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
        if ($statusCode == 500 && $this->debug){
            return;
        }

        if ($e instanceof ApiProblemException) {
            $apiProblem = $e->getApiProblem();

        }
        else{

            $apiProblem = new ApiProblem(
              $statusCode
            );
        }
        /*
             * If it's an HttpException message (e.g. for 404, 403),
             * we'll say as a rule that the exception message is safe
             * for the client. Otherwise, it could be some sensitive
             * low-level exception, which should *not* be exposed
             */
        if ($e instanceof HttpExceptionInterface) {
            $apiProblem->set('detail', $e->getMessage());
        }
        // add url to detail errors
        //check for the loader.

        $response = new Response(
          json_encode($apiProblem->toArray(),true),
          $apiProblem->getStatusCode()
        );
        $response->headers->set('Content-Type', 'application/problem+json');
        $event->setResponse($response);

    }
    public static function getSubscribedEvents()
    {
        // TODO: Implement getSubscribedEvents() method.
        return array(
          KernelEvents::EXCEPTION => 'onKernelException'
        );
    }


}