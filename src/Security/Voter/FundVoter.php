<?php

namespace App\Security\Voter;

use App\Entity\Fund;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class FundVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['FUND_EDIT', 'FUND_DELETE'])
            && $subject instanceof Fund;
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
            case 'FUND_EDIT':
            case 'FUND_DELETE':
                if ($subject->getUser() === $user) {
                    return true;
                }
                
                break;
        }

        return false;
    }
}
