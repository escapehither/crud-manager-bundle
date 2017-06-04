<?php
/*
 * This file is part of the Genia Bundle.
 *
 * (c) Georden Louzayadio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use EscapeHither\CrudManagerBundle\Entity\Resource;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use EscapeHither\CrudManagerBundle\Event\ResourceCreateEvent;
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

class CrudController extends Controller implements ContainerAwareInterface
{
    /**
     * Lists all the Resources entity.
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $requestParameterHandler = $this->get('request_parameter_handler');
        //dump($request);
        dump($requestParameterHandler);
        die();

        $format=$requestParameterHandler->getFormat();
        // ADD Check if the user have authorisation before proceeding from the request.
        $listRequestHandler = $this->get('list_request_handler');
        $resources = $listRequestHandler->process();

        if($format=='html'){
            $response = $this->render($requestParameterHandler->getThemePath(), array($requestParameterHandler->getResourceViewName() => $resources,));
            return $response;
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
     */
    /**
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {

        $requestParameterHandler = $this->get('request_parameter_handler');
        $resourceName = $requestParameterHandler->getResourceViewName();
        $requireRole = 'ROLE_'.strtoupper($resourceName).'_CREATE';
        $this->denyAccessUnlessGranted(
          $requireRole,
          null,
          'Unable to access this page!'
        );

        $dispatcher = $this->get('crud_event_dispatcher');
        $formFactoryHandler = $this->get('form_factory_handler');
        $newResourceCreationHandler = $this->get(
          'new_resource_creation_handler'
        );
        $flashMessageManager = $this->get('flash_message_manager');
        $newResource = $newResourceCreationHandler->process($this->container);
        $LoadPageEvent = new ResourceCreateEvent($newResource);
        $dispatcher->dispatch(
          ResourceCreateEvent::LOAD_CREATE_RESOURCE,
          $resourceName,
          $LoadPageEvent
        );
        $form = $formFactoryHandler->createForm($newResource, $this->container);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // create the the resource create event and dispatch it
            $event = new ResourceCreateEvent($newResource);
            $dispatcher->dispatch(
              ResourceCreateEvent::PRE_CREATE_RESOURCE,
              $resourceName,
              $event
            );
            $em = $this->getDoctrine()->getManager();
            $em->persist($newResource);
            $em->flush();
            // creating the ACL
            $dispatcher->dispatch(
              ResourceCreateEvent::POST_CREATE_RESOURCE,
              $resourceName,
              $event
            );
            $flashMessageManager->addFlash(
              ResourceCreateEvent::POST_CREATE_RESOURCE
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
     * @param $id  Resource unique identification
     * @return Response
     */
    public function showAction($id)
    {
        $requestParameterHandler = $this->get('request_parameter_handler');
        $SingleResourceRequestHandler = $this->get(
          'single_resource_request_handler'
        );
        $resource = $SingleResourceRequestHandler->process($id);
        if ($resource != null) {
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
        } else {
            // add a message if the does't not exist.
            throw $this->createNotFoundException('The '.$requestParameterHandler->getResourceName().' '.$id.' does not exist');
        }

    }


    /**
     * Displays a form to edit an existing Resource entity.
     * @param  Request $request
     * @param  $id
     * @return Response
     */
    public function editAction(Request $request, $id)
    {

        $dispatcher = $this->get('crud_event_dispatcher');
        $requestParameterHandler = $this->get('request_parameter_handler');
        $formFactoryHandler = $this->get('form_factory_handler');
        $flashMessageManager = $this->get('flash_message_manager');
        $resourceName = $requestParameterHandler->getResourceViewName();
        $SingleResourceRequestHandler = $this->get(
          'single_resource_request_handler'
        );
        $resource = $SingleResourceRequestHandler->process($id);
        if ($resource != null) {
            // check for "edit" access: calls all voters
            $this->denyAccessUnlessGranted('edit', $resource);
            $LoadPageEvent = new ResourceCreateEvent($resource);
            $dispatcher->dispatch(
              ResourceCreateEvent::LOAD_UPDATE_RESOURCE,
              $resourceName,
              $LoadPageEvent
            );
            $deleteFormView = $this->createDeleteForm(
              $requestParameterHandler
            );
            // TODO handle Read-Only Field
            $editForm = $formFactoryHandler->createForm(
              $resource,
              $this->container
            );
            $editForm->handleRequest($request);
        } else {
            // do something if the resource does'not exist.
            $this->addFlash(
              'error',
              'The '.$requestParameterHandler->getResourceName(
              ).' '.$id.' does not exist'
            );
            throw $this->createNotFoundException(
              'The '.$requestParameterHandler->getResourceName(
              ).' '.$id.' does not exist'
            );
        }

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $event = new ResourceCreateEvent($resource);
            $dispatcher->dispatch(
              ResourceCreateEvent::PRE_UPDATE_RESOURCE,
              $resourceName,
              $event
            );
            $em = $this->getManager();
            $em->persist($resource);
            $em->flush();
            $dispatcher->dispatch(
              ResourceCreateEvent::POST_UPDATE_RESOURCE,
              $resourceName,
              $event
            );
            $flashMessageManager->addFlash(
              ResourceCreateEvent::POST_UPDATE_RESOURCE
            );

            return $this->redirectToRoute(
              $requestParameterHandler->getRedirectionRoute(),
              $requestParameterHandler->getRedirectionParameter($resource)
            );
        }

        return $this->render(
          $requestParameterHandler->getThemePath(),
          array(
            $requestParameterHandler->getResourceViewName() => $resource,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteFormView,
          )
        );
    }

    /**
     * Deletes a Resource entity.
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $id)
    {
        $dispatcher = $this->get('crud_event_dispatcher');
        $requestParameterHandler = $this->get('request_parameter_handler');
        $flashMessageManager = $this->get('flash_message_manager');
        $resourceName = $requestParameterHandler->getResourceViewName();
        $SingleResourceRequestHandler = $this->get(
          'single_resource_request_handler'
        );
        $resource = $SingleResourceRequestHandler->process($id);
        if ($resource != null) {
            // check for "delete" access: calls all voters
            $this->denyAccessUnlessGranted('delete', $resource);
            $LoadPageEvent = new ResourceCreateEvent($resource);
            $dispatcher->dispatch(
              ResourceCreateEvent::LOAD_DELETE_RESOURCE,
              $resourceName,
              $LoadPageEvent
            );
            $form = $this->createDeleteForm(
              $requestParameterHandler,
              true
            );
            $form->handleRequest($request);
        } else {
            throw $this->createNotFoundException(
              'The '.$requestParameterHandler->getResourceName(
              ).' '.$id.' does not exist'
            );
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new ResourceCreateEvent($resource);
            $dispatcher->dispatch(ResourceCreateEvent::PRE_DELETE_RESOURCE, $resourceName, $event);
            $em = $this->getDoctrine()->getManager();
            $em->remove($resource);
            $em->flush();
            $dispatcher->dispatch(ResourceCreateEvent::POST_DELETE_RESOURCE, $resourceName, $event);
            $flashMessageManager->addFlash(ResourceCreateEvent::POST_DELETE_RESOURCE);
        }

        return $this->redirectToRoute(
          $requestParameterHandler->getRedirectionRoute(),
          $requestParameterHandler->getRedirectionParameter($resource)
        );
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    public function getManager()
    {
        $em = $this->getDoctrine()->getManager();

        return $em;
    }

    /**
     * Creates a form to delete a Resource entity.
     * @param  RequestParameterHandler $requestParameterHandler
     * @param $action
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(RequestParameterHandler $requestParameterHandler, $action = false) {
        $route = $requestParameterHandler->getDeleteRoute();
        if ($route) {
            $parameter = $requestParameterHandler->getRouteParameter();
            $deleteForm = $this->createFormBuilder()
              ->setAction($this->generateUrl($route, $parameter))
              ->setMethod('DELETE')
              ->getForm();
            if (!$action) {
                return $deleteForm->createView();
            } else {
                return $deleteForm;
            }

        }

        return false;
    }

    /**
     * Api Creates a new Resource entity.
     * @param Request $request
     * @return Response
     */
    public function apiNewAction(Request $request)
    {
        $requestParameterHandler = $this->get('request_parameter_handler');
        $resourceName = $requestParameterHandler->getResourceViewName();
        $requireRole = 'ROLE_'.strtoupper($resourceName).'_CREATE';
        $this->denyAccessUnlessGranted($requireRole, null, 'Unable to access this page!');
        $dispatcher = $this->get('crud_event_dispatcher');
        $formFactoryHandler = $this->get('form_factory_handler');
        $newResourceCreationHandler = $this->get(
          'new_resource_creation_handler'
        );
        $flashMessageManager = $this->get('flash_message_manager');
        $newResource = $newResourceCreationHandler->process($this->container);
        $LoadPageEvent = new ResourceCreateEvent($newResource);
        $dispatcher->dispatch(ResourceCreateEvent::LOAD_CREATE_RESOURCE, $resourceName, $LoadPageEvent);
        $form = $formFactoryHandler->createForm($newResource, $this->container);
        $data = $request->getContent();
        $data = json_decode($data, true);
        if ($data === null) {
            $apiProblem = new ApiProblem(
              400, ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT);
            throw new ApiProblemException($apiProblem);
        }

        $form->submit($data);
        $validator = $this->get('validator');
        if (count($validator->validate($newResource)) > 0) {
            $this->throwApiProblemValidationException($form);
        }

        // create the the resource create event and dispatch it
        $event = new ResourceCreateEvent($newResource);
        $dispatcher->dispatch(
          ResourceCreateEvent::PRE_CREATE_RESOURCE,
          $resourceName,
          $event
        );
        $em = $this->getDoctrine()->getManager();
        $em->persist($newResource);
        $em->flush();
        // creating the ACL
        $dispatcher->dispatch(
          ResourceCreateEvent::POST_CREATE_RESOURCE,
          $resourceName,
          $event
        );
        $flashMessageManager->addFlash(
          ResourceCreateEvent::POST_CREATE_RESOURCE
        );

        return $this->generateJsonResponse(
          $newResource,
          $requestParameterHandler,
          201
        );


    }
    
    /**
     *  Api Find and displays a Resource entity.
     * @param $id
     * @return Response
     */
    public function ApiShowAction($id)
    {
        $requestParameterHandler = $this->get('request_parameter_handler');
        // TODO add costume repository get resource method.
        $SingleResourceRequestHandler = $this->get(
          'single_resource_request_handler'
        );
        $resource = $SingleResourceRequestHandler->process($id);

        if ($resource != null) {
            $response = $this->generateJsonResponse(
              $resource,
              $requestParameterHandler
            );

            return $response;
        } else {
            // add a message if the does't not exist.
            throw $this->createNotFoundException(
              'The '.$requestParameterHandler->getResourceName(
              ).' '.$id.' does not exist'
            );
        }

    }

    /**
     * Api Lists all the Resources entity.
     *
     */
    public function apiIndexAction()
    {
        // TODO ADD Check if the user have authorisation before proceeding from the request.
        $listRequestHandler = $this->get('list_request_handler');
        $resources = $listRequestHandler->process();
        $serializer = $this->getSerializer();
        $data = ['data' => $resources];

        $jsonContent = $serializer->serialize($data, 'json');
        $response = new Response($jsonContent, 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }

    /**
     *  Api edit an existing Resource entity.
     * @param Request $request
     * @param  $id
     * @return Response
     */
    public function apiEditAction(Request $request, $id)
    {
        $dispatcher = $this->get('crud_event_dispatcher');
        $requestParameterHandler = $this->get('request_parameter_handler');
        $formFactoryHandler = $this->get('form_factory_handler');
        $flashMessageManager = $this->get('flash_message_manager');
        $resourceName = $requestParameterHandler->getResourceViewName();
        $SingleResourceRequestHandler = $this->get(
          'single_resource_request_handler'
        );
        $resource = $SingleResourceRequestHandler->process($id);
        if ($resource != null) {
            // check for "edit" access: calls all voters
            $this->denyAccessUnlessGranted('edit', $resource);
            $LoadPageEvent = new ResourceCreateEvent($resource);
            $dispatcher->dispatch(
              ResourceCreateEvent::LOAD_UPDATE_RESOURCE,
              $resourceName,
              $LoadPageEvent
            );
            // TODO handle Read-Only Fields
            $editForm = $formFactoryHandler->createForm(
              $resource,
              $this->container
            );
            $data = $request->getContent();
            $data = json_decode($data, true);
            $clearMissing = $request->getMethod() != 'PATCH';

            $editForm->submit($data, $clearMissing);
            // TODO taking car of the crf token error.
            $validator = $this->get('validator');
            if (count($validator->validate($resource)) > 0) {
                $this->throwApiProblemValidationException($editForm);
            }


            $event = new ResourceCreateEvent($resource);
            $dispatcher->dispatch(
              ResourceCreateEvent::PRE_UPDATE_RESOURCE,
              $resourceName,
              $event
            );
            $em = $this->getManager();
            $em->persist($resource);
            $em->flush();
            $dispatcher->dispatch(
              ResourceCreateEvent::POST_UPDATE_RESOURCE,
              $resourceName,
              $event
            );
            $flashMessageManager->addFlash(
              ResourceCreateEvent::POST_UPDATE_RESOURCE
            );
            $response = $this->generateJsonResponse(
              $resource,
              $requestParameterHandler
            );

            return $response;

        } else {
            // do something if the resource does'not exist.
            $this->addFlash(
              'error',
              'The '.$requestParameterHandler->getResourceName(
              ).' '.$id.' does not exist'
            );
            throw $this->createNotFoundException(
              'The '.$requestParameterHandler->getResourceName(
              ).' '.$id.' does not exist'
            );
        }


    }

    /**
     * Deletes a Resource entity.
     * @param $id
     * @return Response
     */
    public function apiDeleteAction($id)
    {
        $dispatcher = $this->get('crud_event_dispatcher');
        $requestParameterHandler = $this->get('request_parameter_handler');
        $flashMessageManager = $this->get('flash_message_manager');
        $resourceName = $requestParameterHandler->getResourceViewName();
        $SingleResourceRequestHandler = $this->get(
          'single_resource_request_handler'
        );
        $resource = $SingleResourceRequestHandler->process($id);
        if ($resource) {
            // check for "delete" access: calls all voters
            $this->denyAccessUnlessGranted('delete', $resource);
            $LoadPageEvent = new ResourceCreateEvent($resource);
            $dispatcher->dispatch(
              ResourceCreateEvent::LOAD_DELETE_RESOURCE,
              $resourceName,
              $LoadPageEvent
            );
            //if ($form->isSubmitted() && $form->isValid()) {
            $event = new ResourceCreateEvent($resource);
            $dispatcher->dispatch(
              ResourceCreateEvent::PRE_DELETE_RESOURCE,
              $resourceName,
              $event
            );
            $em = $this->getDoctrine()->getManager();
            $em->remove($resource);
            $em->flush();
            $dispatcher->dispatch(
              ResourceCreateEvent::POST_DELETE_RESOURCE,
              $resourceName,
              $event
            );
            $flashMessageManager->addFlash(
              ResourceCreateEvent::POST_DELETE_RESOURCE
            );


            //}

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

    public function getSerializer()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizer = new ObjectNormalizer();
        // This line help avoid circular reference.
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $normalizers = array($normalizer);
        $serializer = new Serializer($normalizers, $encoders);



        return $serializer;
    }

    /**
     * @param $resource
     * @param RequestParameterHandler  $requestParameterHandler
     * @param int $status
     * @return Response
     */
    public function generateJsonResponse($resource, RequestParameterHandler $requestParameterHandler, $status = 200) {
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
     * @param FormInterface $form
     * @return array
     */
    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }

    /**
     * @param FormInterface $form
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
