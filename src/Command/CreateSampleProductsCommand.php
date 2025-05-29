<?php

namespace App\Command;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-sample-products',
    description: 'Create sample products for testing',
)]
class CreateSampleProductsCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // First, create some categories if they don't exist
        $categories = [
            'Electronics',
            'Clothing',
            'Books',
            'Home & Garden',
            'Sports'
        ];

        $categoryEntities = [];
        foreach ($categories as $name) {
            $category = $this->entityManager->getRepository(Category::class)->findOneBy(['name' => $name]);
            if (!$category) {
                $category = new Category();
                $category->setName($name);
                $this->entityManager->persist($category);
                $io->note("Created category: $name");
            }
            $categoryEntities[$name] = $category;
        }

        $this->entityManager->flush();

        // Sample products with float prices
        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Latest iPhone with advanced camera system and A17 Pro chip',
                'price' => 1299.000,
                'stock' => 50,
                'category' => 'Electronics'
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'description' => 'Flagship Android phone with AI-powered features',
                'price' => 999.000,
                'stock' => 30,
                'category' => 'Electronics'
            ],
            [
                'name' => 'MacBook Air M3',
                'description' => 'Ultra-thin laptop with M3 chip and all-day battery life',
                'price' => 1499.000,
                'stock' => 25,
                'category' => 'Electronics'
            ],
            [
                'name' => 'Nike Air Max 270',
                'description' => 'Comfortable running shoes with Max Air cushioning',
                'price' => 150.000,
                'stock' => 100,
                'category' => 'Sports'
            ],
            [
                'name' => 'Adidas Ultraboost 22',
                'description' => 'Premium running shoes with responsive cushioning',
                'price' => 180.000,
                'stock' => 75,
                'category' => 'Sports'
            ],
            [
                'name' => 'Levi\'s 501 Jeans',
                'description' => 'Classic straight-fit jeans in premium denim',
                'price' => 89.000,
                'stock' => 200,
                'category' => 'Clothing'
            ],
            [
                'name' => 'H&M Cotton T-Shirt',
                'description' => 'Comfortable cotton t-shirt in various colors',
                'price' => 25.000,
                'stock' => 300,
                'category' => 'Clothing'
            ],
            [
                'name' => 'The Great Gatsby',
                'description' => 'Classic American novel by F. Scott Fitzgerald',
                'price' => 15.000,
                'stock' => 500,
                'category' => 'Books'
            ],
            [
                'name' => 'Programming Book',
                'description' => 'Learn modern web development with practical examples',
                'price' => 45.000,
                'stock' => 100,
                'category' => 'Books'
            ],
            [
                'name' => 'Coffee Table',
                'description' => 'Modern wooden coffee table for living room',
                'price' => 299.000,
                'stock' => 20,
                'category' => 'Home & Garden'
            ],
            [
                'name' => 'Garden Tools Set',
                'description' => 'Complete set of essential gardening tools',
                'price' => 79.000,
                'stock' => 50,
                'category' => 'Home & Garden'
            ],
            [
                'name' => 'Yoga Mat',
                'description' => 'Non-slip yoga mat for home workouts',
                'price' => 35.000,
                'stock' => 150,
                'category' => 'Sports'
            ],
            [
                'name' => 'Wireless Headphones',
                'description' => 'Bluetooth headphones with noise cancellation',
                'price' => 199.000,
                'stock' => 80,
                'category' => 'Electronics'
            ],
            [
                'name' => 'Gaming Mouse',
                'description' => 'High-precision gaming mouse with RGB lighting',
                'price' => 65.000,
                'stock' => 120,
                'category' => 'Electronics'
            ],
            [
                'name' => 'Winter Jacket',
                'description' => 'Warm winter jacket with water-resistant coating',
                'price' => 129.000,
                'stock' => 60,
                'category' => 'Clothing'
            ]
        ];

        $count = 0;
        foreach ($products as $productData) {
            $existingProduct = $this->entityManager->getRepository(Product::class)->findOneBy(['name' => $productData['name']]);

            if (!$existingProduct) {
                $product = new Product();
                $product->setName($productData['name']);
                $product->setDescription($productData['description']);
                $product->setPrice($productData['price']); // Now using float
                $product->setStock($productData['stock']);
                $product->setCategory($categoryEntities[$productData['category']]);

                $this->entityManager->persist($product);
                $count++;
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('Created %d sample products and %d categories.', $count, count($categoryEntities)));

        return Command::SUCCESS;
    }
}