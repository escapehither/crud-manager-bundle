<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 26/11/16
 * Time: 11:48
 */

namespace StarterKit\CrudBundle\Services;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use StarterKit\CrudBundle\Controller\Factory;
use StarterKit\CrudBundle\Controller\ResourceFactory;
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
        $this->em = $em;

    }
    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    public function process($container){

        $parameter = $container->getParameter($this->requestParameterHandler->getResourceConfigName());
        if(isset($parameter['factory'])){
            //$factory = new $parameter['factory']($this->em);
            $factory = $container->get($this->requestParameterHandler->getFactoryServiceName());
            $factoryArguments = $this->requestParameterHandler->getfactoryArguments();
            $factoryMethod = $this->requestParameterHandler->getFactoryMethod();
            if (NULL != $factoryMethod  && NULL != $factoryArguments) {
                $callable = [$factory, $factoryMethod];
                $resource = call_user_func_array($callable, $factoryArguments);

                return $resource;
            }
            elseif (NULL != $factoryMethod  && NULL == $factoryArguments) {
                $callable = [$factory, $factoryMethod];
                $resource = call_user_func($callable);
                return $resource;
            }
            else{
                $factoryService = $container->get($this->requestParameterHandler->getFactoryServiceName());
                $resource =  $factoryService->create();
                return $resource;

            }
        }
        else{
            $resource = ResourceFactory::Create($parameter['entity']);
            return $resource;

        }

    }

}