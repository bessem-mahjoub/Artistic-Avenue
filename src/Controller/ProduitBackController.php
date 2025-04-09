<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\Produit1Type;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twilio\Rest\Client;
use Exception;

#[Route('/produit/back')]
class ProduitBackController extends AbstractController
{
    #[Route('/', name: 'app_produit_back_index', methods: ['GET'])]
    public function index(Request $request, ProduitRepository $produitRepository): Response
{
    // Get query parameters for sorting and filtering
    $sort = $request->query->get('sort', 'id');
    $direction = $request->query->get('direction', 'asc');
    $id = $request->query->get('id');
    $nbproduit = $request->query->get('nbproduit');

    // Prepare the query builder with filtering and sorting
    $qb = $produitRepository->createQueryBuilder('p');

    if ($id) {
        $qb->andWhere('p.id = :id')
           ->setParameter('id', $id);
    }

    if ($nbproduit) {
        $qb->andWhere('p.nbproduit = :nbproduit')
           ->setParameter('nbproduit', $nbproduit);
    }

    $qb->orderBy('p.' . $sort, $direction);

    $produits = $qb->getQuery()->getResult();

    return $this->render('produit_back/index.html.twig', [
        'produits' => $produits,
        'sort' => $sort,
        'direction' => $direction,
        'id' => $id,
        'nbproduit' => $nbproduit,
    ]);
}

    #[Route('/new', name: 'app_produit_back_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProduitRepository $produitRepository): Response
    {
        $produit = new Produit();
        $form = $this->createForm(Produit1Type::class, $produit);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $note = $form->get('note')->getData();
    
            if ($note === null) {
                $produit->setNote(0);
            }
    
            $produitRepository->save($produit, true);
    
            $sid = "ACb001084e10cf614cf26a11fcf553307e";
            $token = "384711a866a6e274aea83d834df0b611";
            $twilio = new Client($sid, $token);
            $message = $twilio->messages
                ->create("+21626733967", // to
                    array(
                        "messagingServiceSid" => "MG89f2004d609fa49505095df053a05651",
                        "body" => " Vous avez un nouveau produit sous le nom de: " 
                    )
                );
    
            return $this->redirectToRoute('app_produit_back_index', [], Response::HTTP_SEE_OTHER);
        }  
    
        return $this->renderForm('produit_back/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }
    

    #[Route('/{id}', name: 'app_produit_back_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit_back/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_back_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        $form = $this->createForm(Produit1Type::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produitRepository->save($produit, true);

            return $this->redirectToRoute('app_produit_back_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit_back/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_back_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $produitRepository->remove($produit, true);
        }

        return $this->redirectToRoute('app_produit_back_index', [], Response::HTTP_SEE_OTHER);
    }
}
