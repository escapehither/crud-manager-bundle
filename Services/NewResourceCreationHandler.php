<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Services;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use EscapeHither\CrudManagerBundle\Controller\Factory;
use EscapeHither\CrudManagerBundle\Controller\ResourceFactory;
use Doctrine\ORM\EntityManager;

/**
 * New resource creation Handler
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class NewResourceCreationHandler implements ContainerAwareInterface
{
    use ContainerAwareTrait;


    /**
     * @var RequestParameterHandler
     */
    protected $requestParameterHandler;

    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * Container.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * New resource handler constructor
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
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Handle the request
     *
     * @param ContainerInterface $container The container
     *
     * @return Resource
     */
    public function process(ContainerInterface $container)
    {

        $parameter = $container->getParameter($this->requestParameterHandler->getResourceConfigName());

        if (isset($parameter['factory'])) {
            $factory = $container->get($this->requestParameterHandler->getFactoryServiceName());
            $factoryArguments = $this->requestParameterHandler->getfactoryArguments();
            $factoryMethod = $this->requestParameterHandler->getFactoryMethod();

            if (null !== $factoryMethod  && null !== $factoryArguments) {
                $resource = call_user_func_array([$factory, $factoryMethod], $factoryArguments);
            } elseif (null !== $factoryMethod  && null === $factoryArguments) {
                $resource = call_user_func([$factory, $factoryMethod]);
            } else {
                $factoryService = $container->get($this->requestParameterHandler->getFactoryServiceName());
                $resource  =  $factoryService->create();
            }

            return $resource;
        }

        return ResourceFactory::Create($parameter['entity']);
    }
}
