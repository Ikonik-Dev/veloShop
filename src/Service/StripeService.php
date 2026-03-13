<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Customer;

class StripeService
{
    private string $stripeSecretKey;

    public function __construct(ParameterBagInterface $params)
    {
        $this->stripeSecretKey = $params->get('stripe_secret_key');
        Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     * Créer un client Stripe
     */
    public function createOrGetCustomer(User $user): string
    {
        if ($user->getStripeCustomerId()) {
            return $user->getStripeCustomerId();
        }

        $customer = Customer::create([
            'email' => $user->getEmail(),
            'name' => $user->getFirstName() . ' ' . $user->getLastName(),
            'phone' => $user->getPhone(),
            'address' => [
                'line1' => $user->getAddress(),
                'city' => $user->getCity(),
                'postal_code' => $user->getPostalCode(),
                'country' => $user->getCountry(),
            ],
        ]);

        return $customer->id;
    }

    /**
     * Créer une session de paiement Stripe
     */
    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): string
    {
        $lineItems = [];
        foreach ($order->getItems() as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item->getVariant()->getBike()->getName() . ' - ' . $item->getVariant()->getColor(),
                        'images' => [], // Ajoutez les URLs des images si disponibles
                    ],
                    'unit_amount' => (int) ((float) $item->getUnitPrice() * 100),
                ],
                'quantity' => $item->getQuantity(),
            ];
        }

        // Ajouter les frais de port si applicable
        if ((float) $order->getShippingAmount() > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Frais de port',
                    ],
                    'unit_amount' => (int) ((float) $order->getShippingAmount() * 100),
                ],
                'quantity' => 1,
            ];
        }

        $session = Session::create([
            'customer' => $order->getUser()->getStripeCustomerId(),
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'order_id' => (string)$order->getId(),
            ],
        ]);

        return $session->id;
    }

    /**
     * Récupérer une session de paiement
     */
    public function getCheckoutSession(string $sessionId): \Stripe\Checkout\Session
    {
        return Session::retrieve($sessionId);
    }
}