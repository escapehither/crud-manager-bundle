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
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * List request Handler
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class ListRequestHandler
{

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
    private $links = [];

    /**
     * The list request handler constructor
     *
     * @param RequestParameterHandler $requestParameterHandler The request parameter handler
     * @param EntityManager           $em                      The entity manager
     */
    public function __construct(RequestParameterHandler $requestParameterHandler, EntityManager $em)
    {
        $this->requestParameterHandler = $requestParameterHandler;
        $this->requestParameterHandler->build();
        $this->em = $em;
        $this->request = $this->requestParameterHandler->getRequest();
        $this->container = $this->requestParameterHandler->container;
    }

    /**
     * Handle the request
     *
     * @return mixt
     */
    public function process()
    {
        $format = $this->requestParameterHandler->getFormat();
        $repository = $this->em->getRepository($this->requestParameterHandler->getRepositoryClass());
        $repositoryArguments = $this->requestParameterHandler->getRepositoryArguments();
        $repositoryMethod = $this->requestParameterHandler->getRepositoryMethod();

        if (null !== $repositoryMethod) {
            return $this->getResourcesFromMethod($repositoryMethod, $repositoryArguments, $repository);
        }

        // TODO CLEAN UP  AND CHECK IF THE REQUEST NEED PAGINATION.
        $pagerFanta = new Pagerfanta(new DoctrineORMAdapter($repository->createQueryBuilder('resource')));
        $pagerFanta->setMaxPerPage(5);
        $page = 1;

        if (!empty($this->request->query->get('page'))) {
            $page = $this->request->query->get('page');
        }

        $pagerFanta->setCurrentPage($page);

        $result = $pagerFanta->getCurrentPageResults();

        if ('html' === $format) {
            return $pagerFanta;
        }

        $list['data'] = $result->getArrayCopy();
        $list['pagination'] = [
              'total' => $pagerFanta->count(),
              'count' => $pagerFanta->getCurrentPageResults()->count(),
              'current_page' => $pagerFanta->getCurrentPage(),
              'per_page' => $pagerFanta->getMaxPerPage(),
              'total_pages' => $pagerFanta->getNbPages(),
               'links' => $this->getLinks($pagerFanta),

            ];

        return $list;


        // TODO Check if the pagination is not needed and if is limited
        // TODO add criteria and sorting.
    }

    /**
     * Get resource from a define config method
     *
     * @param string $repositoryMethod    The repository method
     * @param string $repositoryArguments The repository arguments
     * @param string $repository          Teh repository
     *
     * @return mixed
     */
    protected function getResourcesFromMethod($repositoryMethod, $repositoryArguments, $repository)
    {

        if (null !== $repositoryArguments) {
            $callable = [$repository, $repositoryMethod];

            return call_user_func_array($callable, $repositoryArguments);
        }

        $callable = [$repository, $repositoryMethod];

        return call_user_func($callable);
    }

    // TODO cleaning
    /**
     * Get all resource page list link
     *
     * @param Pagerfanta $pagerFanta
     *
     * @return []
     */
    private function getLinks(Pagerfanta $pagerFanta)
    {

        $route = $this->request->attributes->get('_route');
        // make sure we read the route parameters from the passed option array
        $defaultRouteParams = array_merge($this->request->query->all(), $this->request->attributes->get('_route_params', array()));
        $createLinkUrl = function ($targetPage) use ($route, $defaultRouteParams) {
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

        return $this->links;
    }

    /**
     * Add link
     *
     * @param string $ref The reference
     * @param string $url The resource page url
     */
    private function addLink($ref, $url)
    {
        $this->links[$ref] = $url;
    }
}
