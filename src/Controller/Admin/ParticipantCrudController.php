<?php

namespace App\Controller\Admin;

use App\Entity\Participant;

class ParticipantCrudController
{
    public static function getEntityFqcn(): string
    {
        return Participant::class;
    }
}