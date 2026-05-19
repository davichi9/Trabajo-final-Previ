<?php

namespace App\Repository;

use App\Entity\Clientes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Clientes>
 *
 * @method Clientes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Clientes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Clientes[]    findAll()
 * @method Clientes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Clientes::class);
    }

    /**
     * Search clients by any field (id, nombre, apellidos, telefonoNumero, email, domicilio)
     */
    public function searchClientes(?string $searchTerm): array
    {
        if (!$searchTerm) {
            return $this->findAll();
        }

        $qb = $this->createQueryBuilder('c');
        
        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->like('c.id', ':searchTerm'),
                $qb->expr()->like('c.nombre', ':searchTerm'),
                $qb->expr()->like('c.apellidos', ':searchTerm'),
                $qb->expr()->like('c.telefonoNumero', ':searchTerm'),
                $qb->expr()->like('c.email', ':searchTerm'),
                $qb->expr()->like('c.domicilio', ':searchTerm')
            )
        )
        ->setParameter('searchTerm', '%' . $searchTerm . '%');

        return $qb->getQuery()->getResult();
    }
}
