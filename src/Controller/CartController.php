<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    #[Route('/cart', name: 'cart_index')]
    public function index(): Response
    {
        $cartItems = $this->cartService->getCart();
        $cartSummary = $this->cartService->getCartSummary();

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
            'cartSummary' => $cartSummary,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add', requirements: ['id' => '\d+'])]
    public function add(int $id): Response
    {
        $this->cartService->addToCart($id);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/update/{id}', name: 'cart_update', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function update(int $id): Response
    {
        $quantity = (int)$_POST['quantity'] ?? 1;
        $this->cartService->updateQuantity($id, $quantity);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove', requirements: ['id' => '\d+'])]
    public function remove(int $id): Response
    {
        $this->cartService->removeFromCart($id);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/clear', name: 'cart_clear')]
    public function clear(): Response
    {
        $this->cartService->clearCart();

        return $this->redirectToRoute('cart_index');
    }
}
