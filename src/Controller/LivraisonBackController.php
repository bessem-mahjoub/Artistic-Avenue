<?php

namespace App\Controller;

use App\Entity\Livraison;
use App\Form\Livraison1Type;
use App\Repository\LivraisonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/livraison/back')]
class LivraisonBackController extends AbstractController
{
    #[Route('/', name: 'app_livraison_back_index', methods: ['GET'])]
    public function index(LivraisonRepository $livraisonRepository): Response
    {
        return $this->render('livraison_back/index.html.twig', [
            'livraisons' => $livraisonRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_livraison_back_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LivraisonRepository $livraisonRepository): Response
    {
        $livraison = new Livraison();
        $form = $this->createForm(Livraison1Type::class, $livraison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $livraisonRepository->save($livraison, true);

            return $this->redirectToRoute('app_livraison_back_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('livraison_back/new.html.twig', [
            'livraison' => $livraison,
            'form' => $form,
        ]);
    }

    #[Route('/{idLiv}', name: 'app_livraison_back_show', methods: ['GET'])]
    public function show(Livraison $livraison): Response
    {
        return $this->render('livraison_back/show.html.twig', [
            'livraison' => $livraison,
        ]);
    }

    #[Route('/{idLiv}/edit', name: 'app_livraison_back_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Livraison $livraison, LivraisonRepository $livraisonRepository): Response
    {
        $form = $this->createForm(Livraison1Type::class, $livraison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $livraisonRepository->save($livraison, true);

            return $this->redirectToRoute('app_livraison_back_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('livraison_back/edit.html.twig', [
            'livraison' => $livraison,
            'form' => $form,
        ]);
    }

    #[Route('/{idLiv}', name: 'app_livraison_back_delete', methods: ['POST'])]
    public function delete(Request $request, Livraison $livraison, LivraisonRepository $livraisonRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$livraison->getIdLiv(), $request->request->get('_token'))) {
            $livraisonRepository->remove($livraison, true);
        }

        return $this->redirectToRoute('app_livraison_back_index', [], Response::HTTP_SEE_OTHER);
    }
}
