<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 20/11/16
 * Time: 21:04
 */

namespace EscapeHither\CrudManagerBundle\Services;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class ListRequestHandler {

    /**
     * @var RequestParameterHandler
     */
    protected $requestParameterHandler;

    /**
     * @var EntityManager
     */
    protected $em;
    private $request;
    private $container;
    private $_links = [];


    function __construct(RequestParameterHandler $requestParameterHandler, EntityManager $em)
    {
        $this->requestParameterHandler = $requestParameterHandler;
        $this->em = $em;
        $this->request = $this->requestParameterHandler->getRequest();
        $this->container = $this->requestParameterHandler->container;


        
    }



    public function process(){
        $format=$this->requestParameterHandler->getFormat();
        $repository = $this->em->getRepository($this->requestParameterHandler->getRepositoryClass());
        $repositoryArguments = $this->requestParameterHandler->getRepositoryArguments();
        $repositoryMethod = $this->requestParameterHandler->getRepositoryMethod();
        if (NULL != $repositoryMethod  && NULL != $repositoryArguments) {
            $callable = [$repository, $repositoryMethod];
            $resources = call_user_func_array($callable, $repositoryArguments);

            return $resources;
        }
        elseif (NULL != $repositoryMethod  && NULL == $repositoryArguments) {
            $callable = [$repository, $repositoryMethod];
            $resources = call_user_func($callable);
            return $resources;
        }

        /*if (null !== $repositoryMethod = $this->requestParameterHandler->getRepositoryMethod()) {

            $callable = [$repository, $repositoryMethod];
            $resources = call_user_func_array($callable, $this->requestParameterHandler->getRepositoryArguments());

            return $resources;
        }*/

        //return $repository->findAll();
        //dump($repository->findAll());
        // TODO CLEAN UP  AND CHECK IF THE REQUEST NEED PAGINATION.

        $qb = $repository->createQueryBuilder('resource');
        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(5);
        $page = 1;
        if(!empty($this->request->query->get('page'))){
            $page = $this->request->query->get('page');
        }
        $pagerfanta->setCurrentPage($page);

        $result = $pagerfanta->getCurrentPageResults();
        if($format=='html'){
            return $pagerfanta;
        }
        else{
            $list['data'] = $result->getArrayCopy();
            $list['pagination'] =[
              'total'=>$pagerfanta->count(),
              'count'=>$pagerfanta->getCurrentPageResults()->count(),
              'current_page'=>$pagerfanta->getCurrentPage(),
              'per_page'=>$pagerfanta->getMaxPerPage(),
              'total_pages'=>$pagerfanta->getNbPages(),
               'links'=>$this->getLinks($pagerfanta),

            ];

            return $list;
        }


        //TODO

        /*if (!$requestConfiguration->isPaginated() && !$requestConfiguration->isLimited()) {
            return $repository->findAll();
        }*/

        /*if (!$requestConfiguration->isPaginated()) {
            return $repository->findBy($requestConfiguration->getCriteria(), $requestConfiguration->getSorting(), $requestConfiguration->getLimit());
        }*/

        //return $repository->createPaginator($requestConfiguration->getCriteria(), $requestConfiguration->getSorting());*/

    }
    // TODO cleaning
    private function getLinks($pagerfanta){

        $route = $this->request->attributes->get('_route');
        // make sure we read the route parameters from the passed option array
        $defaultRouteParams = array_merge($this->request->query->all(), $this->request->attributes->get('_route_params', array()));
        $createLinkUrl = function($targetPage) use ($route, $defaultRouteParams) {
            $router = $this->container->get('router');
            return $router->generate($route, array_merge(
              $defaultRouteParams,
              array('page' => $targetPage)
            ));
        };

        $this->addLink('self', $createLinkUrl($pagerfanta->getCurrentPage()));
        $this->addLink('first', $createLinkUrl(1));
        $this->addLink('last', $createLinkUrl($pagerfanta->getNbPages()));

        if ($pagerfanta->hasNextPage()) {
            $this->addLink('next', $createLinkUrl($pagerfanta->getNextPage()));
        }
        if ($pagerfanta->hasPreviousPage()) {
            $this->addLink('prev', $createLinkUrl($pagerfanta->getPreviousPage()));
        }
        return $this->_links;

    }
    public function addLink($ref, $url)
    {
        $this->_links[$ref] = $url;
    }

}
