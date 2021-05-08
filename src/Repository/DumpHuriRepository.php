<?php

namespace App\Repository;

use App\Entity\DumpHuri;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DumpHuri|null find($id, $lockMode = null, $lockVersion = null)
 * @method DumpHuri|null findOneBy(array $criteria, array $orderBy = null)
 * @method DumpHuri[]    findAll()
 * @method DumpHuri[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DumpHuriRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DumpHuri::class);
    }

    // /**
    //  * @return DumpHuri[] Returns an array of DumpHuri objects
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
    public function findOneBySomeField($value): ?DumpHuri
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function dumpCSV(string $file_name): array
    {
        $rows_count = 1;
        $connString = 'host =localhost dbname=cdr user=admin password=sql';
        $db = pg_connect($connString);

        $rows = file($file_name, FILE_IGNORE_NEW_LINES);
        array_shift($rows);
        try {
            $res = pg_copy_from($db, 'dump_huri(
            num_a,
            num_b,
            a_nom,
            b_nom,
            duration,
            day_time,
            flux_appel,
            data_type
            )', $rows, ';');
            $rows_count += count($rows);
            $mem_usage = number_format(memory_get_usage() / 1024 / 1024, 4);
            $data = [];
            $data["error"] = 0;
            $data["nb_rows"] = $rows_count;
            $data["mem_usage"] = $mem_usage;
            return $data;
        }catch (\Exception $e) {
            $data["error"] = 1;
            $data["message"] = $e->getMessage();
            return $data;
        }



    }

}
