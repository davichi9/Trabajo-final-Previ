<?php

namespace App\Repository;

use App\Entity\Pedidos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pedidos>
 *
 * @method Pedidos|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pedidos|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pedidos[]    findAll()
 * @method Pedidos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PedidosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pedidos::class);
    }
}
