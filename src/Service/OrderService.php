<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    private EntityManagerInterface $entityManager;
    private CartService $cartService;

    public function __construct(
        EntityManagerInterface $entityManager,
        CartService $cartService
    ) {
        $this->entityManager = $entityManager;
        $this->cartService = $cartService;
    }

    /**
     * Créer une commande à partir du panier
     */
    public function createOrderFromCart(User $user, float $taxAmount = 0, float $shippingAmount = 0): Order
    {
        $order = new Order();
        $order->setUser($user);
        $order->setStatus('pending');
        $order->setTaxAmount((string) $taxAmount);
        $order->setShippingAmount((string) $shippingAmount);

        // Ajouter les articles du panier à la commande
        $cartItems = $this->cartService->getCart();
        foreach ($cartItems as $item) {
            $orderItem = new OrderItem();
            $orderItem->setVariant($item['variant']);
            $orderItem->setQuantity($item['quantity']);
            $orderItem->setUnitPrice((string) $item['unitPrice']);
            $order->addItem($orderItem);
        }

        // Calculer le total
        $order->calculateTotal();

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        // Vider le panier après création
        $this->cartService->clear();

        return $order;
    }

    /**
     * Mettre à jour le statut de paiement
     */
    public function markAsPaid(Order $order, string $paymentIntentId): void
    {
        $order->setStatus('paid');
        $order->setPaidAt(new \DateTimeImmutable());
        $order->setStripePaymentIntentId($paymentIntentId);

        $this->entityManager->flush();
    }

    /**
     * Marquer comme expédiée
     */
    public function markAsShipped(Order $order): void
    {
        $order->setStatus('shipped');
        $order->setShippedAt(new \DateTimeImmutable());

        $this->entityManager->flush();
    }

    /**
     * Annuler une commande
     */
    public function cancelOrder(Order $order): void
    {
        $order->setStatus('cancelled');
        $this->entityManager->flush();
    }
}
