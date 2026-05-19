<?php

namespace App\Repository;

use App\Entity\Pedidos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;

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

    public function searchPedidos(?string $searchTerm): array
    {
        if (!$searchTerm) {
            return $this->findAll();
        }

        $qb = $this->createQueryBuilder('p')
            ->join('p.cliente', 'c')
            ->where(
                'p.id LIKE :s OR p.estado LIKE :s OR p.contenido LIKE :s OR c.nombre LIKE :s OR c.apellidos LIKE :s OR c.telefonoNumero LIKE :s'
            )
            ->setParameter('s', '%' . $searchTerm . '%');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get revenue grouped by week for a given month
     */
    public function getWeeklyRevenueByMonth(int $year, int $month, bool $onlyPaid = true): array
    {
        $startDate = new \DateTime("$year-$month-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');
        $endDate->setTime(23, 59, 59);

        $sql = '
            SELECT 
                CONCAT(YEAR(p.fecha_salida), "-W", LPAD(WEEK(p.fecha_salida), 2, "0")) as week_key,
                WEEK(p.fecha_salida) as week_number,
                SUM(p.precio) as totalRevenue,
                MIN(p.fecha_salida) as weekStart,
                MAX(p.fecha_salida) as weekEnd
            FROM pedidos p
            WHERE p.fecha_salida IS NOT NULL
            AND p.fecha_salida >= :startDate
            AND p.fecha_salida <= :endDate
        ';

        if ($onlyPaid) {
            $sql .= ' AND p.pagado = 1';
        }

        $sql .= ' GROUP BY YEAR(p.fecha_salida), WEEK(p.fecha_salida) ORDER BY p.fecha_salida ASC';

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('week_key', 'week_key');
        $rsm->addScalarResult('week_number', 'week_number');
        $rsm->addScalarResult('totalRevenue', 'totalRevenue');
        $rsm->addScalarResult('weekStart', 'weekStart', 'datetime');
        $rsm->addScalarResult('weekEnd', 'weekEnd', 'datetime');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('startDate', $startDate);
        $query->setParameter('endDate', $endDate);

        return $query->getResult();
    }
}
