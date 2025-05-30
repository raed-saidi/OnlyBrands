<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Form\CheckoutType;
use App\Service\CartService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/checkout')]
#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    private StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    #[Route('/', name: 'checkout_index')]
    public function index(Request $request, CartService $cartService, EntityManagerInterface $entityManager): Response
    {
        $cart = $cartService->getCart();

        if (count($cart) === 0) {
            $this->addFlash('error', 'Your cart is empty');
            return $this->redirectToRoute('cart_index');
        }

        $order = new Order();
        $order->setUser($this->getUser());
        $order->setTotal($cartService->getTotal());
        $order->setStatus('pending');
        $order->setCreatedAt(new \DateTime());

        $form = $this->createForm(CheckoutType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paymentMethod = $request->request->get('payment_method');

            // Création des OrderItems et mise à jour du stock
            foreach ($cart as $item) {
                $product = $item['product'];
                $quantity = $item['quantity'];

                // Vérification stock
                $newStock = $product->getStock() - $quantity;
                if ($newStock < 0) {
                    $this->addFlash('error', 'Insufficient stock for ' . $product->getName());
                    return $this->redirectToRoute('checkout_index');
                }

                // Création OrderItem
                $orderItem = new OrderItem();
                $orderItem->setProduct($product);
                $orderItem->setQuantity($quantity);
                $orderItem->setPrice($product->

getPrice());
                $orderItem->setOrder($order);
                $order->addItem($orderItem);

                // Mise à jour stock
                $product->setStock($newStock);
                $entityManager->persist($product);
            }

            if ($paymentMethod === 'credit') {
                $amount = (int) ($cartService->getTotal() * 1000); // Convert TND to millimes for Stripe
                try {
                    $paymentIntent = $this->stripeService->createPaymentIntent($amount);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur de paiement : ' . $e->getMessage());
                    return $this->redirectToRoute('checkout_index');
                }

                $order->setStatus('pending_payment');
                $entityManager->persist($order);
                $entityManager->flush();

                // Affiche la page paiement avec Stripe
                return $this->render('checkout/payment.html.twig', [
                    'order' => $order,
                    'clientSecret' => $paymentIntent->client_secret,
                    'stripePublicKey' => $this->getParameter('stripe_public_key'),
                ]);
            }

            // Paiement hors ligne (ex: PayPal or other)
            $order->setStatus('paid');
            $entityManager->persist($order);
            $entityManager->flush();

            $cartService->clear();

            return $this->redirectToRoute('checkout_success', ['id' => $order->getId()]);
        }

        return $this->render('checkout/index.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart,
            'total' => $cartService->getTotal(),
        ]);
    }

    #[Route('/success/{id}', name: 'checkout_success')]
    public function success(Order $order, CartService $cartService): Response
    {
        if ($order->getUser() !== $this->getUser()) {
            throw $_pinpoint_the_issuecontroller->createAccessDeniedException('You are not authorized to view this order');
        }

        // Nettoyage panier après paiement réussi (Stripe ou autre)
        $cartService->clear();

        return $this->render('checkout/success.html.twig', [
            'order' => $order,
        ]);
    }
}