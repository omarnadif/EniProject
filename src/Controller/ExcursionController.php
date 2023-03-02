<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\CreerSortieFormType;
use App\Form\UpdateSortieFormType;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route(path: 'excursion/')]
class ExcursionController extends AbstractController
{
    #[Route(path: 'index', name: 'indexExcursion', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em, SortieRepository $sortieRepository, LieuRepository $lieuRepository, ParticipantRepository $participantRepository): \Symfony\Component\HttpFoundation\Response
    {
        $sorties = $sortieRepository->findAll();

        $sortie = new Sortie();

        $searchTerm = $request->request->get('searchTerm');
        if ($searchTerm) {
            $participant = $participantRepository->search($searchTerm);
        } else {
            $participant = $participantRepository->findAll();
        }

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
        $participantOrganises = [];
        foreach ($sorties as $sortie) {
            $participantOrganises[] = $sortie->getParticipantOrganise();
        }

        return $this->render('excursions/indexExcursion.html.twig', [
            'sorties' => $sorties,
            'lieu' => $lieu,
            'participant' => $participant,
            'participantOrganises'=>$participantOrganises]);
    }

    #[Route(path: 's', name: 'selectExcursion', methods: ['GET'])]
    public function SelectExcursion(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('excursions/selectExcursion.html.twig');
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

    #[Route('createExcursion', name: 'createExcursion', methods: ['GET', 'POST'])]
    public function createExcursion(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $sortie = new Sortie();

        //Création du formulaire
        $form = $this->createForm(CreerSortieFormType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $organisateur = $this->getUser();
            $sortie->setParticipantOrganise($organisateur);

            $sortie = new Sortie();

            // Récupération des données du formulaire
            $sortie = $form->getData();

            // Récupération de l'image de la sortie du formulaire
            $sortieUserPicture = $form->get('sortieUploadPicture')->getData();

            // Vérification si une image de la Sortie a été téléchargée
            if ($sortieUserPicture) {

                // Génération d'un nom de fichier unique pour éviter les conflits
                $originalFilename = pathinfo($sortieUserPicture->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$sortieUserPicture->guessExtension();

                // Déplacement de l'image téléchargée dans le répertoire de stockage définis dans service.yaml (dans sortie_ImageUpload_directory)
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

            // Redirection vers la liste
            return $this->redirectToRoute('indexExcursion');
        }

        return $this->render('excursions/EditeExcursion.html.twig', [
            'excursionForm' => $form->createView(),
        ]);
    }

    #[Route('deleteExcursion/{id}', name: 'deleteExcursion', methods: ['GET'])]
    public function deleteExcursion($id, EntityManagerInterface $entityManager): Response
    {
        $sortie = $entityManager->find(Sortie::class, $id);

        if ($sortie === null) {
            // la sortie n'a pas été trouvée
        } else {
            // Suppression de l'image de la sortie, s'il en a une
            $lieuUserPicture = $sortie->getSortieImageUpload();
            if ($lieuUserPicture) {
                $imagePath = $this->getParameter('sortie_ImageUpload_directory') . '/' . $lieuUserPicture;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            // Supprimé dans l'entité Sortie
            $entityManager->remove($sortie);
            $entityManager->flush();
        }
        return $this->redirectToRoute('indexExcursion');
    }

    #[Route('inscriptionExcursion/{id}', name: 'inscriptionExcursion', methods: ['GET', 'POST'])]
    public function addParticipantEvent($id, Request $request, EntityManagerInterface $em, SortieRepository $sortieRepository, LieuRepository $lieuRepository, ParticipantRepository $participantRepository, SluggerInterface $slugger): Response
    {
        // Obtenez l'objet Sortie en fonction de l'ID de la sortie à partir de la base de données.
        $sortie = $sortieRepository->find($id);

        // Obtenez l'objet Participant en fonction de l'ID du participant à partir de la base de données.
        // Récupérez l'objet User à partir de la session.
        /* @var Participant $participant*/
        $participant = $this->getUser();

        // Ajoutez le participant à la sortie.
        $sortie->addParticipant($participant);
        $participant->addSortie($sortie);
        $em->flush();

        $searchTerm = $request->request->get('searchTerm');
        if ($searchTerm) {
            $participants = $participantRepository->search($searchTerm);
        } else {
            $participants = $participantRepository->findAll();
        }

        if ($searchTerm) {
            $lieux = $lieuRepository->search($searchTerm);
        } else {
            $lieux = $lieuRepository->findAll();
        }
        if ($searchTerm) {
            $sorties = $sortieRepository->search($searchTerm);
        } else {
            $sorties = $sortieRepository->findAll();
        }

        return $this->render('excursions/indexExcursion.html.twig', [
            'sorties' => $sorties,
            'lieux' => $lieux,
            'participants' => $participants,
        ]);
    }
}