<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService
    ) {}

    #[Route('/cart', name: 'cart_index')]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig', [
            'cartItems' => $this->cartService->getCart(),
            'cartSummary' => $this->cartService->getCartSummary(),
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add', requirements: ['id' => '\d+'])]
    public function add(int $id, Request $request): Response
    {
        $this->cartService->addToCart(
            $id,
            max(1, (int)$request->query->get('quantity', 1))
        );
        
        $this->addFlash('success', 'Product added to cart successfully.');
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/update/{id}', name: 'cart_update', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function update(int $id, Request $request): Response
    {
        $this->validateCsrfToken($request->request->get('_token'), 'cart_update_' . $id);
        
        $this->cartService->updateQuantity(
            $id,
            max(1, (int)$request->request->get('quantity', 1))
        );
        
        $this->addFlash('success', 'Cart updated successfully.');
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove', requirements: ['id' => '\d+'])]
    public function remove(int $id, Request $request): Response
    {
        $this->validateCsrfToken($request->query->get('_token'), 'cart_remove_' . $id);
        
        $this->cartService->removeFromCart($id);
        $this->addFlash('success', 'Item removed from cart.');
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/clear', name: 'cart_clear')]
    public function clear(Request $request): Response
    {
        $this->validateCsrfToken($request->query->get('_token'), 'cart_clear');
        
        $this->cartService->clearCart();
        $this->addFlash('success', 'Cart cleared successfully.');
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/coupon/apply', name: 'coupon_apply', methods: ['POST'])]
    public function applyCoupon(Request $request): JsonResponse
    {
        $this->validateCsrfToken($request->request->get('_token'), 'coupon_apply');
        
        $code = trim($request->request->get('code', ''));
        if (empty($code)) {
            return $this->jsonError('Please enter a coupon code.', 400);
        }

        $result = $this->cartService->applyCoupon($code);
        
        return $result['success'] 
            ? $this->jsonSuccess('Coupon applied successfully!', $this->cartService->getCartSummary())
            : $this->jsonError($result['message'] ?? 'Invalid coupon code.', 400);
    }

    #[Route('/cart/count', name: 'cart_count', methods: ['GET'])]
    public function getCartCount(): JsonResponse
    {
        return new JsonResponse(['count' => $this->cartService->getCartCount()]);
    }

    private function validateCsrfToken(?string $token, string $tokenId): void
    {
        if (!$this->isCsrfTokenValid($tokenId, $token)) {
            throw new InvalidCsrfTokenException('Invalid CSRF token.');
        }
    }

    private function jsonError(string $message, int $status = 400): JsonResponse
    {
        return new JsonResponse(['success' => false, 'message' => $message], $status);
    }

    private function jsonSuccess(string $message, array $data = []): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'message' => $message,
            ...$data
        ]);
    }
}