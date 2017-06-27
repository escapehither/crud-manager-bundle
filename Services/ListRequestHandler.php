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
        $this->requestParameterHandler->build();
        $this->em = $em;
        $this->request = $this->requestParameterHandler->getRequest();
        $this->container = $this->requestParameterHandler->container;


        
    }



    public function process(){
        $format=$this->requestParameterHandler->getFormat();
        $repository = $this->em->getRepository($this->requestParameterHandler->getRepositoryClass());
        $repositoryArguments = $this->requestParameterHandler->getRepositoryArguments();
        $repositoryMethod = $this->requestParameterHandler->getRepositoryMethod();
        if(NULL != $repositoryMethod){
            return $this->getResourcesFromMethod($repositoryMethod, $repositoryArguments, $repository);
        }
        /*if (null !== $repositoryMethod = $this->requestParameterHandler->getRepositoryMethod()) {

            $callable = [$repository, $repositoryMethod];
            $resources = call_user_func_array($callable, $this->requestParameterHandler->getRepositoryArguments());

            return $resources;
        }*/

        //return $repository->findAll();

        // TODO CLEAN UP  AND CHECK IF THE REQUEST NEED PAGINATION.

        $qb = $repository->createQueryBuilder('resource');
        $adapter = new DoctrineORMAdapter($qb);
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage(5);
        $page = 1;
        if(!empty($this->request->query->get('page'))){
            $page = $this->request->query->get('page');
        }
        $pagerFanta->setCurrentPage($page);

        $result = $pagerFanta->getCurrentPageResults();
        if($format=='html'){
            return $pagerFanta;
        }
        else{
            $list['data'] = $result->getArrayCopy();
            $list['pagination'] =[
              'total'=>$pagerFanta->count(),
              'count'=>$pagerFanta->getCurrentPageResults()->count(),
              'current_page'=>$pagerFanta->getCurrentPage(),
              'per_page'=>$pagerFanta->getMaxPerPage(),
              'total_pages'=>$pagerFanta->getNbPages(),
               'links'=>$this->getLinks($pagerFanta),

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

    /**
     * @param $repositoryMethod
     * @param $repositoryArguments
     * @param $repository
     * @return mixed
     */
    protected function getResourcesFromMethod($repositoryMethod, $repositoryArguments, $repository) {

        if ($repositoryArguments != NULL) {
            $callable = [$repository, $repositoryMethod];
            return call_user_func_array($callable, $repositoryArguments);
        }
        elseif (  $repositoryArguments == NULL ) {
            $callable = [$repository, $repositoryMethod];
            return call_user_func($callable);

        }
       return [];
    }

    // TODO cleaning
    private function getLinks(Pagerfanta $pagerFanta){

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

        $this->addLink('self', $createLinkUrl($pagerFanta->getCurrentPage()));
        $this->addLink('first', $createLinkUrl(1));
        $this->addLink('last', $createLinkUrl($pagerFanta->getNbPages()));

        if ($pagerFanta->hasNextPage()) {
            $this->addLink('next', $createLinkUrl($pagerFanta->getNextPage()));
        }
        if ($pagerFanta->hasPreviousPage()) {
            $this->addLink('prev', $createLinkUrl($pagerFanta->getPreviousPage()));
        }
        return $this->_links;

    }
    public function addLink($ref, $url)
    {
        $this->_links[$ref] = $url;
    }

}
