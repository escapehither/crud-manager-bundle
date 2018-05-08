<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace EscapeHither\CrudManagerBundle\Services;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The crud event dispatcher
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class CrudEventDispatcher
{
    /**
     * @var RequestParameterHandler
     */
    protected $requestParameterHandler;
    protected $dispatcher;
    /**
     * The Crud event dispatcher constructor
     *
     * @param RequestParameterHandler  $requestParameterHandler
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(RequestParameterHandler $requestParameterHandler, EventDispatcherInterface $dispatcher)
    {
        $this->requestParameterHandler = $requestParameterHandler;
        $this->requestParameterHandler->build();
        $this->dispatcher = $dispatcher;
    }

    /**
     * Dispatch the event
     *
     * @param string $eventName    The event anme
     * @param string $resourceName The resource name
     * @param Event  $event        The event
     *
     */
    public function dispatch($eventName, $resourceName, $event)
    {
        $this->dispatcher->dispatch($eventName, $event);
        $this->dispatcher->dispatch($eventName.'.'.$resourceName, $event);
    }
}
