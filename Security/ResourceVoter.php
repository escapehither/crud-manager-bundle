<?php

/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 26/03/17
 * Time: 19:48
 */
namespace EscapeHither\CrudManagerBundle\Security;
use EscapeHither\CrudManagerBundle\Entity\ResourceInterface;
use EscapeHither\SecurityManagerBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use EscapeHither\CrudManagerBundle\Services\RequestParameterHandler;

class ResourceVoter extends Voter{
// these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const BASE_ROLE = 'ROLE_MANAGE';
    private $decisionManager;
    private $requestParameterHandler;

    public function __construct(AccessDecisionManagerInterface $decisionManager,RequestParameterHandler $requestParameterHandler)
    {
        $this->decisionManager = $decisionManager;
        $this->requestParameterHandler = $requestParameterHandler;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::EDIT,self::DELETE))) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof ResourceInterface) {
            return false;
        }

        return true;
    }

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

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($resource, $user, $token);
            case self::EDIT:
                return $this->canEdit($resource, $user,$token);
            case self::DELETE:
                return $this->canEdit($resource, $user,$token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(ResourceInterface $resource, User $user, $token)
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

    private function canEdit(ResourceInterface $resource, User $user,$token)
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