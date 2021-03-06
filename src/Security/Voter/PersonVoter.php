<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Security\Voter;

use App\Entity\Person;
use App\Entity\ThemeAffiliation;
use App\Service\HistoricityManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class PersonVoter extends Voter
{
    public const EDIT = 'PERSON_EDIT';
    public const VIEW = 'PERSON_VIEW';
    public const ADD = 'PERSON_ADD';
    public const EDIT_HISTORY = 'PERSON_EDIT_HISTORY';

    public function __construct(private Security $security, private HistoricityManager $historicityManager) {}

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return (in_array($attribute, [self::EDIT, self::VIEW])
                && $subject instanceof Person)
               || $attribute === self::ADD
               || $attribute === self::EDIT_HISTORY;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var Person $user */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Admins can do anything
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        return match ($attribute) {
            self::EDIT => $this->security->isGranted('ROLE_HUMAN_RESOURCES')
                          || $this->isEditorForPersonsTheme($user, $subject), // todo who else can edit?
            self::VIEW => true,
            self::ADD => $this->security->isGranted('ROLE_HUMAN_RESOURCES'), // todo who else can add?
            default => false,
        };
    }

    private function isEditorForPersonsTheme(Person $user, Person $subject): bool
    {
        // todo this needs to be tested
        $userThemes = array_map(function (ThemeAffiliation $themeAffiliation) {
            return $themeAffiliation->getTheme();
        }, $this->historicityManager->getCurrentEntities($subject->getThemeAffiliations())->toArray());
        $adminThemes = array_map(function (ThemeAffiliation $themeAffiliation) {
            return $themeAffiliation->getTheme();
        }, $user->getThemeAdminThemeAffiliations()->toArray());
        if (count(array_intersect($userThemes, $adminThemes)) > 0) {
            return true;
        }
        $labManagerThemes = array_map(function (ThemeAffiliation $themeAffiliation) {
            return $themeAffiliation->getTheme();
        }, $user->getLabManagerThemeAffiliations()->toArray());
        if (count(array_intersect($userThemes, $labManagerThemes)) > 0) {
            return true;
        }
        return false;
    }
}
