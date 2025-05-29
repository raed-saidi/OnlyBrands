<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'search_index')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $query = $request->query->get('q', '');

        if (empty($query)) {
            return $this->redirectToRoute('product_index');
        }

        $products = $productRepository->searchProducts($query);

        return $this->render('search/index.html.twig', [
            'products' => $products,
            'query' => $query,
        ]);
    }
}