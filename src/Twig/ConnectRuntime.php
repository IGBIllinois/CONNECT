<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Twig;

use App\Entity\Person;
use App\Service\HistoricityManager;
use Doctrine\Common\Collections\Collection;
use Twig\Extension\RuntimeExtensionInterface;

class ConnectRuntime implements RuntimeExtensionInterface
{
    private HistoricityManager $historicityManager;

    public function __construct(HistoricityManager $historicityManager)
    {
        $this->historicityManager = $historicityManager;
    }

    public function getCurrent(Collection $collection): Collection
    {
        return $this->historicityManager->getCurrentEntities($collection);
    }

    public function getRoleName(string $rawRole): string
    {
        $roleNames = array_flip(Person::USER_ROLES);
        if(key_exists($rawRole, $roleNames)){
            return $roleNames[$rawRole];
        } else {
            return $rawRole;
        }
    }
}