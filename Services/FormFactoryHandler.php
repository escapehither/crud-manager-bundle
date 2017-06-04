<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 04/12/16
 * Time: 19:13
 */

namespace EscapeHither\CrudManagerBundle\Services;
use Symfony\Component\Form\FormFactory;
use Doctrine\ORM\EntityManager;
use EscapeHither\CrudManagerBundle\Entity\Resource;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Form\Form;
class FormFactoryHandler {
    /**
     * @var RequestParameterHandler
     */
    protected $requestParameterHandler;

    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var FormFactory
     */
    protected $formFactory;

    function __construct(RequestParameterHandler $requestParameterHandler, EntityManager $em, FormFactory $formFactory)
    {
        $this->requestParameterHandler = $requestParameterHandler;
        $this->requestParameterHandler->build();
        $this->em = $em;
        $this->formFactory = $formFactory;

    }

    /**
     * @param $newResource
     * @param $container
     * @return Form
     */
    public function createForm(Resource $newResource, Container $container){
        if($this->requestParameterHandler->getFormConfig()){
            $formConfig=$this->requestParameterHandler->getFormConfig();
        }
        else{
            $parameter = $container->getParameter($this->requestParameterHandler->getResourceConfigName());
            $formConfig = $parameter['form'];
        }
        return $this->createFormFactory($formConfig,$newResource);

    }
    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string $type    The fully qualified class name of the form type
     * @param mixed  $data    The initial data for the form
     * @param array  $options Options for the form
     *
     * @return Form
     */
    protected function createFormFactory($type, $data = null, array $options = array())
    {
        return $this->formFactory->create($type, $data, $options);
    }

}