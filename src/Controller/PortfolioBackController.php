<?php

namespace App\Controller;

use App\Entity\Portfolio;
use App\Form\Portfolio1Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/portfolio/back')]
class PortfolioBackController extends AbstractController
{
    #[Route('/', name: 'app_portfolio_back_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $query = $request->query->get('q');
        $sort = $request->query->get('sort', 'id');
        $direction = $request->query->get('dir', 'asc');
        $filter = $request->query->get('filter');
    
        $qb = $entityManager->getRepository(Portfolio::class)
            ->createQueryBuilder('p')
            ->andWhere('p.titre LIKE :query OR p.description LIKE :query')
            ->setParameter('query', "%{$query}%");
    
        if ($filter === 'popular') {
            $qb->orderBy('p.likes', 'desc');
        } else {
            $qb->orderBy("p.{$sort}", $direction);
        }
    
        $portfolios = $qb->getQuery()->getResult();
    
        return $this->render('portfolio_back/index.html.twig', [
            'portfolios' => $portfolios,
            'query' => $query,
            'sort' => $sort,
            'direction' => $direction,
            'filter' => $filter,
        ]);
    }
    

    #[Route('/new', name: 'app_portfolio_back_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $portfolio = new Portfolio();
        $form = $this->createForm(Portfolio1Type::class, $portfolio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($portfolio);
            $entityManager->flush();

            return $this->redirectToRoute('app_portfolio_back_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('portfolio_back/new.html.twig', [
            'portfolio' => $portfolio,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_portfolio_back_show', methods: ['GET'])]
    public function show(Portfolio $portfolio): Response
    {
        return $this->render('portfolio_back/show.html.twig', [
            'portfolio' => $portfolio,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_portfolio_back_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Portfolio $portfolio, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Portfolio1Type::class, $portfolio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_portfolio_back_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('portfolio_back/edit.html.twig', [
            'portfolio' => $portfolio,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_portfolio_back_delete', methods: ['POST'])]
    public function delete(Request $request, Portfolio $portfolio, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$portfolio->getId(), $request->request->get('_token'))) {
            $entityManager->remove($portfolio);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_portfolio_back_index', [], Response::HTTP_SEE_OTHER);
    }
}
