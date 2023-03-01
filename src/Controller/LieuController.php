<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Ville;
use App\Form\LieuFormType;
use App\Form\VilleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route(path: '/admin/lieu/')]
class LieuController extends AbstractController
{
    #[Route(path: 'index', name: 'indexlieu', methods:['GET'])]
    public function profile(EntityManagerInterface $em): Response
    {

        $villes = $em->getRepository(Ville::class)->findAll();

        return $this->render('lieu/indexLieu.html.twig', [
            'villes' => $villes,
        ]);
    }

    #[Route('create', name: 'createLieu', methods: ['GET', 'POST'])]
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

            //Insertion du Participant en BDD (Base de donnée)
            $entityManager->persist($lieu);
            $entityManager->flush();

            // Redirection vers la liste
            return $this->redirectToRoute('indexlieu');
        }

        return $this->render('lieu/createLieu.html.twig', [
            'lieu' => $lieu,
            'lieuForm' => $formLieu->createView(),
        ]);
    }

    #[Route(path: 'update/{id}', name: 'updateVille_Lieu', methods: ['GET','POST'])]
    public function update($id, EntityManagerInterface $em,Request $request): Response
    {
        $ville = $em->find(Ville::class, $id);

        //Création du formulaire
        $formVille = $this->createForm(VilleFormType::class, $ville);
        $formVille->handleRequest($request);

        //Vérification du formulaire
        if ($formVille->isSubmitted() && $formVille->isValid()) {


            //Insertion du Participant en BDD (Base de donnée)
            $em->persist($ville);
            $em->flush();

            $this->addFlash('success', 'La ville a bien été modifier !');

            // Redirection vers la liste
            return $this->redirectToRoute('indexlieu');
        }

        if ($ville === null) {
            // la ville n'a pas été trouvée
            return $this->render('lieu/indexLieu.html.twig', [
                'ville' => $ville,]);
        } else {
            // la ville a été trouvée
        }

        return $this->render('ville/updateVille.html.twig', [
            'ville' => $ville,
            'formVille' => $formVille->createView(),
        ]);
    }

    #[Route(path: 'delete/{id}', name: 'deleteVille_Lieu', methods: ['GET'])]
    public function deleteLieu($id, EntityManagerInterface $em, Filesystem $filesystem): Response
    {
        $lieu = $em->find(Lieu::class, $id);

        if ($lieu === null) {
            // le lieu n'a pas été trouvé
        } else {
            // Suppression de l'image stockée dans le projet ****à tester******
            $filename = $lieu->getLieuImageUpload();
            if ($filename !== null) {
                $filesystem->remove($this->getParameter('lieu_ImageUpload_directory') . '/' . $filename);
            }

            // Suppression du nom de l'image stockée en base de données
            $lieu->setLieuImageUpload(null);

            $em->remove($lieu);
            $em->flush();

            return $this->redirectToRoute('indexlieu');
        }

        return $this->render('lieu/indexLieu.html.twig', [
            'lieu' => $lieu,
        ]);
    }
}
