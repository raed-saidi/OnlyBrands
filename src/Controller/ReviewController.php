<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Review;
use App\Form\ReviewType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends AbstractController
{
    #[Route('/product/{id}/review', name: 'product_review_add', methods: ['POST'])]
    public function addReview(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        // Check if user is logged in
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to leave a review.');
            return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
        }

        $review = new Review();
        $review->setProduct($product);
        $review->setUser($user);

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Your review has been submitted!');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
    }
}