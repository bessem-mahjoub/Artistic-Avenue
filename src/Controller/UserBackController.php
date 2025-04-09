<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user/back')]
class UserBackController extends AbstractController
{
    #[Route('/', name: 'app_user_back_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $sortField = $request->query->get('sortField', 'id');
    $sortDirection = $request->query->get('sortDirection', 'asc');

    $searchQuery = $request->query->get('q');

    $queryBuilder = $userRepository->createQueryBuilder('u');
    
    // Add search query if available
    if ($searchQuery) {
        $queryBuilder->where('u.email LIKE :searchQuery OR u.id LIKE :searchQuery')
            ->setParameter('searchQuery', '%' . $searchQuery . '%');
    }
    
    // Add sorting
    $queryBuilder->orderBy('u.' . $sortField, $sortDirection);

    $users = $paginator->paginate(
        $queryBuilder->getQuery(),
        $request->query->getInt('page', 1),
        limit: 2
    );

    return $this->render('user_back/index.html.twig', [
        'users' => $users,
        'searchQuery' => $searchQuery,
        'sortField' => $sortField,
        'sortDirection' => $sortDirection,
    ]);
    }

    #[Route('/new', name: 'app_user_back_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $hashPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashPassword);
            $userRepository->save($user, true);
            return $this->redirectToRoute('app_user_back_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user_back/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_back_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user_back/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_back_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $hashPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashPassword);
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_back_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user_back/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_back_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_back_index', [], Response::HTTP_SEE_OTHER);
    }
}
