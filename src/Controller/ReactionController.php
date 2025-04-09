<?php

namespace App\Controller;

use App\Entity\Reaction;
use App\Form\ReactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PortfolioRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;




#[Route('/reaction')]
class ReactionController extends AbstractController
{
    #[Route('/', name: 'app_reaction_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $reactions = $entityManager
            ->getRepository(Reaction::class)
            ->findAll();

        return $this->render('reaction/index.html.twig', [
            'reactions' => $reactions,
        ]);
    }

    #[Route('/new/{id_portfolio}', name: 'app_reaction_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PortfolioRepository $portfolioRepository, EntityManagerInterface $entityManager, $id_portfolio): Response
    {
        $reaction = new Reaction();
        $form = $this->createForm(ReactionType::class, $reaction, [
            'idPortfolio' => $id_portfolio,
        ]);
        
    
       

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reaction);
            $entityManager->flush();

            return $this->redirectToRoute('app_reaction_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reaction/new.html.twig', [
            'reaction' => $reaction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reaction_show', methods: ['GET'])]
    public function show(Reaction $reaction): Response
    {
        return $this->render('reaction/show.html.twig', [
            'reaction' => $reaction,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reaction $reaction, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReactionType::class, $reaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reaction_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reaction/edit.html.twig', [
            'reaction' => $reaction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reaction_delete', methods: ['POST'])]
    public function delete(Request $request, Reaction $reaction, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reaction->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reaction);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reaction_index', [], Response::HTTP_SEE_OTHER);
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ...
            ->add('idPortfolio', HiddenType::class, [
                'data' => $options['idPortfolio'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reaction::class,
            'idPortfolio' => null,
        ]);
    }
}
