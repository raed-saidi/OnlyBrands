<?php

namespace App\Controller;

use App\Service\CouponService;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CouponController extends AbstractController
{
    private $couponService;
    private $cartService;

    public function __construct(CouponService $couponService, CartService $cartService)
    {
        $this->couponService = $couponService;
        $this->cartService = $cartService;
    }

    #[Route('/coupon/apply', name: 'coupon_apply', methods: ['POST'])]
    public function apply(Request $request): JsonResponse
    {
        $code = $request->request->get('code');

        if (!$code) {
            new JsonResponse([
                'success' => false,
                'message' => 'No code provided.'
            ]);
        }

        $total = $this->cartService->getCartTotal();
        $result = $this->couponService->applyCoupon($code, $total);

        return new JsonResponse($result);
    }

    #[Route('/coupon/remove', name: 'coupon_remove', methods: ['POST'])]
    public function remove(): JsonResponse
    {
        $this->couponService->removeCoupon();

        return new JsonResponse([
            'success' => true,
            'message' => 'Coupon removed.'
        ]);
    }
}
