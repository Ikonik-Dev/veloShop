<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findByUser($userId)
    {
        return $this->findBy(['user' => $userId], ['createdAt' => 'DESC']);
    }

    public function findByStatus(string $status)
    {
        return $this->findBy(['status' => $status], ['createdAt' => 'DESC']);
    }
}
