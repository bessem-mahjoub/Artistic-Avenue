<?php

namespace App\Controller;

use App\Entity\PointRelais;
use App\Form\PointRelaisType;
use App\Repository\PointRelaisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;



#[Route('/point/relais')]
class PointRelaisController extends AbstractController
{
    #[Route('/', name: 'app_point_relais_index', methods: ['GET'])]
    public function index(Request $request, PointRelaisRepository $pointRelaisRepository, PaginatorInterface $paginator): Response
    {
        $point_relais = $pointRelaisRepository->findAll();
        $pagination = $paginator->paginate(
            $point_relais,
            $request->query->getInt('page', 1),
            5 //limit par page
        );
    
        return $this->render('point_relais/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }
    


    #[Route('/new', name: 'app_point_relais_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PointRelaisRepository $pointRelaisRepository): Response
    {
        $pointRelai = new PointRelais();
        $form = $this->createForm(PointRelaisType::class, $pointRelai);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pointRelaisRepository->save($pointRelai, true);

            return $this->redirectToRoute('app_point_relais_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('point_relais/new.html.twig', [
            'point_relai' => $pointRelai,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_point_relais_show', methods: ['GET'])]
    public function show(PointRelais $pointRelai): Response
    {
        return $this->render('point_relais/show.html.twig', [
            'point_relai' => $pointRelai,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_point_relais_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PointRelais $pointRelai, PointRelaisRepository $pointRelaisRepository): Response
    {
        $form = $this->createForm(PointRelaisType::class, $pointRelai);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pointRelaisRepository->save($pointRelai, true);

            return $this->redirectToRoute('app_point_relais_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('point_relais/edit.html.twig', [
            'point_relai' => $pointRelai,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_point_relais_delete', methods: ['POST'])]
    public function delete(Request $request, PointRelais $pointRelai, PointRelaisRepository $pointRelaisRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pointRelai->getId(), $request->request->get('_token'))) {
            $pointRelaisRepository->remove($pointRelai, true);
        }

        return $this->redirectToRoute('app_point_relais_index', [], Response::HTTP_SEE_OTHER);
    }

    


public function map(PointRelaisRepository $pointRelaisRepository)
{
    // Get all point relais
    $pointRelaisList = $pointRelaisRepository->findAll();
    
    // Render the map view and pass the list of point relais to it
    return $this->render('map.html.twig', [
        'pointRelaisList' => $pointRelaisList,
    ]);
}



public function myChart(PointRelaisRepository $repository): Response
{
    // Récupérer les données de la base de données
    $data = $repository->getDataForPieChart();

    // Préparer les données pour le graphique
    $pieData = [];
    $pieData[] = ['ville', 'nom'];
    foreach ($data as $row) {
        $pieData[] = [$row['ville'], $row['nom']];
    }

    // Créer le graphique en cercle
    $pieChart = new PieChart();
    $pieChart->getData()->setArrayToDataTable($pieData);
    $pieChart->getOptions()->setTitle('My Pie Chart');
    $pieChart->getOptions()->setHeight(400);
    $pieChart->getOptions()->setWidth(600);

    // Afficher le graphique dans une vue Twig
    return $this->render('chart.html.twig', [
        'pieChart' => $pieChart
    ]);
}









}
