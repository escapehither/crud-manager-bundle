<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 21/01/17
 * Time: 22:06
 */

namespace EscapeHither\CrudManagerBundle\Services;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CrudEventDispatcher {
    /**
     * @var RequestParameterHandler
     */
    protected $requestParameterHandler;
    protected $dispatcher;
    function __construct(RequestParameterHandler $requestParameterHandler,EventDispatcherInterface $dispatcher)
    {
        $this->requestParameterHandler = $requestParameterHandler;
        $this->requestParameterHandler->build();
        $this->dispatcher= $dispatcher;


    }

    public function dispatch($eventName,$resourceName,$event){
        $this->dispatcher->dispatch($eventName, $event);
        $this->dispatcher->dispatch($eventName.'.'.$resourceName, $event);

    }
}