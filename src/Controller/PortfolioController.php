<?php

namespace App\Controller;

use App\Entity\Portfolio;
use App\Form\PortfolioType;
use App\Repository\PortfolioRepository;
use App\Message\Messenger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;







#[Route('/portfolio')]
class PortfolioController extends AbstractController
{
    #[Route('/', name: 'app_portfolio_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager, PortfolioRepository $portfolioRepository): Response
    {
        $query = $request->query->get('q');
        $sort = $request->query->get('sort', 'id');
        $direction = $request->query->get('dir', 'asc');
        $filter = $request->query->get('filter');
    
        $currentPage = $request->query->getInt('currentPage', 1);
        $portfoliosPerPage = 3;
        $offset = ($currentPage - 1) * $portfoliosPerPage;
    
        $qb = $portfolioRepository->createQueryBuilder('p')
            ->andWhere('p.titre LIKE :query OR p.description LIKE :query')
            ->setParameter('query', "%{$query}%");
    
        if ($filter === 'popular') {
            $qb->orderBy('p.likes', 'desc');
        } else {
            $qb->orderBy("p.{$sort}", $direction);
        }
    
        $totalPortfolios = $qb->select('COUNT(p.id)')->getQuery()->getSingleScalarResult();
        $totalPages = ceil($totalPortfolios / $portfoliosPerPage);
    
        $portfolios = $portfolioRepository->findBy(
            [], [$sort => $direction], $portfoliosPerPage, $offset
        );
    
        return $this->render('portfolio/index.html.twig', [
            'portfolios' => $portfolios,
            'query' => $query,
            'sort' => $sort,
            'direction' => $direction,
            'filter' => $filter,
            'currentPage' => $currentPage,
            'portfoliosPerPage' => $portfoliosPerPage,
            'totalPortfolios' => $totalPortfolios,
            'totalPages' => $totalPages,
        ]);
    }
    

    #[Route('/new', name: 'app_portfolio_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $portfolio = new Portfolio();
        $form = $this->createForm(PortfolioType::class, $portfolio);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $imageFile = $form->get('image')->getData();
    
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('portfolio_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle exception if something went wrong during file upload
                    // ...
                }
    
                $portfolio->setImage($newFilename);
            }
    
            $entityManager->persist($portfolio);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_portfolio_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('portfolio/new.html.twig', [
            'portfolio' => $portfolio,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_portfolio_show', methods: ['GET'])]
    public function show(Portfolio $portfolio, MessageBusInterface $messageBus): Response
    {

      

        return $this->render('portfolio/show.html.twig', [
            'portfolio' => $portfolio,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_portfolio_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Portfolio $portfolio, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PortfolioType::class, $portfolio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the path of the original image
            $imagePath = $portfolio->getImage();

            $entityManager->flush();

            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                // Move the uploaded file to a temporary location
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(sys_get_temp_dir(), $newFilename);
                $pathToImage = sys_get_temp_dir() . '/' . $newFilename;

              
             
            
          
            }

            return $this->redirectToRoute('app_portfolio_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('portfolio/edit.html.twig', [
            'portfolio' => $portfolio,
            'form' => $form,
        ]);
    }
        

    #[Route('/{id}', name: 'app_portfolio_delete', methods: ['POST'])]
    public function delete(Request $request, Portfolio $portfolio, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$portfolio->getId(), $request->request->get('_token'))) {
            $entityManager->remove($portfolio);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_portfolio_index', [], Response::HTTP_SEE_OTHER);
    }

     /**
 * @Route("/portfolio/{id}/like", name="portfolio_like")
 */
/*#[Route('/{id}/like', name: 'portfolio_like', methods: ['POST'])]
public function like(Portfolio $portfolio)
{
    $portfolio->setLikes($portfolio->getLikes() + 1);
    $this->getDoctrine()->getManager()->flush();
    return $this->redirectToRoute('app_portfolio_index', [], Response::HTTP_SEE_OTHER);
  
}*/
#[Route('/{id}/like', name: 'portfolio_like', methods: ['POST'])]
public function like(Portfolio $portfolio, EntityManagerInterface $entityManager): Response
{
    $portfolio->setLikes($portfolio->getLikes() + 1);
    $entityManager->flush();

    return $this->redirectToRoute('app_portfolio_index', [], Response::HTTP_SEE_OTHER);
}


#[Route('/submit', name: 'app_portfolio_submit', methods: ['GET', 'POST'])]
public function submitPortfolio(Request $request, EntityManagerInterface $entityManager, MessageBusInterface $messageBus): Response
{
    $portfolio = new Portfolio();
    $form = $this->createForm(PortfolioType::class, $portfolio);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Handle image upload
        $imageFile = $form->get('image')->getData();

        if ($imageFile) {
            $newFilename = uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('portfolio_images_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                // Handle exception if something went wrong during file upload
                // ...
            }

            $portfolio->setImage($newFilename);
        }

        $entityManager->persist($portfolio);
        $entityManager->flush();

        // Dispatch message
        $message = new Messenger($portfolio->getId());
        $messageBus->dispatch($message);

        return $this->redirectToRoute('app_portfolio_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('portfolio/submit.html.twig', [
        'portfolio' => $portfolio,
        'form' => $form,
    ]);
}

#[Route('/{id}/buy', name: 'app_portfolio_buy', methods: ['POST'])]
public function buy(Portfolio $portfolio, Request $request, EntityManagerInterface $entityManager): Response
{
    // Retrieve the Stripe API keys
    $stripePublicKey = $this->getParameter('pk_test_51N086yDiF47XSsVjkhq55XZtCddLUZg3U7KYMCuqpTfRTCLGEOe4AFAoGj1WrrtxiInWtBpjLbotW9yHJppO5Xbj00oaJ1RPct');
    $stripeSecretKey = $this->getParameter('sk_test_51N086yDiF47XSsVj1ZZTsxjcVTJVcJIDAMxQ2aVWcTUptL3O2asV1vUqCApYiNSN8iz0lVnTorhXnrphOPVgY01F00HMN8jZoK');
    \Stripe\Stripe::setApiKey($stripeSecretKey);

    // Create a new Stripe PaymentIntent
    $intent = \Stripe\PaymentIntent::create([
        'amount' => $portfolio->getPrice() * 100,
        'currency' => 'eur',
    ]);

    // Update the portfolio with the PaymentIntent ID
    $portfolio->setPaymentIntentId($intent->id);
    $entityManager->flush();

    // Render the template with the Stripe public key and PaymentIntent client secret
    return $this->render('portfolio/buy.html.twig', [
        'stripe_public_key' => $stripePublicKey,
        'client_secret' => $intent->client_secret,
    ]);
}



}
