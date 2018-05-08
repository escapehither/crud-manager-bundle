<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Services;

use Doctrine\ORM\EntityManager;

/**
 * Single resource request handler
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class SingleResourceRequestHandler
{

    /**
     * @var RequestParameterHandler
     */
    protected $requestParameterHandler;

    /**
     * @var EntityManager
     */
    protected $em;


    /**
     * Single resource handler constructor
     *
     * @param RequestParameterHandler $requestParameterHandler The request parameter handler
     * @param EntityManager           $em                      The Entity manager
     */
    public function __construct(RequestParameterHandler $requestParameterHandler, EntityManager $em)
    {
        $this->requestParameterHandler = $requestParameterHandler;
        $this->requestParameterHandler->build();
        $this->em = $em;
    }

    /**
     * Handle the request
     *
     * @param ContainerInterface $parameter The parameter
     *
     * @return mixt
     */
    public function process($parameter)
    {
        // TODO CHECK if COSTUM REPOSITORY METHOD WORK AND CHECK TO RETURN ONLY ONE RESOURCE.
        $this->requestParameterHandler;
        $repository = $this->em->getRepository($this->requestParameterHandler->getRepositoryClass());
        $repositoryArguments = $this->requestParameterHandler->getRepositoryArguments();
        $repositoryMethod = $this->requestParameterHandler->getRepositoryMethod();

        if (null !== $repositoryMethod  && null !== $repositoryArguments) {
            // TODO check if they are callable
            return call_user_func_array([$repository, $repositoryMethod], $repositoryArguments);
        }

        if (null !== $repositoryMethod  && null === $repositoryArguments) {
            // TODO check if they are callable
            return call_user_func([$repository, $repositoryMethod], $parameter);
        }

        return $repository->find($parameter);
    }
}
