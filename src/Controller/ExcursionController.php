<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: 'excursion/')]
class ExcursionController extends AbstractController
{
    #[Route(path: '', name: 'selectExcursion', methods: ['GET'])]
    public function SelectExcursion(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('excursions/excursion.html.twig');
    }

    #[Route(path: 'edite', name: 'editeExcursion', methods: ['GET'])]
    public function editeExcursion(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('excursions/EditeExcursion.html.twig');
    }

    #[Route(path: 'update', name: 'updateExcursion', methods: ['GET'])]
    public function updateExcursion(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('excursions/updateExcursion.html.twig');
    }

}
