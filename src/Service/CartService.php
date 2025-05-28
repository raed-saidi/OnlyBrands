<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private RequestStack $requestStack;
    private ProductRepository $productRepository;
    private CouponService $couponService;

    public function __construct(
        RequestStack $requestStack,
        ProductRepository $productRepository,
        CouponService $couponService
    ) {
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
        $this->couponService = $couponService;
    }

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    /**
     * Returns cart items with actual Product entities and quantities
     *
     * @return array [ ['product' => Product, 'quantity' => int], ... ]
     */
    public function getCart(): array
    {
        $cart = $this->getSession()->get('cart', []);
        $cartWithProducts = [];

        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);
            if ($product) {
                $cartWithProducts[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                ];
            }
        }

        return $cartWithProducts;
    }

    /**
     * Add a product to cart or increase quantity if already present
     */
    public function addToCart(int $id, int $quantity = 1): void
    {
        $cart = $this->getSession()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id] += $quantity;
        } else {
            $cart[$id] = $quantity;
        }

        $this->getSession()->set('cart', $cart);
    }

    /**
     * Update the quantity of a product in the cart (minimum 1)
     */
    public function updateQuantity(int $id, int $quantity): void
    {
        $cart = $this->getSession()->get('cart', []);
        if (isset($cart[$id])) {
            $cart[$id] = max(1, $quantity);
        }
        $this->getSession()->set('cart', $cart);
    }

    /**
     * Remove a product from the cart
     */
    public function removeFromCart(int $id): void
    {
        $cart = $this->getSession()->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
        }
        $this->getSession()->set('cart', $cart);
    }

    /**
     * Clear the whole cart and remove any applied coupon
     */
    public function clearCart(): void
    {
        $this->getSession()->remove('cart');
        $this->couponService->removeCoupon();
    }

    /**
     * Calculate total price of the cart before shipping and discounts
     */
    public function getCartTotal(): float
    {
        $total = 0;
        foreach ($this->getCart() as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }
        return $total;
    }

    /**
     * Get total quantity of all items in the cart
     */
    public function getCartCount(): int
    {
        $count = 0;
        $cart = $this->getSession()->get('cart', []);
        foreach ($cart as $quantity) {
            $count += $quantity;
        }
        return $count;
    }

    /**
     * Get cart summary including subtotal, shipping, discount, total, and coupon details
     */
    public function getCartSummary(): array
    {
        $subtotal = $this->getCartTotal();
        $appliedCoupon = $this->couponService->getAppliedCoupon();
        $discount = $appliedCoupon ? $appliedCoupon['discount'] : 0;

        $shippingCost = ($subtotal >= 150) ? 0 : 7.0;

        $total = max(0, $subtotal + $shippingCost - $discount);

        return [
            'subtotal' => $subtotal,
            'shipping' => $shippingCost,
            'discount' => $discount,
            'total' => $total,
            'coupon' => $appliedCoupon,
        ];
    }
}
