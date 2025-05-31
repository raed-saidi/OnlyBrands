<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    public function __construct(
        private RequestStack $requestStack,
        private ProductRepository $productRepository,
        private CouponService $couponService,
        private float $freeShippingThreshold = 150.0,
        private float $shippingCost = 7.0
    ) {}

    public function getCart(): array
    {
        $cart = $this->getSession()->get('cart', []);
        $cartWithProducts = [];

        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);
            if ($product instanceof Product) {
                $cartWithProducts[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                ];
            } else {
                $this->removeFromCart($id);
            }
        }

        return $cartWithProducts;
    }

    public function addToCart(int $productId, int $quantity = 1): void
    {
        $cart = $this->getSession()->get('cart', []);
        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
        $this->getSession()->set('cart', $cart);
    }

    public function updateQuantity(int $productId, int $quantity): void
    {
        $cart = $this->getSession()->get('cart', []);
        if (array_key_exists($productId, $cart)) {
            $cart[$productId] = max(1, $quantity);
            $this->getSession()->set('cart', $cart);
        }
    }

    public function removeFromCart(int $productId): void
    {
        $cart = $this->getSession()->get('cart', []);
        unset($cart[$productId]);
        $this->getSession()->set('cart', $cart);
    }

    public function clearCart(): void
    {
        $this->getSession()->remove('cart');
        $this->couponService->removeCoupon();
    }

    public function getTotal(): float
    {
        return array_reduce(
            $this->getCart(),
            fn(float $total, array $item) => $total + ($item['product']->getPrice() * $item['quantity']),
            0.0
        );
    }

    public function getCartCount(): int
    {
        return array_sum($this->getSession()->get('cart', []));
    }

    public function applyCoupon(string $code): array
    {
        return $this->couponService->applyCoupon($code);
    }

    public function getCartSummary(): array
    {
        $subtotal = $this->getTotal();
        $coupon = $this->couponService->getAppliedCoupon();
        
        return [
            'subtotal' => $subtotal,
            'shipping' => $subtotal >= $this->freeShippingThreshold ? 0.0 : $this->shippingCost,
            'discount' => $coupon['discount'] ?? 0.0,
            'total' => max(0, $subtotal - ($coupon['discount'] ?? 0.0)),
            'coupon' => $coupon,
        ];
    }

    private function getSession()
    {
        return $this->requestStack->getSession();
    }
}