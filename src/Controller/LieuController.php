<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuFormType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route(path: '/admin/lieu/')]
class LieuController extends AbstractController
{
    #[Route(path: 'indexLieu', name: 'indexLieu', methods:['GET','POST'])]
    public function indexLieu(Request $request, EntityManagerInterface $em, LieuRepository $lieuRepository): Response
    {

        $searchTerm = $request->request->get('searchTerm');
        if ($searchTerm) {
            $lieu = $lieuRepository->search($searchTerm);
        } else {
            $lieu = $lieuRepository->findAll();
        }
        return $this->render('lieu/indexLieu.html.twig', [
            'lieu' => $lieu,
        ]);
    }


    #[Route('createLieu', name: 'createLieu', methods: ['GET', 'POST'])]
    public function createLieu(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Création
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

                // Déplacement de l'image téléchargée dans le répertoire de stockage définis dans service.yaml (dans participant_image_directory)
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

        return $this->render('lieu/createLieu.html.twig', [
            'lieu' => $lieu,
            'lieuForm' => $formLieu->createView(),
        ]);
    }

    #[Route(path: 'updateLieu/{id}', name: 'updateLieu', methods: ['GET','POST'])]
    public function updateLieu($id, EntityManagerInterface $em,Request $request): Response
    {
        $lieu = $em->find(Lieu::class, $id);

        //Création du formulaire
        $formLieu = $this->createForm(LieuFormType::class, $lieu);
        $formLieu->handleRequest($request);

        //Vérification du formulaire
        if ($formLieu->isSubmitted() && $formLieu->isValid()) {

            //Insertion du Lieu en BDD (Base de donnée)
            $em->persist($lieu);
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
            // le lieu a été trouvée
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

            // Suppression de l'image de profil de l'utilisateur, s'il en a une
            $lieuUserPicture = $lieu->getLieuImageUpload();
            if ($lieuUserPicture) {
                $imagePath = $this->getParameter('lieu_ImageUpload_directory') . '/' . $lieuUserPicture;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Supprimer l'entité Lieu
            $em->remove($lieu);
            $em->flush();
        }

        return $this->redirectToRoute('indexLieu');
    }
}
