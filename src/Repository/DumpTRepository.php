<?php

namespace App\Repository;

use App\Entity\DumpT;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DumpT|null find($id, $lockMode = null, $lockVersion = null)
 * @method DumpT|null findOneBy(array $criteria, array $orderBy = null)
 * @method DumpT[]    findAll()
 * @method DumpT[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DumpTRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DumpT::class);
    }

    // /**
    //  * @return DumpT[] Returns an array of DumpT objects
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
    public function findOneBySomeField($value): ?DumpT
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function dumpCSV(string $file_name,$fileDelimiter): array
    {
        $rows_count = 1;
        $connString = 'host =localhost dbname=cdr user=admin password=sql';
        $db = pg_connect($connString);

        $rows = file($file_name, FILE_IGNORE_NEW_LINES);
        {
            $res = pg_copy_from($db, 'dump_t(
            flux_appel,
            data_type,
            day_time,
            duration,
            imei,
            num_a,
            num_b,
            cell_id,
            location_num_a,
            brand,
            os,
            model,
            a_nom,
            a_piece,
            a_adresse,
            b_nom,
            b_piece,
            b_adresse
            )', $rows, $fileDelimiter);
            $rows_count += count($rows);
            $mem_usage = number_format(memory_get_usage() / 1024 / 1024, 4);
            $data = [];
            $data["error"] = 0;
            $data["nb_rows"] = $rows_count;
            $data["mem_usage"] = $mem_usage;
            return $data;
        }



    }

    public function deleteNumberRecords($number) {


        $qr = $this->createQueryBuilder('t')
            ->delete()
            ->where('t.num_a LIKE :number')
            ->setParameters([
                'number' => $number
            ])
            ->getQuery()
            ->getResult();

    }

    public function deleteUnwantedRecords($number) {


        $qr = $this->createQueryBuilder('trecord')
            ->delete()
            ->where('trecord.num_b LIKE :number')
            ->setParameters([
                'number' => $number
            ])
            ->getQuery()
            ->getResult();

    }


    public function truncateTable($class): array
    {
        $em = $this->getEntityManager();
        $classMetaData = $em->getClassMetadata($class);
        $connection = $em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->beginTransaction();

        $data = [];

        try {
            $q = $dbPlatform->getTruncateTableSql($classMetaData->getTableName());
            $connection->executeUpdate($q);
            $connection->commit();
        }
        catch (\Exception $e) {
            $connection->rollback();
            $data["error"] = 1;
            return $data;
        }

        $data["error"] = 0;
        return $data;


    }


    public function getNumberName($number){
        try {
            $qr = $this->createQueryBuilder('t')
                ->select('t.a_nom')
                ->where('t.num_a = :number')
                ->setParameters([
                    'number' => $number
                ])
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();
            $res = [];
            $res["status"] = 200;
            $res["data"] = $qr;
            return $res;
        }catch (Exception $exception){
            $res = [];
            $res["status"] = 500;
            $res["data"] = 0;
            return $res;
        }
    }

    public function standardTEncoding($dir,$file_name,$fileDelimiter){

        $connString = 'host =localhost dbname=cdr user=admin password=sql';

        $source = $dir.'/'.$file_name;
        $destination = $dir.'/copy/'.$file_name;
        copy($source,$destination);

        if (($handle = fopen($destination, 'r')) !== FALSE  && ($outFile = fopen($source, "w") )!== false)
        {

            // Loop through each row
            while (($row = fgetcsv($handle,"",$fileDelimiter)) !== FALSE)
            {

                foreach ($row as &$item){
                    $item = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $item);
                    $item = $this->removeSpecialChar($item);
                }


                fputcsv($outFile, $row,";"," ",'');

            }

            // Close the file pointer
            fclose($handle);
            fclose($outFile);
            unlink($destination);
        }

    }

    function removeSpecialChar($str){

        setlocale(LC_ALL, 'fr_FR.UTF-8');

// Convert the codepoints to entities
        $str = preg_replace("/\\\\u([0-9a-fA-F]{4})/", "&#x\\1;", $str);

// Convert the entities to a UTF-8 string
        $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');

// Convert the UTF-8 string to an ISO-8859-1 string
        return iconv("UTF-8", "ISO-8859-1//TRANSLIT", $str);

    }

}
