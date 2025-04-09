<?php

namespace App\Repository;

use App\Entity\PointRelais;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PointRelais>
 *
 * @method PointRelais|null find($id, $lockMode = null, $lockVersion = null)
 * @method PointRelais|null findOneBy(array $criteria, array $orderBy = null)
 * @method PointRelais[]    findAll()
 * @method PointRelais[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PointRelaisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PointRelais::class);
    }

    public function save(PointRelais $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PointRelais $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return PointRelais[] Returns an array of PointRelais objects
     */
    public function findByExampleField($value): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneBySomeField($value): ?PointRelais
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllPointRelais()
{
    return $this->createQueryBuilder('p')
        ->getQuery()
        ->getResult();
}


}
