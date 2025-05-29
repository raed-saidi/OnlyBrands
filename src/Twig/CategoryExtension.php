<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CategoryExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getCategoryIcon', [$this, 'getCategoryIcon']),
        ];
    }

    public function getCategoryIcon(string $categoryName): string
    {
        $icons = [
            'Electronics' => 'fas fa-laptop',
            'Clothing' => 'fas fa-tshirt',
            'Books' => 'fas fa-book',
            'Home & Garden' => 'fas fa-home',
            'Sports' => 'fas fa-football-ball',
            'Beauty' => 'fas fa-palette',
            'Toys' => 'fas fa-gamepad',
            'Automotive' => 'fas fa-car',
            'Health' => 'fas fa-heartbeat',
            'Food' => 'fas fa-utensils',
            'Jewelry' => 'fas fa-gem',
            'Music' => 'fas fa-music',
            'Movies' => 'fas fa-film',
            'Gaming' => 'fas fa-gamepad',
            'Furniture' => 'fas fa-couch',
            'Tools' => 'fas fa-tools',
            'Pet Supplies' => 'fas fa-paw',
            'Baby' => 'fas fa-baby',
            'Office' => 'fas fa-briefcase',
            'Travel' => 'fas fa-plane',
        ];

        $key = ucwords(strtolower(trim($categoryName)));
        
        return $icons[$key] ?? 'fas fa-tag';
    }
}