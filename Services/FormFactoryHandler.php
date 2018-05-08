<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Services;

use Symfony\Component\Form\FormFactory;
use Doctrine\ORM\EntityManager;
use EscapeHither\CrudManagerBundle\Entity\Resource;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Form\Form;

/**
 * The Form factory Handler Handle form creation
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class FormFactoryHandler
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
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * Form factory Handler constructor
     *
     * @param RequestParameterHandler $requestParameterHandler The request parameter handler
     * @param EntityManager           $em                      The Entity manager
     * @param FormFactory             $formFactory             The form factory
     */
    public function __construct(RequestParameterHandler $requestParameterHandler, EntityManager $em, FormFactory $formFactory)
    {
        $this->requestParameterHandler = $requestParameterHandler;
        $this->requestParameterHandler->build();
        $this->em = $em;
        $this->formFactory = $formFactory;
    }

    /**
     * Create form
     *
     * @param Resource  $newResource
     * @param Container $container
     *
     * @return Form
     */
    public function createForm(Resource $newResource, Container $container)
    {
        if ($this->requestParameterHandler->getFormConfig()) {
            $formConfig = $this->requestParameterHandler->getFormConfig();
        } else {
            $parameter = $container->getParameter($this->requestParameterHandler->getResourceConfigName());
            $formConfig = $parameter['form'];
        }

        return $this->createFormFactory($formConfig, $newResource);
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
