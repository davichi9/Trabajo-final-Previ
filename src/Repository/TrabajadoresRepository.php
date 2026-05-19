<?php

namespace App\Repository;

use App\Entity\Trabajadores;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trabajadores>
 *
 * @method Trabajadores|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trabajadores|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trabajadores[]    findAll()
 * @method Trabajadores[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrabajadoresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trabajadores::class);
    }

    public function searchTrabajadores(string $searchTerm = ''): array
    {
        $qb = $this->createQueryBuilder('t');

        if ($searchTerm) {
            $qb->where('t.id LIKE :search')
                ->orWhere('t.nombre LIKE :search')
                ->orWhere('t.apellidos LIKE :search')
                ->orWhere('t.telefonoNumero LIKE :search')
                ->orWhere('t.email LIKE :search')
                ->orWhere('t.rol LIKE :search')
                ->setParameter('search', '%' . $searchTerm . '%');
        }

        return $qb->orderBy('t.nombre', 'ASC')->getQuery()->getResult();
    }
}
