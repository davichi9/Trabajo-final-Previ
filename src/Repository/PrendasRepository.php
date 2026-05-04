<?php

namespace App\Repository;

use App\Entity\Prendas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Prendas>
 *
 * @method Prendas|null find($id, $lockMode = null, $lockVersion = null)
 * @method Prendas|null findOneBy(array $criteria, array $orderBy = null)
 * @method Prendas[]    findAll()
 * @method Prendas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrendasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prendas::class);
    }
}
