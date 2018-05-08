<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Security;

use EscapeHither\CrudManagerBundle\Entity\ResourceInterface;
use EscapeHither\SecurityManagerBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use EscapeHither\CrudManagerBundle\Services\RequestParameterHandler;

/**
 * Resource Voter
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class ResourceVoter extends Voter
{

    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const BASE_ROLE = 'ROLE_MANAGE';
    private $decisionManager;
    private $requestParameterHandler;

    /**
     * The resoure voter constructor
     *
     * @param AccessDecisionManagerInterface $decisionManager
     * @param RequestParameterHandler        $requestParameterHandler
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager, RequestParameterHandler $requestParameterHandler)
    {
        $this->decisionManager = $decisionManager;
        $this->requestParameterHandler = $requestParameterHandler;
    }

    /**
     * {@inheritDoc}
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE))) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof ResourceInterface) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }
        // ROLE_SUPER_ADMIN can do anything! The power!
        $resourceName = $this->requestParameterHandler->getResourceName();
        $resourceUpperRole = self::BASE_ROLE.'_'.strtoupper($resourceName);

        if ($this->decisionManager->decide($token, array($resourceUpperRole))) {
            return true;
        }

        // you know $subject is a resource object, thanks to supports
        /** @var ResourceInterface $resource */
        $resource = $subject;

        if (self::VIEW  === $attribute) {
            return $this->canView($resource, $user, $token);
        }

        if (self::EDIT  === $attribute || self::DELETE === $attribute) {
            return $this->canEdit($resource, $user, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * Check if the user can view the content
     *
     * @param ResourceInterface $resource
     * @param User              $user
     * @param TokenInterface    $token
     *
     * @return boolean
     */
    private function canView(ResourceInterface $resource, User $user, TokenInterface $token)
    {
        // if they can edit, they can view
        if ($this->canEdit($resource, $user, $token)) {
            return true;
        }
        /// this assumes that the data object has a getOwner() method
        // to get the entity of the user who owns this data object
        $resourceName = $this->requestParameterHandler->getResourceName();
        $role = 'ROLE_'.strtoupper($resourceName).'_SHOW';

        if ($this->decisionManager->decide($token, array($role))) {
            return true;
        }

        return $user === $resource->getAuthor();
    }

    /**
     * Check if the user can edit the content
     *
     * @param ResourceInterface $resource
     * @param User              $user
     * @param TokenInterface    $token
     *
     * @return boolean
     */
    private function canEdit(ResourceInterface $resource, User $user, TokenInterface $token)
    {
        // this assumes that the data object has a getOwner() method
        // to get the entity of the user who owns this data object
        $resourceName = $this->requestParameterHandler->getResourceName();
        $role = 'ROLE_'.strtoupper($resourceName).'_EDIT';

        if ($this->decisionManager->decide($token, array($role))) {
            return true;
        }

        return $user === $resource->getAuthor();
    }
}
