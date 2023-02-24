<?php

namespace App\Repository;

use App\Entity\CPerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CPerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method CPerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method CPerson[]    findAll()
 * @method CPerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CPersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CPerson::class);
    }

    // /**
    //  * @return CPerson[] Returns an array of CPerson objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CPerson
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
