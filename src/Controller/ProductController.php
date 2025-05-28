<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'product_index')]
    #[Route('/products/{id}', name: 'product_index_category', requirements: ['id' => '\d+'])]
    public function index(?Category $category, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $queryBuilder = $productRepository->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c');
        if ($category) {
            $queryBuilder->andWhere('p.category = :category')
                ->setParameter('category', $category);
        }
        $products = $queryBuilder->getQuery()->getResult();
        $categories = $categoryRepository->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    #[Route('/products/{id}', name: 'product_show', requirements: ['id' => '\d+'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}