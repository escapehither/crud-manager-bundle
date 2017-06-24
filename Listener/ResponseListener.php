<?php

/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 24/06/17
 * Time: 13:55
 */
namespace EscapeHither\CrudManagerBundle\Listener;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
class ResponseListener {
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $event->getResponse()->headers->set('x-frame-options', 'deny');
    }

}