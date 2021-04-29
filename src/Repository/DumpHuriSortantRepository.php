<?php

namespace App\Repository;

use App\Entity\DumpHuriSortant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DumpHuriSortant|null find($id, $lockMode = null, $lockVersion = null)
 * @method DumpHuriSortant|null findOneBy(array $criteria, array $orderBy = null)
 * @method DumpHuriSortant[]    findAll()
 * @method DumpHuriSortant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DumpHuriSortantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DumpHuriSortant::class);
    }

    // /**
    //  * @return DumpHuriSortant[] Returns an array of DumpHuriSortant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DumpHuriSortant
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
