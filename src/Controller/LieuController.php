<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuFormType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route(path: 'lieu/')]
class LieuController extends AbstractController
{
    #[Route(path: 'indexLieu', name: 'indexLieu', methods:['GET','POST'])]
    public function indexLieu(Request $request, EntityManagerInterface $em, LieuRepository $lieuRepository): Response
    {
        // Récupère le terme de recherche depuis la requête
        $searchTerm = $request->request->get('searchTerm');
        if ($searchTerm) {
            // Effectue une recherche dans la base de données en utilisant le terme de recherche
            $lieu = $lieuRepository->search($searchTerm);
        } else {
            // Récupère tous les lieux depuis la base de données
            $lieu = $lieuRepository->findAll();
        }

        // Retourne la réponse HTTP avec le résultat de la recherche ou tous les lieux
        return $this->render('lieu/indexLieu.html.twig', [
            'lieu' => $lieu,
        ]);
    }

    #[Route('createLieu', name: 'createLieu', methods: ['GET', 'POST'])]
    public function createLieu(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Création d'une instance de Lieu
        $lieu = new Lieu();

        //Création du formulaire
        $formLieu = $this->createForm(LieuFormType::class, $lieu);
        $formLieu->handleRequest($request);

        //Vérification du formulaire
        if ($formLieu->isSubmitted() && $formLieu->isValid()) {

            // Récupération des données du formulaire
            $lieu = $formLieu->getData();

            // Récupération de l'image de profil du formulaire
            $lieuUserPicture = $formLieu->get('lieuUploadPicture')->getData();

            // Vérification si une image de profil a été téléchargée
            if ($lieuUserPicture) {

                // Génération d'un nom de fichier unique pour éviter les conflits
                $originalFilename = pathinfo($lieuUserPicture->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$lieuUserPicture->guessExtension();

                // Déplacement de l'image téléchargée dans le répertoire de stockage défini dans service.yaml (dans participant_image_directory)
                try {
                    $lieuUserPicture->move(
                        $this->getParameter('lieu_ImageUpload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gestion d'une exception si nécessaire
                }

                // Ajout du nom du fichier d'image dans l'entité Lieu
                if ($lieu instanceof Lieu) {
                    $lieu->setLieuImageUpload($newFilename);
                }
            }

            //Insertion du Lieu en BDD (Base de donnée)
            $entityManager->persist($lieu);
            $entityManager->flush();

            // Redirection vers la liste
            return $this->redirectToRoute('indexLieu');
        }

        // Affichage de la vue pour créer un Lieu
        return $this->render('lieu/createLieu.html.twig', [
            'lieu' => $lieu,
            'lieuForm' => $formLieu->createView(),
        ]);
    }

    #[Route(path: 'updateLieu/{id}', name: 'updateLieu', methods: ['GET','POST'])]
    public function updateLieu($id, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, Filesystem $filesystem): \Symfony\Component\HttpFoundation\Response
    {
        $lieu = $em->find(Lieu::class, $id);

        //Création du formulaire
        $formLieu = $this->createForm(LieuFormType::class, $lieu);
        $formLieu->handleRequest($request);

        //Vérification du formulaire
        if ($formLieu->isSubmitted() && $formLieu->isValid()) {

            // Récupération de l'image de profil du formulaire
            $lieuUserPicture = $formLieu->get('lieuUploadPicture')->getData();

            // Vérification s'il y a une image dans la sortie a été téléchargée
            if ($lieuUserPicture) {

                // Génération d'un nom de fichier unique pour éviter les conflits
                $originalFilename = pathinfo($lieuUserPicture->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$lieuUserPicture->guessExtension();

                // Déplacement de l'image téléchargée dans le répertoire de stockage défini dans service.yaml (dans sortie_ImageUpload_directory)
                try {
                    $lieuUserPicture->move(
                        $this->getParameter('lieu_ImageUpload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gestion d'une exception si nécessaire
                }

                // Suppression de l'ancienne image, s'il en existe une
                $oldFilename = $lieu->getLieuImageUpload();
                if ($oldFilename) {
                    $filesystem->remove($this->getParameter('lieu_ImageUpload_directory').'/'.$oldFilename);
                }

                // Ajout du nom du fichier d'image dans l'entité Sortie
                if ($lieu instanceof Lieu) {
                    $lieu->setLieuImageUpload($newFilename);
                }
            }
            $em->flush();

            $this->addFlash('success', 'Le lieu a bien été modifié !');

            // Redirection vers la liste
            return $this->redirectToRoute('indexLieu');
        }

        if ($lieu === null) {
            // le lieu n'a pas été trouvée
            return $this->render('lieu/indexLieu.html.twig', [
                'lieu' => $lieu,]);
        } else {
            // le lieu a été trouvé
        }

        return $this->render('lieu/updateLieu.html.twig', [
            'lieu' => $lieu,
            'formLieu' => $formLieu->createView(),
        ]);
    }

    #[Route(path: 'deleteLieu/{id}', name: 'deleteLieu', methods: ['GET'])]
    public function deleteLieu($id, EntityManagerInterface $em): Response
    {
        $lieu = $em->find(Lieu::class, $id);

        if ($lieu === null) {
            // le lieu n'a pas été trouvé
        } else {

            // Suppression de l'image du lieu, s'il en a une
            $lieuUserPicture = $lieu->getLieuImageUpload();
            if ($lieuUserPicture) {
                $imagePath = $this->getParameter('lieu_ImageUpload_directory') . '/' . $lieuUserPicture;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        // Supprimé dans l'entité Lieu
        $em->remove($lieu);
        $em->flush();
        }
        return $this->redirectToRoute('indexLieu');
    }
}
