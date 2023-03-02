<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\CreerSortieFormType;
use App\Form\UpdateSortieFormType;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route(path: 'excursion/')]
class ExcursionController extends AbstractController
{
    #[Route(path: 'index', name: 'indexExcursion', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em, SortieRepository $sortieRepository, LieuRepository $lieuRepository): \Symfony\Component\HttpFoundation\Response
    {
        $sortie = new Sortie();
        $searchTerm = $request->request->get('searchTerm');
        if ($searchTerm) {
            $lieu = $lieuRepository->search($searchTerm);
        } else {
            $lieu = $lieuRepository->findAll();
        }
        if ($searchTerm) {
            $sortie = $sortieRepository->search($searchTerm);
        } else {
            $sortie = $sortieRepository->findAll();
        }

        return $this->render('excursions/indexExcursion.html.twig', [
            'sortie' => $sortie,
            'lieu' => $lieu,
        ]);
    }

    #[Route(path: 's', name: 'selectExcursion', methods: ['GET'])]
    public function SelectExcursion(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('excursions/excursion.html.twig');
    }


    #[Route(path: 'updateExcursion', name: 'updateExcursion', methods: ['GET'])]
    public function updateExcursion(Request $request, EntityManagerInterface $entityManager): \Symfony\Component\HttpFoundation\Response
    {
        $sortie = new Sortie();
        //Création du formulaire
        $form = $this->createForm(UpdateSortieFormType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $organisateur = $this->getUser();
            $sortie->setParticipantOrganise($organisateur);
            $entityManager->persist($sortie);
            $entityManager->flush();
        }
        return $this->render('excursions/updateExcursion.html.twig', [
            'excursionForm' => $form->createView(),
        ]);
    }

    #[Route('editExcursion', name: 'editExcursion', methods: ['GET', 'POST'])]
    public function excursionForm(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $sortie = new Sortie();

        //Création du formulaire
        $form = $this->createForm(CreerSortieFormType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $organisateur = $this->getUser();
            $sortie->setParticipantOrganise($organisateur);

            // Récupération des données du formulaire
            $sortie = $form->getData();

            // Récupération de l'image de la sortie du formulaire
            $sortieUserPicture = $form->get('sortieUploadPicture')->getData();

            // Vérification si une image de profil a été téléchargée
            if ($sortieUserPicture) {

                // Génération d'un nom de fichier unique pour éviter les conflits
                $originalFilename = pathinfo($sortieUserPicture->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$sortieUserPicture->guessExtension();

                // Déplacement de l'image téléchargée dans le répertoire de stockage définis dans service.yaml (dans participant_image_directory)
                try {
                    $sortieUserPicture->move(
                        $this->getParameter('sortie_ImageUpload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gestion d'une exception si nécessaire
                }

                // Ajout du nom du fichier d'image dans l'entité Sortie
                if ($sortie instanceof Sortie) {
                    $sortie->setSortieImageUpload($newFilename);
                }
            }

            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        return $this->render('excursions/EditeExcursion.html.twig', [
            'excursionForm' => $form->createView(),
        ]);
    }
}