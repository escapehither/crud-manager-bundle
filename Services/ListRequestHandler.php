<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Services;

use EscapeHither\CrudManagerBundle\ResourceProvider\ResourceProviderInterface;

/**
 * List request Handler
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class ListRequestHandler
{

    /**
     * @var RequestParameterHandler
     */
    protected $requestParameterHandler;
    private $request;
    private $resourceProvider;
    private $container;
    private $format;
    private $resourceClass;

    /**
     * The list request handler constructor
     *
     * @param RequestParameterHandler   $requestParameterHandler The request parameter handler.
     * @param ResourceProviderInterface $resourceProvider        The entity manager.
     */
    public function __construct(RequestParameterHandler $requestParameterHandler, ResourceProviderInterface $resourceProvider)
    {
        $this->requestParameterHandler = $requestParameterHandler;
        $this->requestParameterHandler->build();
        $this->resourceProvider =  $resourceProvider;
        $this->request = $this->requestParameterHandler->getRequest();
        $this->container = $this->requestParameterHandler->container;
        $this->format = $this->requestParameterHandler->getFormat();
        $this->resourceClass = $this->requestParameterHandler->getRepositoryClass();
    }

    /**
     * Handle the request
     *
     * @return mixt
     */
    public function process()
    {
        
        $repositoryArguments = $this->requestParameterHandler->getRepositoryArguments();
        $repositoryMethod = $this->requestParameterHandler->getRepositoryMethod();

        return $this->resourceProvider->getResult($this->request, $this->resourceClass, $this->format);
    }
}
