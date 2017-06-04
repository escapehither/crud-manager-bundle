<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 28/01/17
 * Time: 19:35
 */

namespace EscapeHither\CrudManagerBundle\Services;


class FlashMessageManager
{
    protected $requestParameterHandler;
    function __construct(RequestParameterHandler $requestParameterHandler)
    {
        $this->requestParameterHandler = $requestParameterHandler;



    }
    public function addFlash($eventName){

        $this->requestParameterHandler;
        $request = $this->requestParameterHandler->getRequest();
        $resourceName=$this->requestParameterHandler->getResourceName();
        if($eventName =='resource.post.create'){
            $request->getSession()->getFlashBag()->add('success','The '.$resourceName.' has been successfully created');
        }
        elseif($eventName =='resource.post.update'){
            $request->getSession()->getFlashBag()->add('success','Your changes has been successfully saved!');
        }
        elseif($eventName =='resource.post.delete'){
            $request->getSession()->getFlashBag()->add('success','The '.$resourceName.' has been successfully deleted');
        }


    }

}