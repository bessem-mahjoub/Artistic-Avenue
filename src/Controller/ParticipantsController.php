<?php
namespace App\Controller;

use App\Entity\Event;
use App\Entity\Participants;
use App\Form\ParticipantsType;
use App\Repository\EventRepository;
use App\Repository\ParticipantsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twilio\Rest\Client;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use Endroid\QrCode\QrCode;
use libphonenumber\NumberFormat;
use Doctrine\Persistence\ManagerRegistry;
use Endroid\QrCode\Color\Rgb;

use Endroid\QrCode\Response\QrCodeResponse;





class ParticipantsController extends AbstractController
{
    #[Route('/participants', name: 'app_participants_index', methods: ['GET'])]
    public function index(ParticipantsRepository $participantsRepository): Response
    {
        $participants = $participantsRepository->findAll();

        return $this->render('participants/index.html.twig', [
            'participants' => $participants,
        ]);
    }

    #[Route('/participants/new/{id}', name: 'app_participants_new', methods: ['GET', 'POST'])]
public function new(
    EntityManagerInterface $doctrine,
    Request $request,
    int $id,
    ParticipantsRepository $participantsRepository,
    PhoneNumberUtil $phoneNumberUtil
): Response {
    $event = $doctrine->getRepository(Event::class)->find($id);
    if ($event->getNbrPlace() == 0) {
        return $this->redirectToRoute('event_index');
    }

    $participant = new Participants();
    $form = $this->createForm(ParticipantsType::class, $participant);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $participantsRepository->save($participant, true);

        // Valider le numéro de téléphone
        try {
            $parsedNumber = $phoneNumberUtil->parse($participant->getnum(), 'TN'); // Remplacez 'TN' par le code du pays attendu
            if (!$phoneNumberUtil->isValidNumber($parsedNumber)) {
                throw new \Exception('Numéro de téléphone invalide');
            }
        } catch (NumberParseException $e) {
            throw new \Exception('Erreur de validation du numéro de téléphone');
        }

        // Envoyer le SMS en utilisant l'API Twilio
        $accountSid = 'AC130691e6899fb72009fd2f9a4e6330ef';
        $authToken = 'bd08eda7f285a38a149d8d27d925a5c9';
        $twilioNumber = '+15625731341'; // Votre numéro Twilio
        $client = new Client($accountSid, $authToken);
        $client->messages->create(
            $participant->getnum(),
            [
                'from' => $twilioNumber,
                'body' => 'Votre participation a été enregistrée avec succès !'
            ]
        );

        $participant->addEvent($event);
        $doctrine->persist($participant);
        $doctrine->flush();

        $event->setNbrPlace($event->getNbrPlace()-1);
        $doctrine->persist($event);
        $doctrine->flush();
        return $this->redirectToRoute('app_participants_new', ['id' => $id]);
    }

    return $this->render('participants/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

    

    #[Route('/participants/{id}', name: 'app_participants_show', methods: ['GET'])]
    public function show(Participants $participant): Response
    {
        return $this->render('participants/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/participants/{id}/edit', name: 'app_participants_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participants $participant): Response
    {
        $form = $this->createForm(ParticipantsType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('participants_index');
        }

        return $this->render('participants/edit.html.twig', [
            'form' => $form->createView(),
            'participant' => $participant,
        ]);
    }

    #[Route('/participants/{id}/delete', name: 'app_participants_delete', methods: ['DELETE'])]
    public function delete(Request $request, Participants $participant): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($participant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_participants_index');
    }
    #[Route('/participants/{id}/my-participation', name: 'participants_my_participation', methods: ['GET'])]
public function myParticipation(Participants $participant): Response
{
    // Récupérer les événements liés au participant
    $events = $participant->getEvent();

    return $this->render('participants/my_participation.html.twig', [
        'events' => $events,
    ]);
}
private $managerRegistry;



    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    
  



}
