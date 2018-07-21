<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\ResourceProvider;

use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

/**
 * Doctrine resource provider
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class DoctrineResourceProvider extends AbstractResourceProviderBase implements ResourceProviderInterface
{

    const DEFAULT_MAX_PER_PAGE = 10;
    /**
     * @var EntityManager The entity manager.
     */
    private $em;
    /**
     * @var Router $router Tne router.
     */
    private $router;

    /**
     * Resource provider constructor.
     *
     * @param EntityManager $em     The entity manager.
     * @param Router        $router The router service.
     */
    public function __construct(EntityManager $em, Router $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * Get the resource provider result.
     *
     * @param Request $request         The request.
     * @param string  $resourceClass   The resource class.
     * @param string  $format          The format.
     * @param [type]  $method          The method.
     * @param [type]  $methodArguments The method arguments.
     *
     * @return array/Pagerfanta
     */
    public function getResult($request, $resourceClass, $format, $method = null, $methodArguments = null)
    {

        $repository = $this->em->getRepository($resourceClass);

        if (null !== $method) {
            return $this->getResourcesFromMethod($method, $repositoryArguments, $repository);
        }

        // TODO CLEAN UP  AND CHECK IF THE REQUEST NEED PAGINATION.
        $qb = $repository->createQueryBuilder('resource');
        $entityPropertiesName = $this->getEntityPropertiesName($resourceClass);

        foreach ($request->query->all() as $key => $value) {
            if (in_array($key, $entityPropertiesName)) {
                if (is_array($value)) {
                    $string = '';
                    $parameters = [];
                    $count = 0;
                    $space = '';

                    foreach ($value as $filterKey => $filter) {
                        if ($count > 0) {
                            $space = " OR";
                        }

                        $string .= sprintf('%s resource.%s = :%s%d', $space, $key, $key, $filterKey);
                        $parameters[$key.$filterKey] = $filter;
                        $count += 1;
                    }

                    $qb->andwhere($string);
                    $qb->setParameters($parameters);
                } else {
                    $qb->andwhere(sprintf('resource.%s = :%s', $key, $key));
                    $qb->setParameter($key, $value);
                }
            }
        }
        // Handle sorting and order.
        $sort = $request->query->get('_sort');
        $order = $request->query->get('_order');
        $maxPerPage = self::DEFAULT_MAX_PER_PAGE;

        if (!empty($sort) && in_array($sort, $entityPropertiesName)) {
            if ('asc' === $order) {
                $qb->orderBy(sprintf('resource.%s', $sort), $order);
            } else {
                $qb->orderBy(sprintf('resource.%s', $sort), 'desc');
            }
        }

        $pagerFanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $page = 1;

        if (!empty($request->query->get('page'))) {
            $page = $request->query->get('page');
        }

        if (!empty($request->query->get('_max_per_page')) && is_int($request->query->get('_max_per_page'))) {
            $maxPerPage = $request->query->get('_max_per_page');
        }

        $pagerFanta->setMaxPerPage($maxPerPage);

        $pagerFanta->setCurrentPage($page);

        $data = $pagerFanta->getCurrentPageResults();

        if ('html' === $format) {
            return $pagerFanta;
        }

        $result['data'] = $data->getArrayCopy();
        $result['pagination'] = [
              'total' => $pagerFanta->count(),
              'count' => $pagerFanta->getCurrentPageResults()->count(),
              'current_page' => $pagerFanta->getCurrentPage(),
              'per_page' => $pagerFanta->getMaxPerPage(),
              'total_pages' => $pagerFanta->getNbPages(),
               'links' => $this->getLinks($pagerFanta, $request),

            ];

        return $result;
    }
        /**
     * Get resource from a define config method
     *
     * @param string $method              The repository method
     * @param string $repositoryArguments The repository arguments
     * @param string $repository          Teh repository
     *
     * @return mixed
     */
    protected function getResourcesFromMethod($method, $repositoryArguments, $repository)
    {

        if (null !== $repositoryArguments) {
            $callable = [$repository, $method];

            return call_user_func_array($callable, $repositoryArguments);
        }

        $callable = [$repository, $method];

        return call_user_func($callable);
    }
    /**
     * Get all resource page list link
     *
     * @param Pagerfanta $pagerFanta
     *
     * @return []
     */
    private function getLinks(Pagerfanta $pagerFanta, $request)
    {
        $route = $request->attributes->get('_route');
        // make sure we read the route parameters from the passed option array
        $defaultRouteParams = array_merge($request->query->all(), $request->attributes->get('_route_params', array()));
        $createLinkUrl = function ($targetPage) use ($route, $defaultRouteParams) {

            return $this->router->generate($route, array_merge(
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
}
