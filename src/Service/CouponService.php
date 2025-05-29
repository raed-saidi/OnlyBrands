<?php

namespace App\Service;

use App\Entity\Coupon;
use App\Repository\CouponRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CouponService
{
    private $couponRepository;
    private $entityManager;
    private $requestStack;

    public function __construct(
        CouponRepository $couponRepository,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack
    ) {
        $this->couponRepository = $couponRepository;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    public function applyCoupon(string $code, float $subtotal): array
    {
        $response = [
            'success' => false,
            'message' => '',
            'discount' => 0,
        ];

        $coupon = $this->couponRepository->findValidCouponByCode($code);

        if (!$coupon) {
            $response['message'] = 'Invalid or expired coupon code.';
            return $response;
        }

        if (!$coupon->isValid()) {
            $response['message'] = 'This coupon is no longer valid.';
            return $response;
        }

        if ($coupon->getMinimumPurchase() !== null && $subtotal < $coupon->getMinimumPurchase()) {
            $response['message'] = sprintf(
                'This coupon requires a minimum purchase of %s TND.',
                number_format($coupon->getMinimumPurchase(), 3)
            );
            return $response;
        }

        $discount = $coupon->calculateDiscount($subtotal);

        $this->getSession()->set('coupon', [
            'code' => $coupon->getCode(),
            'discount' => $discount,
            'type' => $coupon->getType(),
            'value' => $coupon->getValue(),
        ]);

        $response['success'] = true;
        $response['discount'] = $discount;

        if ($coupon->getType() === 'percentage') {
            $response['message'] = sprintf('Coupon applied! %s%% discount.', $coupon->getValue());
        } else {
            $response['message'] = sprintf('Coupon applied! %s TND discount.', number_format($coupon->getValue(), 3));
        }

        return $response;
    }

    public function removeCoupon(): void
    {
        $this->getSession()->remove('coupon');
    }

    public function getAppliedCoupon(): ?array
    {
        return $this->getSession()->get('coupon');
    }

    public function recordCouponUsage(string $code): void
    {
        $coupon = $this->couponRepository->findOneBy(['code' => $code]);

        if ($coupon) {
            $coupon->incrementUsageCount();
            $coupon->setUpdatedAt(new \DateTime());
            $this->entityManager->persist($coupon);
            $this->entityManager->flush();
        }
    }
}