<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route(path: '', name: 'home', methods: ['GET'])]

    public function home(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('home/home.html.twig');
    }

    #[Route(path: 'about', name: 'about', methods: ['GET'])]
    public function about(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('home/about.html.twig');
    }

    #[Route(path: 'legal-stuff', name: 'legal_stuff', methods: ['GET'])]
    public function legalStuff(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('home/legal_stuff.html.twig');
    }


}
