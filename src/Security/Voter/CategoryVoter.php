<?php

namespace App\Security\Voter;

use App\Entity\Category;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CategoryVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['CATEGORY_EDIT', 'CATEGORY_DELETE'])
            && $subject instanceof Category;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'CATEGORY_EDIT':
            case 'CATEGORY_DELETE':
                if ($subject->getUser() === $user) {
                    return true;
                }
                
                break;
        }

        return false;
    }
}
