<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: 'excursion')]
class ExcursionController extends AbstractController
{
    #[Route(path: '', name: 'excursion', methods: ['GET'])]
    public function home(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('excursions/excursion.html.twig');
    }



}
