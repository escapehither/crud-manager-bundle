<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use EscapeHither\CrudManagerBundle\Entity\Resource;

/**
 * The Resource.create event is dispatched each time a new resource is created
 * in the system.
 *
 *@author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class ResourceEvent extends Event
{
    const LOAD_CREATE_RESOURCE = 'resource.load.create';
    const PRE_CREATE_RESOURCE = 'resource.pre.create';
    const POST_CREATE_RESOURCE = 'resource.post.create';
    const LOAD_UPDATE_RESOURCE = 'resource.load.update';
    const PRE_UPDATE_RESOURCE = 'resource.pre.update';
    const POST_UPDATE_RESOURCE = 'resource.post.update';
    const LOAD_DELETE_RESOURCE = 'resource.load.delete';
    const PRE_DELETE_RESOURCE = 'resource.pre.delete';
    const POST_DELETE_RESOURCE = 'resource.post.delete';

    protected $resource;

    /**
     * The resource event creator Constructor
     *
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get the envent Object
     *
     * @return Resource $resource
     */
    public function getResource()
    {
        return $this->resource;
    }
    /**
     *  Set the event response
     *
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        $this->stopPropagation();
    }
}
