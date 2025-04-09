<?php

namespace App\Controller;

use Dompdf\Dompdf;
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReclamationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/reclamation')]
class ReclamationController extends AbstractController
{


    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, ReclamationRepository $repo, Request $request, PaginatorInterface $paginator): Response
    {
        // Get the statistics
        $stats = $entityManager->createQueryBuilder()
            ->select('r.typeReclamation, COUNT(r.idReclamation) as num_reclamations')
            ->from(Reclamation::class, 'r')
            ->groupBy('r.typeReclamation')
            ->getQuery()
            ->getResult();



        $filters = $request->get("divisions");
        $total = $repo->TotalReclamation($filters);

        $query = $repo->filterwithdiv($filters);
        $req = $entityManager->createQueryBuilder('r')
            ->select('DISTINCT r.typeReclamation')
            ->from(Reclamation::class, 'r')
            ->getQuery()
            ->getResult();


        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        if ($request->get('ajax')) {
            return new JsonResponse([
                'content' => $this->renderView(
                    'reclamation/_content.html.twig',
                    [
                        'reclamations' => $pagination,
                        'types' => $req,
                        'total' => $total,


                    ]
                ),
            ]);
        }

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $pagination,
            'types' => $req,
            'total' => $total,


        ]);

        return $this->renderView('reclamation/stat.html.twig', [
            'reclamations' => $pagination,
            'types' => $req,
            'stats' => $stats,
        ]);
    }
    #[Route('/stat', name: 'stat', methods: ['GET'])]
    public function stat(EntityManagerInterface $entityManager)
    {
        $rec = $entityManager->getRepository(Reclamation::class)->findAll();
        // Get the statistics
        $stats = $entityManager->createQueryBuilder()
            ->select('r.typeReclamation, COUNT(r.idReclamation) as num_reclamations')
            ->from(Reclamation::class, 'r')
            ->groupBy('r.typeReclamation')
            ->getQuery()
            ->getResult();





        return $this->render('reclamation/stat.html.twig', [
            'reclamations' => $rec,
            'stats' => $stats,
        ]);
    }





    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamation();
        $reclamation->setTemps(new \DateTime()); // Set the current date and time
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reclamation);
            $entityManager->flush();
          
            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);

        } else {
           
        }

        return $this->renderForm('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'f' => $form,
        ]);
    }


    #[Route('/{idReclamation}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{idReclamation}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
           

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        } else {
           
        }

        return $this->renderForm('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'f' => $form,
        ]);
    }

    #[Route('/{idReclamation}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getIdReclamation(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
           
        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }
        
    }


    /**
     * @Route("/{id}/pdf", name="app_rec_pdf", methods={"GET"})
     */
    public function pdf(Reclamation $Reclamation): Response
    {
        // create new PDF document
        $dompdf = new Dompdf();

        // generate HTML content for the document
        $html = $this->renderView('Reclamation/pdf.html.twig', [
            'Reclamation' => $Reclamation,

        ]);

        // load HTML into the PDF document
        $dompdf->loadHtml($html);

        // render PDF document
        $dompdf->render();

        // create a response object to return the PDF file
        $response = new Response($dompdf->output());

        // set content type to application/pdf
        $response->headers->set('Content-Type', 'application/pdf');

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            'Reclamation.pdf'
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }



    #[Route('/test', name: 'test', methods: ['GET'])]
    public function test(EntityManagerInterface $entityManager): Response
    {
        $typerec =  $entityManager->getRepository(Reclamation::class)->findAll();
        dd($typerec);
    }


    // #[Route('/test', name: 'test', methods: ['GET'])]
    // public function index2(EntityManagerInterface $entityManager,  Request $request): Response
    // {
    //     $typerec =  $entityManager->getRepository(Reclamation::class)->getype();
    //     dd($typerec);
    //     // $filters = $request->get("divisions");
    //     // $clients = $entityManager->filterwithdiv($filters);
    //     // $total = $entityManager->TotalClients($filters);

    //     if ($request->get('ajax')) {
    //         return new JsonResponse([
    //             'content' => $this->renderView(
    //                 'client/_content.html.twig',
    //                 [
    //                     'clients' => $clients,
    //                     // 'divisions' => $divisions,
    //                     'total' => $total,
    //                 ]
    //             ),
    //         ]);
    //     }
    //     return $this->render(
    //         'client/index.html.twig',
    //         [
    //             'clients' => $clients,
    //             // 'divisions' => $divisions,
    //             'total' => $total,
    //         ]
    //     );
    // }
}
    

// }
// #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
//     public function new(Request $request, EntityManagerInterface $entityManager, FlashyNotifier $flashy): Response
//     {
//         $reclamation = new Reclamation();
//         $reclamation->setTemps(new \DateTime()); // Set the current date and time
//         $form = $this->createForm(ReclamationType::class, $reclamation)
//             ->add('captcha', Recaptcha3Type::class, [
//                 'constraints' => new Recaptcha3(),
//                 'action_name' => 'homepage',
                
//             ]);
//         $form->handleRequest($request);
    
//         if ($form->isSubmitted() && $form->isValid()) {
//             $entityManager->persist($reclamation);
//             $entityManager->flush();
//             $flashy->success('Reclamation created successfully!');
//             return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
//         }else{
//             $flashy->error('Reclamation dosent created !');
//         }
    
//         return $this->renderForm('reclamation/new.html.twig', [
//             'reclamation' => $reclamation,
//             'f' => $form,
//         ]);
//     }
    
    
    
    

//     #[Route('/{idReclamation}', name: 'app_reclamation_show', methods: ['GET'])]
//     public function show(Reclamation $reclamation): Response
//     {
//         return $this->render('reclamation/show.html.twig', [
//             'reclamation' => $reclamation,
//         ]);
//     }