<?php

namespace App\Security;

use App\Entity\Trick;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TrickVoter extends Voter
{
	private $security;

	const EDIT = 'edit';
	const DELETE = 'delete';

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EDIT, self::DELETE])) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof Trick) {
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

        // you know $subject is a Post object, thanks to supports
        /** @var Trick $trick */
        $trick = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($trick, $user);
            case self::DELETE:
                return $this->canDelete($trick, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(Trick $trick, User $user)
    {
        // if they can delete, they can edit
        if ($this->canDelete($trick, $user)) {
            return true;
        }

        return false;
    }

    private function canDelete(Trick $trick, User $user)
    {

    	// ROLE_ADMIN can do everything
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $user === $trick->getUser(); // true if the user is the owner of the trick
    }
}