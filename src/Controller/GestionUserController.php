<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\GestionUserFormType;
use App\Form\ParticipantProfileFormType;
use App\Form\SitesFormType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/gestionUser/')]
class GestionUserController extends AbstractController
{
    #[Route(path: 'index', name: 'indexGestionUser', methods:['GET'])]
    public function profile(EntityManagerInterface $em): Response
    {
       $participantRepository = $em->getRepository(Participant::class);

       $users= $participantRepository->findUser();

        return $this->render('admin/gestionUser.html.twig', [
            'users' => $users,
        ]);
    }

   #[Route(path: 'delete/{id}', name: 'deleteParticipant', methods: ['GET'])]
    public function delete($id, EntityManagerInterface $em): Response
    {
        $participant = $em->find(Participant::class, $id);

        if ($participant === null) {
        } else {
            $em->remove($participant);
            $em->flush();
            return $this->redirectToRoute('indexGestionUser');
        }

        return $this->render('admin/gestionUser.html.twig');
    }

    #[Route(path: 'update/{id}', name: 'updateGestionUser', methods: ['GET','POST'])]
    public function update($id, EntityManagerInterface $em,Request $request): Response
    {
        $participant = $em->find(Participant::class, $id);

        //Création du formulaire
        $formParticipant = $this->createForm(GestionUserFormType::class, $participant);
        $formParticipant->handleRequest($request);

        //Vérification du formulaire
        if ($formParticipant->isSubmitted() && $formParticipant->isValid()) {

            //Insertion du Participant en BDD (Base de donnée)
            $em->persist($participant);
            $em->flush();

            $this->addFlash('success', 'DONE !');
            // Redirection vers la liste
            return $this->redirectToRoute('indexGestionUser');
        }

        if ($participant === null) {
            //  pas trouvée
            return $this->render('admin/gestionUser.html.twig', [
                'participant' => $participant,]);
        } else {
            // trouvée
        }

        return $this->render('admin/updateUser.html.twig', [
            'participant' => $participant,
            'formParticipant' => $formParticipant->createView(),
        ]);
    }
}