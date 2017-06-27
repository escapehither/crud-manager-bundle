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



class SingleResourceRequestHandler {

    /**
     * @var RequestParameterHandler
     */
    protected $requestParameterHandler;

    /**
     * @var EntityManager
     */
    protected $em;


    function __construct(RequestParameterHandler $requestParameterHandler, EntityManager $em)
    {
        $this->requestParameterHandler = $requestParameterHandler;
        $this->requestParameterHandler->build();
        $this->em = $em;

        
    }



    public function process($parameter){
        // TODO CHECK if COSTUME REPOSITORY METHOD WORK AND CHECK TO RETURN ONLY ONE RESOURCE.
        $this->requestParameterHandler;
        $repository = $this->em->getRepository($this->requestParameterHandler->getRepositoryClass());
        $repositoryArguments = $this->requestParameterHandler->getRepositoryArguments();
        $repositoryMethod = $this->requestParameterHandler->getRepositoryMethod();
        if (NULL != $repositoryMethod  && NULL != $repositoryArguments) {
            $callable = [$repository, $repositoryMethod];
            return call_user_func_array($callable, $repositoryArguments);


        }
        elseif (NULL != $repositoryMethod  && NULL == $repositoryArguments) {
            $callable = [$repository, $repositoryMethod];
            return call_user_func($callable,$parameter);

        }
        return $repository->find($parameter);





    }

}
