<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order')]
#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    #[Route('/{id}', name: 'app_order_show')]
    public function show(Order $order, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($order->getUser()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('You do not have access to this order.');
        }

        return $this->render('profile/order_show.html.twig', [
            'order' => $order,
            'user' => $user,
        ]);
    }
}