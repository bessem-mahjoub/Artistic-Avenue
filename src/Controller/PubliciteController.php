<?php

namespace App\Controller;

use App\Entity\Publicite;
use App\Form\PubliciteType;
use App\Repository\PubliciteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/publicite')]
class PubliciteController extends AbstractController
{
    #[Route('/', name: 'app_publicite_index', methods: ['GET'])]
    public function index(PubliciteRepository $publiciteRepository): Response
    {
        
        return $this->render('publicite/index.html.twig', [
            'publicites' => $publiciteRepository->findAll(),
          
        ]);
        
    }
    #[Route('/indexfront', name: 'app_publicite_indexfront', methods: ['GET'])]
public function indexfront(Request $request, PubliciteRepository $publiciteRepository): Response
{
    $currentPage = $request->query->getInt('currentPage', 1);
    $publicitesPerPage = 1;
    $offset = ($currentPage - 1) * $publicitesPerPage;
    
    $qb = $publiciteRepository->createQueryBuilder('pu');
    $totalPublicites = $qb->select('COUNT(pu.id)')->getQuery()->getSingleScalarResult();
    $totalPages = ceil($totalPublicites / $publicitesPerPage);

   
    return $this->render('publicite/indexfront.html.twig', [
        'publicites' => $publiciteRepository->findAll(),
        'currentPage' => $currentPage,
        'publicitesPerPage' => $publicitesPerPage,
        'totalPublicites' => $totalPublicites,
        'totalPages' => $totalPages,
    ]);
}


    #[Route('/new', name: 'app_publicite_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PubliciteRepository $publiciteRepository): Response
    {
        $publicite = new Publicite();
        $form = $this->createForm(PubliciteType::class, $publicite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $publiciteRepository->save($publicite, true);

            return $this->redirectToRoute('app_publicite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('publicite/new.html.twig', [
            'publicite' => $publicite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_publicite_show', methods: ['GET'])]
    public function show(Publicite $publicite): Response
    {
        return $this->render('publicite/show.html.twig', [
            'publicite' => $publicite,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_publicite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Publicite $publicite, PubliciteRepository $publiciteRepository): Response
    {
        $form = $this->createForm(PubliciteType::class, $publicite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $publiciteRepository->save($publicite, true);

            return $this->redirectToRoute('app_publicite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('publicite/edit.html.twig', [
            'publicite' => $publicite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_publicite_delete', methods: ['POST'])]
    public function delete(Request $request, Publicite $publicite, PubliciteRepository $publiciteRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$publicite->getId(), $request->request->get('_token'))) {
            $publiciteRepository->remove($publicite, true);
        }

        return $this->redirectToRoute('app_publicite_index', [], Response::HTTP_SEE_OTHER);
    }
}
