<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twilio\Rest\Client;
use Doctrine\ORM\EntityManagerInterface; 
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\Transport\TransportInterface;








class EventController extends AbstractController
{
    #[Route('/events', name: 'app_event_index', methods: ['GET'])]
public function index(Request $request, EventRepository $eventRepository): Response
{
    // Récupérer le numéro de page depuis la requête GET
    $currentPage = $request->query->getInt('currentPage', 1);

    // Utiliser $currentPage pour effectuer la pagination de vos données
    // et récupérer les données de la page courante
    $eventsPerPage = 2; // Nombre d'événements par page
    $offset = ($currentPage - 1) * $eventsPerPage;
    $events = $eventRepository->findBy([], null, $eventsPerPage, $offset);

    // Calculer le total d'événements et le nombre total de pages
    $totalEvents = count($eventRepository->findAll()); // Remplacez cette ligne par votre propre logique pour obtenir le total d'événements
    $totalPages = ceil($totalEvents / $eventsPerPage);
    
    // Passer les données à votre template Twig
    return $this->render('event/index.html.twig', [
        'events' => $events,
        'currentPage' => $currentPage,
        'eventsPerPage' => $eventsPerPage,
        'totalEvents' => $totalEvents,
        'totalPages' => $totalPages,
    ]);
}

#[Route('/client', name: 'client', methods: ['GET'])]
public function client(Request $request, EventRepository $eventRepository): Response
{
    // Récupérer le numéro de page depuis la requête GET
    $currentPage = $request->query->getInt('currentPage', 1);

    // Utiliser $currentPage pour effectuer la pagination de vos données
    // et récupérer les données de la page courante
    $eventsPerPage = 10; // Nombre d'événements par page
    $offset = ($currentPage - 1) * $eventsPerPage;
    $events = $eventRepository->findBy([], null, $eventsPerPage, $offset);

    // Calculer le total d'événements et le nombre total de pages
    $totalEvents = count($eventRepository->findAll()); // Remplacez cette ligne par votre propre logique pour obtenir le total d'événements
    $totalPages = ceil($totalEvents / $eventsPerPage);

    // Passer les données à votre template Twig
    return $this->render('event/client.html.twig', [
        'events' => $events,
        'currentPage' => $currentPage,
        'eventsPerPage' => $eventsPerPage,
        'totalEvents' => $totalEvents,
        'totalPages' => $totalPages,
    ]);
}

    
    
    
    #[Route('/events/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EventRepository $eventRepository): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventRepository->save($event, true);

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/events/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/events/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_event_index');
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/events/{id}', name: 'app_event_delete', methods: ['DELETE'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index');
    }

    public function submitevent(Request $request): Response
{
    $event = new event();
    $form = $this->createForm(eventType::class, $event);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Get the uploaded file
        $file = $form['image']->getData();

        // Generate a unique filename for the file
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();

        // Move the file to the uploads directory
        $file->move(
            $this->getParameter('uploads_directory'),
            $fileName
        );

        // Set the filename of the uploaded file
        $event->setImage($fileName);

        // Save the portfolio to the database
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($event);
        $entityManager->flush();

        // Replace the following lines with your own logic to get the phone number of the recipient
        $phoneNumber = $event->getPhoneNumber();

        // Configure Twilio authentication information
        $sid = 'AC1816236a5b1e246e85172fd16f8a2575'; // Replace this line with your Twilio SID
        $token = 'c15c8fd45a6a5f63c229ac0d9bd34db7'; // Replace this line with your Twilio token
        $client = new Client($sid, $token);

        // Send SMS
        $client->messages->create(
            $phoneNumber,
            [
                'from' => 'NUMERO_TWILIO', // Replace this line with your Twilio number
                'body' => 'Votre message SMS ici', // Replace this line with the content of your SMS message
            ]
        );

        // Redirect to home page or any other appropriate page
        return $this->redirectToRoute('home');
    }

    return $this->render('event/index.html.twig');
}


#[Route('/events/details/{id}', name: 'app_event_back_details')]
public function getEventDetails(Request $request, Event $event): Response {
    // Récupérer les participants de l'événement
    $participants = $event->getParticipants();
    
    // Passer les participants à la vue pour les afficher
    return $this->render('event_back/details.html.twig', [
        'event' => $event,
        'participants' => $participants,
    ]);
}
    
    #[Route('/events/pdf/stat', name: 'app_event_pdf')]
    public function indexpdf(Request $request, EventRepository $repository)
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
    
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
    
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('event/pdf.html.twig', [
            'events' => $repository->findAll()
        ]);
    
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
    
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');
    
        // Render the HTML as PDF
        $dompdf->render();
    
        // Get the output of the PDF
        $output = $dompdf->output();
    
        // Send the generated PDF response to the browser
        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
    
        // Set the response as attachment to force download
        $response->headers->set('Content-Disposition', 'attachment;filename=event.pdf');
    
        return $response;
    }
    
    
    
    
}
