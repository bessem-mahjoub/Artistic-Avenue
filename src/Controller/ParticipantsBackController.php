<?php

namespace App\Controller;

use App\Entity\Participants;
use App\Form\Participants1Type;
use App\Repository\ParticipantsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/participants/back')]
class ParticipantsBackController extends AbstractController
{
    #[Route('/', name: 'app_participants_back_index', methods: ['GET'])]
    public function index(Request $request, ParticipantsRepository $participantsRepository)
    {
        $searchTerm = $request->query->get('search');
        $participants = [];

        if ($searchTerm) {
            // Utilisez $searchTerm pour filtrer les participants en fonction du terme de recherche
            // Par exemple, vous pouvez utiliser une méthode de recherche dans votre entité Participant
            $participants = $participantsRepository->searchParticipants($searchTerm);
        } else {
            $participants = $participantsRepository->findAll();
        }

        return $this->render('participants_back/index.html.twig', [
            'participants' => $participants,
        ]);
    }
    #[Route('/new', name: 'app_participants_back_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ParticipantsRepository $participantsRepository): Response
    {
        $participant = new Participants();
        $form = $this->createForm(Participants1Type::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participantsRepository->save($participant, true);

            return $this->redirectToRoute('app_participants_back_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participants_back/new.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participants_back_show', methods: ['GET'])]
    public function show(Participants $participant): Response
    {
        return $this->render('participants_back/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_participants_back_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participants $participant, ParticipantsRepository $participantsRepository): Response
    {
        $form = $this->createForm(Participants1Type::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participantsRepository->save($participant, true);

            return $this->redirectToRoute('app_participants_back_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participants_back/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participants_back_delete', methods: ['POST'])]
    public function delete(Request $request, Participants $participant, ParticipantsRepository $participantsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->request->get('_token'))) {
            $participantsRepository->remove($participant, true);
        }

        return $this->redirectToRoute('app_participants_back_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/my-participation', name: 'app_participants_back_my_participation', methods: ['GET'])]
    public function myParticipation(Participants $participant): Response
    {
        // Récupérer les événements liés au participant
        $events = $participant->getEvent();
    
        return $this->render('participants_back/my_participation.html.twig', [
            'events' => $events,
        ]);
    }
}
