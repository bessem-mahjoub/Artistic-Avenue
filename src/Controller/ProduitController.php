<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Endroid\QrCode\QrCode;





#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository, Request $request): Response
    {
        // Récupérer le numéro de page depuis la requête GET
        $currentPage = $request->query->getInt('currentPage', 1);

        // Utiliser $currentPage pour effectuer la pagination de vos données
        // et récupérer les données de la page courante
        $produitsPerPage = 2; // Nombre d'événements par page
        $offset = ($currentPage - 1) * $produitsPerPage;
        $produits = $produitRepository->findBy([], null, $produitsPerPage, $offset);

        // Calculer le total d'événements et le nombre total de pages
        $totalProduits = count($produitRepository->findAll()); // Remplacez cette ligne par votre propre logique pour obtenir le total d'événements
        $totalPages = ceil($totalProduits / $produitsPerPage);

        // Passer les données à votre template Twig
        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'currentPage' => $currentPage,
            'produitsPerPage' => $produitsPerPage,
            'totalProduits' => $totalProduits,
            'totalPages' => $totalPages,
        ]);
    }



    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProduitRepository $produitRepository): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produitRepository->save($produit, true);

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        // Throw a 404 error if the product doesn't exist
        if (!$produit) {
            throw $this->createNotFoundException('Product not found');
        }
    
        $form = $this->createFormBuilder()
            ->add('note', ChoiceType::class, [
                'label' => 'Rate this product',
                'choices' => [
                    '1 star' => 1,
                    '2 stars' => 2,
                    '3 stars' => 3,
                    '4 stars' => 4,
                    '5 stars' => 5,
                ],
                'expanded' => true,
                'multiple' => false,
                'attr' => ['class' => 'form-check-inline'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->getForm();
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $note = $form->get('note')->getData();
    
            // Do whatever you want with the note, e.g. save it to the database
            $produit->setNote($note);
            $produitRepository->save($produit, true);
    
            $this->addFlash('success', 'Thank you for rating this product!');
        }
    
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
            'note_form' => $form->createView(),
        ]);
    }
    



    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produitRepository->save($produit, true);

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $produitRepository->remove($produit, true);
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }




    
    #[Route('/{id}/qrcode', name: 'app_produit_qrcode', methods: ['GET'])]
    public function generateQrCode(Produit $produit): Response
    {
        $qrCode = new QrCode($produit->getNomProd()); // Utilisez le nom du produit dans le QR code
        $qrCode->setSize(250);
    
        // Créez une réponse avec l'image comme contenu
        $response = new Response();
        $response->headers->set('Content-Type', $qrCode->getContentType());
        $qrCode->writeStringToOutput($response->getBody());
    
        return $response;
    }
    
    
    
}