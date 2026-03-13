<?php

namespace App\Service;

use App\Entity\BikeVariant;
use App\Repository\BikeVariantRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const CART_SESSION_KEY = 'cart_items';

    private RequestStack $requestStack;
    private BikeVariantRepository $variantRepository;

    public function __construct(RequestStack $requestStack, BikeVariantRepository $variantRepository)
    {
        $this->requestStack = $requestStack;
        $this->variantRepository = $variantRepository;
    }

    /**
     * Ajouter un article au panier
     */
    public function addToCart(int $variantId, int $quantity = 1): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_SESSION_KEY, []);

        if (isset($cart[$variantId])) {
            $cart[$variantId]['quantity'] += $quantity;
        } else {
            $variant = $this->variantRepository->find($variantId);
            if (!$variant) {
                throw new \InvalidArgumentException("Variant avec l'ID $variantId n'a pas été trouvé.");
            }
            $cart[$variantId] = [
                'variantId' => $variantId,
                'quantity' => $quantity,
            ];
        }

        $session->set(self::CART_SESSION_KEY, $cart);
    }

    /**
     * Retirer un article du panier
     */
    public function removeFromCart(int $variantId): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_SESSION_KEY, []);
        unset($cart[$variantId]);
        $session->set(self::CART_SESSION_KEY, $cart);
    }

    /**
     * Mettre à jour la quantité
     */
    public function updateQuantity(int $variantId, int $quantity): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_SESSION_KEY, []);

        if ($quantity <= 0) {
            $this->removeFromCart($variantId);
        } elseif (isset($cart[$variantId])) {
            $cart[$variantId]['quantity'] = $quantity;
            $session->set(self::CART_SESSION_KEY, $cart);
        }
    }

    /**
     * Obtenir le panier complet avec les détails des produits
     */
    public function getCart(): array
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_SESSION_KEY, []);
        $items = [];

        foreach ($cart as $variantId => $item) {
            $variant = $this->variantRepository->find($variantId);
            if ($variant) {
                $unitPrice = (float)$variant->getBasePrice();
                $items[] = [
                    'variant' => $variant,
                    'quantity' => $item['quantity'],
                    'unitPrice' => $unitPrice,
                    'total' => $unitPrice * $item['quantity'],
                ];
            }
        }

        return $items;
    }

    /**
     * Calcul du total du panier
     */
    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->getCart() as $item) {
            $total += $item['total'];
        }
        return $total;
    }

    /**
     * Obtenir le nombre d'articles
     */
    public function getCount(): int
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_SESSION_KEY, []);
        return array_sum(array_column($cart, 'quantity', 0));
    }

    /**
     * Vider le panier
     */
    public function clear(): void
    {
        $session = $this->requestStack->getSession();
        $session->remove(self::CART_SESSION_KEY);
    }

    /**
     * Vérifier si le panier est vide
     */
    public function isEmpty(): bool
    {
        return count($this->getCart()) === 0;
    }
}