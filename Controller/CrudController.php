<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace EscapeHither\CrudManagerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use EscapeHither\CrudManagerBundle\Event\ResourceEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Form\FormInterface;
use EscapeHither\CrudManagerBundle\Api\ApiProblem;
use EscapeHither\CrudManagerBundle\Api\ApiProblemException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EscapeHither\CrudManagerBundle\Services\RequestParameterHandler;
use Symfony\Component\Form\Exception\LogicException;
use EscapeHither\CrudManagerBundle\Entity\ResourceInterface;

/**
 * The crud controller
 * Provide all CRUD AND API method.
 *
 *@author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class CrudController extends Controller implements ContainerAwareInterface
{
    const NOT_FOUND = 'does not exist';
    const EVENT_DISPATCHER = 'escapehither.crud_event_dispatcher';
    const FLASH_MANAGER = 'escapehither.crud_flash_message_manager';
    const FORM_FACTORY = 'escapehither.crud_form_factory_handler';
    const REQUEST_PARAMETER_HANDLER = 'escapehither.crud_request_parameter_handler';
    const SINGLE_RESOURCE_HANDLER = 'escapehither.crud_single_resource_request_handler';
    const NEW_RESOURCE_HANDLER = 'escapehither.crud_new_resource_creation_handler';

    /**
     *  Lists all the Resources entity.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $requestParameterHandler = $this->getRequestParameterHandler();
        $format = $requestParameterHandler->getFormat();
        $resourceName = $requestParameterHandler->getResourceViewName();
        $this->securityCheck($requestParameterHandler, $resourceName);
        // ADD Check if the user have authorisation before proceeding from the request.
        $listRequestHandler = $this->get('escapehither.crud_list_request_handler');
        $resources = $listRequestHandler->process();

        if ('html' === $format) {
            return $this->render($requestParameterHandler->getThemePath(), array($resourceName => $resources, ));
        }

        $serializer = $this->getSerializer();
        $jsonContent = $serializer->serialize($resources, 'json');
        $response = new Response($jsonContent, 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

 
    /**
     * Creates a new Resource entity.
     *
     * @param Request $request The request
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $requestParameterHandler = $this->getRequestParameterHandler();
        $resourceName = $requestParameterHandler->getResourceViewName();
        $this->securityCheck($requestParameterHandler, $resourceName);
        $dispatcher = $this->get(self::EVENT_DISPATCHER);
        $newResource = $this->get(self::NEW_RESOURCE_HANDLER)->process($this->container);
        $dispatcher->dispatch(
            ResourceEvent::LOAD_CREATE_RESOURCE,
            $resourceName,
            new ResourceEvent($newResource)
        );
        $form = $this->get(self::FORM_FACTORY)->createForm($newResource, $this->container);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // create the the resource create event and dispatch it
            $event = new ResourceEvent($newResource);
            $dispatcher->dispatch(
                ResourceEvent::PRE_CREATE_RESOURCE,
                $resourceName,
                $event
            );
            $em = $this->getDoctrine()->getManager();
            $em->persist($newResource);
            $em->flush();
            $dispatcher->dispatch(
                ResourceEvent::POST_CREATE_RESOURCE,
                $resourceName,
                $event
            );
            $this->get(self::FLASH_MANAGER)->addFlash(
                ResourceEvent::POST_CREATE_RESOURCE
            );

            return $this->redirectToRoute(
                $requestParameterHandler->getRedirectionRoute(),
                $requestParameterHandler->getRedirectionParameter($newResource)
            );
        }

        return $this->render(
            $requestParameterHandler->getThemePath(),
            array(
                $requestParameterHandler->getResourceViewName() => $newResource,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Find and displays a Resource entity.
     *
     * @param int $id Resource unique identification
     *
     * @return Response
     */
    public function showAction($id)
    {
        $requestParameterHandler = $this->getRequestParameterHandler();
        $singleResourceRequestHandler = $this->get(self::SINGLE_RESOURCE_HANDLER);
        $resource = $singleResourceRequestHandler->process($id);

        if (null !== $resource) {
            $deleteFormView = $this->createDeleteForm(
                $requestParameterHandler
            );

            return $this->render(
                $requestParameterHandler->getThemePath(),
                array(
                    $requestParameterHandler->getResourceViewName() => $resource,
                    'delete_form' => $deleteFormView,
                )
            );
        }
        // Add a message if the does't not exist.
        throw $this->createNotFoundException(sprintf('The %s %d %s', $requestParameterHandler->getResourceName(), $id, self::NOT_FOUND));
    }

    /**
     * Displays a form to edit an existing Resource entity.
     *
     * @param  Request $request The request
     * @param  int     $id      The resource id
     *
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        $dispatcher = $this->get(self::EVENT_DISPATCHER);
        $requestParameterHandler = $this->getRequestParameterHandler();
        $resourceName = $requestParameterHandler->getResourceViewName();
        $resource = $this->get(self::SINGLE_RESOURCE_HANDLER)->process($id);

        if (null === $resource) {
            throw $this->createNotFoundException(
                sprintf('The /%s %d %s', $requestParameterHandler->getResourceName(), $id, self::NOT_FOUND)
            );
        }

         // check for "edit" access: calls all voters
         $this->denyAccessUnlessGranted('edit', $resource);
         $dispatcher->dispatch(ResourceEvent::LOAD_UPDATE_RESOURCE, $resourceName, new ResourceEvent($resource));
         // TODO handle Read-Only Field
         $editForm = $this->get(self::FORM_FACTORY)->createForm($resource, $this->container);
         $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $event = new ResourceEvent($resource);
            $dispatcher->dispatch(
                ResourceEvent::PRE_UPDATE_RESOURCE,
                $resourceName,
                $event
            );
            $em = $this->getManager();
            $em->persist($resource);
            $em->flush();
            $dispatcher->dispatch(
                ResourceEvent::POST_UPDATE_RESOURCE,
                $resourceName,
                $event
            );
            $this->get(self::FLASH_MANAGER)->addFlash(
                ResourceEvent::POST_UPDATE_RESOURCE
            );

            return $this->redirectToRoute(
                $requestParameterHandler->getRedirectionRoute(),
                $requestParameterHandler->getRedirectionParameter($resource)
            );
        }

        return $this->render(
            $requestParameterHandler->getThemePath(),
            array(
                $resourceName => $resource,
                'edit_form' => $editForm->createView(),
                'delete_form' => $this->createDeleteForm($requestParameterHandler),
            )
        );
    }

    /**
     * Deletes a Resource entity.
     *
     * @param Request $request The Request
     * @param int     $id      The recource unique identifier
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $id)
    {
        $dispatcher = $this->get(self::EVENT_DISPATCHER);
        $requestParameterHandler = $this->getRequestParameterHandler();
        $resourceName = $requestParameterHandler->getResourceViewName();
        $resource = $this->get(self::SINGLE_RESOURCE_HANDLER)->process($id);

        if (null === $resource) {
            throw $this->createNotFoundException(
                sprintf('The /%s %d %s', $requestParameterHandler->getResourceName(), $id, self::NOT_FOUND)
            );
        }

        // check for "delete" access: calls all voters
        $this->denyAccessUnlessGranted('delete', $resource);
        $dispatcher->dispatch(
            ResourceEvent::LOAD_DELETE_RESOURCE,
            $resourceName,
            new ResourceEvent($resource)
        );
        $form = $this->createDeleteForm($requestParameterHandler, true);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new ResourceEvent($resource);
            $dispatcher->dispatch(ResourceEvent::PRE_DELETE_RESOURCE, $resourceName, $event);
            $em = $this->getDoctrine()->getManager();
            $em->remove($resource);
            $em->flush();
            $dispatcher->dispatch(ResourceEvent::POST_DELETE_RESOURCE, $resourceName, $event);
            $this->get(self::FLASH_MANAGER)->addFlash(ResourceEvent::POST_DELETE_RESOURCE);
        }

        return $this->redirectToRoute(
            $requestParameterHandler->getRedirectionRoute(),
            $requestParameterHandler->getRedirectionParameter($resource)
        );
    }

    /**
     * Api Creates a new Resource entity.
     *
     * @param Request $request The request
     *
     * @return Response
     */
    public function apiNewAction(Request $request)
    {
        $requestParameterHandler = $this->getRequestParameterHandler();
        $resourceName = $requestParameterHandler->getResourceViewName();
        $this->denyAccessUnlessGranted(sprintf('ROLE_%$_CREATE', strtoupper($resourceName)), null, 'Unable to access this page!');
        $dispatcher = $this->get(self::EVENT_DISPATCHER);
        $newResource =  $this->get(self::NEW_RESOURCE_HANDLER)->process($this->container);
        $dispatcher->dispatch(ResourceEvent::LOAD_CREATE_RESOURCE, $resourceName, new ResourceEvent($newResource));
        $form = $this->get(self::FORM_FACTORY)->createForm($newResource, $this->container);
        $data = json_decode($request->getContent(), true);

        if (null === $data) {
            throw new ApiProblemException(new ApiProblem(400, ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT));
        }

        $form->submit($data);
        if (count($this->get('validator')->validate($newResource)) > 0) {
            $this->throwApiProblemValidationException($form);
        }

        // create the the resource create event and dispatch it
        $event = new ResourceEvent($newResource);
        $dispatcher->dispatch(
            ResourceEvent::PRE_CREATE_RESOURCE,
            $resourceName,
            $event
        );
        $em = $this->getDoctrine()->getManager();
        $em->persist($newResource);
        $em->flush();
        // creating the ACL
        $dispatcher->dispatch(
            ResourceEvent::POST_CREATE_RESOURCE,
            $resourceName,
            $event
        );

        return $this->generateJsonResponse(
            $newResource,
            $requestParameterHandler,
            201
        );
    }

    /**
     *  Api Find and displays a Resource entity.
     *
     * @param int $id The resource parameter inique identifier
     *
     * @return Response The JsonResponse
     */
    public function apiShowAction($id)
    {
        $requestParameterHandler = $this->getRequestParameterHandler();
        $resource = $this->get(
            self::SINGLE_RESOURCE_HANDLER
        )->process($id);

        if (null === $resource) {
            throw $this->createNotFoundException(
                sprintf('The /%s %d %s', $requestParameterHandler->getResourceName(), $id, self::NOT_FOUND)
            );
        }

        return $this->generateJsonResponse(
            $resource,
            $requestParameterHandler
        );
    }

    /**
     *  Api edit an existing Resource entity.
     *
     * @param Request $request The request
     * @param int     $id      The resource unique identifier
     *
     * @return Response The response
     */
    public function apiEditAction(Request $request, $id)
    {
        $dispatcher = $this->get(self::EVENT_DISPATCHER);
        $requestParameterHandler = $this->getRequestParameterHandler();
        $resourceName = $requestParameterHandler->getResourceViewName();

        $resource = $this->get(self::SINGLE_RESOURCE_HANDLER)->process($id);

        if (null === $resource) {
            throw $this->createNotFoundException(
                sprintf('The /%s %d %s', $requestParameterHandler->getResourceName(), $id, self::NOT_FOUND)
            );
        }
         // check for "edit" access: calls all voters
        $this->denyAccessUnlessGranted('edit', $resource);
        $loadPageEvent = new ResourceEvent($resource);
        $dispatcher->dispatch(ResourceEvent::LOAD_UPDATE_RESOURCE, $resourceName, $loadPageEvent);
        // TODO handle Read-Only Fields
        $editForm = $this->get(self::FORM_FACTORY)->createForm($resource, $this->container);
        $data = $request->getContent();
        $data = json_decode($data, true);

        $editForm->submit($data, 'PATCH' !== $request->getMethod());
        // TODO taking car of the crf token error.

        if (count($this->get('validator')->validate($resource)) > 0) {
            $this->throwApiProblemValidationException($editForm);
        }

        $event = new ResourceEvent($resource);
        $dispatcher->dispatch(ResourceEvent::PRE_UPDATE_RESOURCE, $resourceName, $event);
        $em = $this->getManager();
        $em->persist($resource);
        $em->flush();
        $dispatcher->dispatch(
            ResourceEvent::POST_UPDATE_RESOURCE,
            $resourceName,
            $event
        );

        return $this->generateJsonResponse(
            $resource,
            $requestParameterHandler
        );
    }

    /**
     * Deletes a Resource entity.
     *
     * @param int $id The resource unique identifier
     *
     * @return Response
     */
    public function apiDeleteAction($id)
    {
        $dispatcher = $this->get(self::EVENT_DISPATCHER);
        $requestParameterHandler = $this->getRequestParameterHandler();
        $resourceName = $requestParameterHandler->getResourceViewName();
        $resource = $this->get(self::SINGLE_RESOURCE_HANDLER)->process($id);

        if ($resource) {
            // check for "delete" access: calls all voters
            $this->denyAccessUnlessGranted('delete', $resource);
            $dispatcher->dispatch(
                ResourceEvent::LOAD_DELETE_RESOURCE,
                $resourceName,
                new ResourceEvent($resource)
            );
            //TODO isSubmitted and isValid
            $event = new ResourceEvent($resource);
            $dispatcher->dispatch(
                ResourceEvent::PRE_DELETE_RESOURCE,
                $resourceName,
                $event
            );
            $em = $this->getDoctrine()->getManager();
            $em->remove($resource);
            $em->flush();
            $dispatcher->dispatch(
                ResourceEvent::POST_DELETE_RESOURCE,
                $resourceName,
                $event
            );
        }
        $response = new Response(null, 204);
        $response->headers->set('Content-Type', 'application/json');
        $url = $this->generateUrl(
            $requestParameterHandler->getRedirectionRoute(),
            $requestParameterHandler->getRedirectionParameter($resource)
        );
        $response->headers->set('Location', $url);

        return $response;
    }

    /**
     * Get The serializer
     *
     * @return Serializer
     */
    public function getSerializer()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizer = new ObjectNormalizer();
        // This line help avoid circular reference.
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $normalizers = array($normalizer);

        return new Serializer($normalizers, $encoders);
    }

    /**
     * Generate the Json response
     *
     * @param ResourceInterface       $resource                The resource
     * @param RequestParameterHandler $requestParameterHandler The request parameter Handler
     * @param int                     $status                  The response status
     *
     * @return Response
     */
    public function generateJsonResponse($resource, RequestParameterHandler $requestParameterHandler, $status = 200)
    {
        $serializer = $this->getSerializer();
        $data = ['data' => $resource];
        $jsonContent = $serializer->serialize($data, 'json');
        $response = new Response($jsonContent, $status);
        $response->headers->set('Content-Type', 'application/json');
        $url = $this->generateUrl(
            $requestParameterHandler->getRedirectionRoute(),
            $requestParameterHandler->getRedirectionParameter($resource)
        );
        $response->headers->set('Location', $url);

        return $response;
    }

     /**
     * Get Doctrine manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    public function getManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return \EscapeHither\CrudManagerBundle\Services\RequestParameterHandler
     */
    protected function getRequestParameterHandler()
    {
        $requestParameterHandler = $this->get(self::REQUEST_PARAMETER_HANDLER);
        $requestParameterHandler->build();

        return $requestParameterHandler;
    }

    /**
     * Check access
     *
     * @param RequestParameterHandler $requestParameterHandler The request parameter Handler
     * @param string                  $resourceName            The resource name
     */
    protected function securityCheck(RequestParameterHandler $requestParameterHandler, $resourceName)
    {
        $securityConfig = $requestParameterHandler->getSecurityConfig();

        if (empty($securityConfig)) {
            $requireRole = sprintf('ROLE_%s_CREATE', strtoupper($resourceName));
            $this->denyAccessUnlessGranted(
                $requireRole,
                null,
                'Unable to access this page!'
            );
        } elseif (!empty($securityConfig['check'])) {
                $requireRole = $securityConfig['check'];
                $this->denyAccessUnlessGranted(
                    $requireRole,
                    null,
                    'Unable to access this page!'
                );
        }
    }

    /**
     * Creates a form to delete a Resource entity.
     *
     * @param  RequestParameterHandler $requestParameterHandler The request parameter Handler
     * @param  bool                    $action                  Tell if it's comming from an action
     *
     * @return \Symfony\Component\Form\Form The form
     *
     *@throws \LogicException when the delete is not provided
     */
    private function createDeleteForm(RequestParameterHandler $requestParameterHandler, $action = false)
    {
        $route = $requestParameterHandler->getDeleteRoute();

        if ($action & !$route) {
            $actionName = $requestParameterHandler->getActionName();
            $routeName = $requestParameterHandler->getRouteName();
            throw new LogicException(sprintf(' The parameter delete_route is require for %s call in route : %s', $actionName, $routeName));
        }

        if ($route) {
            $parameter = $requestParameterHandler->getRouteParameter();
            $deleteForm = $this->createFormBuilder()
                ->setAction($this->generateUrl($route, $parameter))
                ->setMethod('DELETE')
                ->getForm();
            if (!$action) {
                return $deleteForm->createView();
            }

            return $deleteForm;
        }

        return false;
    }

    /**
     * Get Error from the form
     *
     * @param FormInterface $form
     *
     * @return array
     */
    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface && $childErrors = $this->getErrorsFromForm($childForm)) {
                $errors[$childForm->getName()] = $childErrors;
            }
        }

        return $errors;
    }


    /**
     * Throw Api problem Validation Exeption
     * @param FormInterface $form
     *
     * @return Response
     */
    private function throwApiProblemValidationException(FormInterface $form)
    {
        $errors = $this->getErrorsFromForm($form);
        $apiProblem = new ApiProblem(400, ApiProblem::TYPE_VALIDATION_ERROR);
        $apiProblem->set('errors', $errors);
        throw new ApiProblemException($apiProblem);
    }
}
