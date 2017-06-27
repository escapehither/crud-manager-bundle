<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 26/11/16
 * Time: 11:48
 */

namespace EscapeHither\CrudManagerBundle\Services;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use EscapeHither\CrudManagerBundle\Controller\Factory;
use EscapeHither\CrudManagerBundle\Controller\ResourceFactory;
use Doctrine\ORM\EntityManager;


class NewResourceCreationHandler implements ContainerAwareInterface {
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

    function __construct(RequestParameterHandler $requestParameterHandler, EntityManager $em)
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
    public function process(ContainerInterface $container){

        $parameter = $container->getParameter($this->requestParameterHandler->getResourceConfigName());
        if(isset($parameter['factory'])){
            $factory = $container->get($this->requestParameterHandler->getFactoryServiceName());
            $factoryArguments = $this->requestParameterHandler->getfactoryArguments();
            $factoryMethod = $this->requestParameterHandler->getFactoryMethod();
            if (NULL != $factoryMethod  && NULL != $factoryArguments) {
                $callable = [$factory, $factoryMethod];
                $resource = call_user_func_array($callable, $factoryArguments);
            }
            elseif (NULL != $factoryMethod  && NULL == $factoryArguments) {
                $callable = [$factory, $factoryMethod];
                $resource = call_user_func($callable);
            }
            else{
                $factoryService = $container->get($this->requestParameterHandler->getFactoryServiceName());
                $resource  =  $factoryService->create();


            }
            return $resource;
        }
        else{
            $resource = ResourceFactory::Create($parameter['entity']);
            return $resource;

        }

    }

}