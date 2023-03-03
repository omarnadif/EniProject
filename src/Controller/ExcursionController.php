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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route(path: 'excursion/')]
class ExcursionController extends AbstractController
{
    #[Route(path: 'indexExcursion', name: 'indexExcursion', methods: ['GET'])]
    public function indexExcursion(Request $request, SortieRepository $sortieRepository, LieuRepository $lieuRepository, ParticipantRepository $participantRepository): \Symfony\Component\HttpFoundation\Response
    {
        // Récupère toutes les sorties avec "findAll()" à partir de la base de données.
        $sorties = $sortieRepository->findAll();

        // Initialise un nouvel objet Sortie.
        $sortie = new Sortie();

        // Si "searchTerm" est présent, effectue une recherche pour les participants.
        // Sinon, récupère tous les participants.
        $searchTerm = $request->request->get('searchTerm');
        if ($searchTerm) {
            $participants = $participantRepository->search($searchTerm);
        } else {
            $participants = $participantRepository->findAll();
        }

        // Si "searchTerm" est présent, effectue une recherche pour les lieux.
        // Sinon, récupère tous les lieux.
        if ($searchTerm) {
            $lieu = $lieuRepository->search($searchTerm);
        } else {
            $lieu = $lieuRepository->findAll();
        }

        // Si "searchTerm" est présent, effectue une recherche pour les sorties.
        // Sinon, récupère toutes les sorties.
        if ($searchTerm) {
            $sortie = $sortieRepository->search($searchTerm);
        } else {
            $sortie = $sortieRepository->findAll();
        }

        // Initialise un tableau vide pour les participants organisant chaque sortie.
        $participantOrganises = [];

        // Pour chaque sortie, ajoute le participant organisant à $participantOrganises.
        foreach ($sorties as $sortie) {
            $participantOrganises[] = $sortie->getParticipantOrganise();
        }

        // Rend la vue "indexExcursion.html.twig" avec les données récupérées.
        return $this->render('excursions/indexExcursion.html.twig', [
            'sorties' => $sorties,
            'lieu' => $lieu,
            'participants' => $participants,
            'participantOrganises'=>$participantOrganises]);
    }

    #[Route(path: 's', name: 'selectExcursion', methods: ['GET'])]
    public function SelectExcursion(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('excursions/selectExcursion.html.twig');
    }


    #[Route(path: 'updateExcursion/{id}', name: 'updateExcursion', methods: ['GET', 'POST'])]
    public function updateExcursion($id, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, Filesystem $filesystem): \Symfony\Component\HttpFoundation\Response
    {
        // Récupération de l'excursion correspondant à l'identifiant fourni
        $sortie = $entityManager->getRepository(Sortie::class)->find($id);

        // Vérification que la sortie existe
        if (!$sortie) {
            throw $this->createNotFoundException('La sortie n\'existe pas.');
        }

        //Création du formulaire pour la mise à jour de l'excursion
        $form = $this->createForm(UpdateSortieFormType::class, $sortie);
        $form->handleRequest($request);

        // Vérification que le formulaire a été soumis et que les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {
            $organisateur = $this->getUser();
            $sortie->setParticipantOrganise($organisateur);

            // Récupération de l'image de profil du formulaire
            $sortieUserPicture = $form->get('sortieUploadPicture')->getData();

            // Vérification s'il y a une image dans la sortie a été téléchargée
            if ($sortieUserPicture) {

                // Génération d'un nom de fichier unique pour éviter les conflits
                $originalFilename = pathinfo($sortieUserPicture->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$sortieUserPicture->guessExtension();

                // Déplacement de l'image téléchargée dans le répertoire de stockage défini dans service.yaml (dans sortie_ImageUpload_directory)
                try {
                    $sortieUserPicture->move(
                        $this->getParameter('sortie_ImageUpload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gestion d'une exception si nécessaire
                }

                // Suppression de l'ancienne image, s'il en existe une
                $oldFilename = $sortie->getSortieImageUpload();
                if ($oldFilename) {
                    $filesystem->remove($this->getParameter('sortie_ImageUpload_directory').'/'.$oldFilename);
                }

                // Ajout du nom du fichier d'image dans l'entité Sortie
                if ($sortie instanceof Sortie) {
                    $sortie->setSortieImageUpload($newFilename);
                }
            }
            // Enregistrement des modifications dans la base de données
            $entityManager->flush();

            // Ajout d'un message flash pour indiquer que la sortie a été modifiée avec succès
            $this->addFlash('success', 'La sortie a été modifiée avec succès.');
            return $this->redirectToRoute('indexExcursion', ['id' => $sortie->getId()]);
        }

        // Rendu de la vue pour la mise à jour de l'excursion avec le formulaire créé précédemment
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

                // Déplacement de l'image téléchargée dans le répertoire de stockage défini dans service.yaml (dans sortie_ImageUpload_directory)
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
        // Récupération de l'objet Sortie correspondant à l'ID de la sortie depuis la base de données
        $sortie = $sortieRepository->find($id);

        // Récupération de l'objet Participant correspondant à l'ID du participant depuis la base de données
        // Récupération de l'objet User depuis la session
        /* @var Participant $participant*/
        $participant = $this->getUser();

        // Ajout du participant à la sortie
        $sortie->addParticipant($participant);
        $participant->addSortie($sortie);
        $em->flush();

        // Recherche d'un terme de recherche dans la base de données et récupération des résultats pour les afficher
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

        // Renvoie des données nécessaires à l'affichage de la page d'accueil des excursions
        return $this->render('excursions/indexExcursion.html.twig', [
            'sorties' => $sorties,
            'lieux' => $lieux,
            'participants' => $participants,
        ]);
    }
}