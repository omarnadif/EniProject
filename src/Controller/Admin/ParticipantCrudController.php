<?php

namespace App\Controller\Admin;

use App\Entity\Participant;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ParticipantCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Participant::class;
    }
}