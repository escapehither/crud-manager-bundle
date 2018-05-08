<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Services;

/**
 * The flash manager
 * Send flash message
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class FlashMessageManager
{
    protected $requestParameterHandler;
    const SUCCESS = 'success';

    /**
     * The flash manager constructor
     *
     * @param RequestParameterHandler $requestParameterHandler
     */
    public function __construct(RequestParameterHandler $requestParameterHandler)
    {
        $this->requestParameterHandler = $requestParameterHandler;
    }
    /**
     * Add flash according to the event
     *
     * @param string $eventName
     */
    public function addFlash($eventName)
    {

        $this->requestParameterHandler->build();
        $request = $this->requestParameterHandler->getRequest();
        $resourceName = $this->requestParameterHandler->getResourceName();

        switch ($eventName) {
            case 'resource.post.create':
                $request->getSession()->getFlashBag()->add(self::SUCCESS, 'The '.$resourceName.' has been successfully created');
                break;
            case 'resource.post.update':
                $request->getSession()->getFlashBag()->add(self::SUCCESS, 'Your changes has been successfully saved!');
                break;
            case 'resource.post.delete':
                $request->getSession()->getFlashBag()->add(self::SUCCESS, 'The '.$resourceName.' has been successfully deleted');
                break;
        }
    }
}
