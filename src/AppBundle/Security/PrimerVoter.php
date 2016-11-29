<?php

namespace AppBundle\Security;

use AppBundle\Entity\Primer;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PrimerVoter extends Voter
{
    const VIEW = 'PRIMER_VIEW';
    const EDIT = 'PRIMER_EDIT';
    const DELETE = 'PRIMER_DELETE';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        // If the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE))) {
            return false;
        }

        // Only vote for Project object
        if (!$subject instanceof Primer) {
            return false;
        }

        // Else it's a supported attribute and a supported object
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // If user is not log in, deny access
        if (!$user instanceof User) {
            return false;
        }

        // If user is a SuperAdmin user
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }

        // In all other case
        $primer = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($primer, $user);
            case self::EDIT:
                return $this->canEdit($primer, $user);
            case self::DELETE:
                return $this->canDelete($primer, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Primer $primer, User $user)
    {
        if ($this->canEdit($primer, $user)) {
            return true;
        }

        return false;
    }

    private function canEdit(Primer $primer, User $user)
    {
        if ($this->canDelete($primer, $user)) {
            return true;
        }

        return false;
    }

    private function canDelete(Primer $primer, User $user)
    {
        if ($primer->getTeam()->isMember($user)) {
            return true;
        }

        return false;
    }
}