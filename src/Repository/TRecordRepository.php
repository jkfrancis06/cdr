<?php

namespace App\Repository;

use App\Entity\TRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TRecord|null find($id, $lockMode = null, $lockVersion = null)
 * @method TRecord|null findOneBy(array $criteria, array $orderBy = null)
 * @method TRecord[]    findAll()
 * @method TRecord[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TRecord::class);
    }

    public function dumpAll()  {

        /* INSERT INTO test_import_two (name, name1, name2)
        (SELECT name, name1, name2 FROM test_import_one WHERE id = 2) */


        $separator = "\r\n'";

        $db = $this->getEntityManager();
        $query = "INSERT INTO trecord (num_a, num_b, duration, day_time,data_type,flux_appel)
              SELECT  num_a, num_b ,duration,day_time,data_type,flux_appel
              FROM dump_t";
        $stmt = $db->getConnection()->prepare($query);
        $params = array(
        );
        $qr = $stmt->execute($params);

        return $qr;

    }

    // retourne toutes les communications d'un certain numero

    public function matchAllRecordCriteria($data_type,$number){
        try {
            $qr = $this->createQueryBuilder('trecord')
                ->select('trecord')
                ->from('App:TRecord','trecord')
                ->where('trecord.data_type = :data_type')
                ->andWhere('trecord.num_a = :num')
                ->setParameters([
                    'num' => $number,
                    $data_type => $data_type
                ])
                ->getQuery()
                ->execute();
            $res = [];
            $res["status"] = 200;
            $res["data"] = $qr;
            return $res;
        }catch (Exception $exception){
            $res = [];
            $res["status"] = 500;
            return $res;
        }

    }




    // compte les communications d'un certain numero

    public function countAllCommuniactions($number){
        try {
            $qr = $this->createQueryBuilder('t')
                ->select('count(t.id)')
                ->where('t.num_a = :num')
                ->setParameters([
                    'num' => $number
                ])
                ->getQuery()
                ->getSingleScalarResult();
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


    // compte les communications d'un certain numero basé sur des criteres voix ou sms

    public function countAllCriteriaCommuniactions($number,$data_type){
        try {
            $qr = $this->createQueryBuilder('trecord')
                ->select('count(trecord.id)')
                ->where('trecord.num_a = :num')
                ->andWhere('trecord.data_type = :data_type')
                ->setParameters([
                    'num' => $number,
                    'data_type' => $data_type
                ])
                ->getQuery()
                ->getSingleScalarResult();
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

    // compte les appels sortant ou entrant

    public function countAllSentReceivedCalls($number,$data_type,$flux){
        try {
            $qr = $this->createQueryBuilder('t')
                ->select('count(t.id)')
                ->where('t.num_a = :num')
                ->andWhere('t.data_type = :data_type AND t.flux_appel = :flux_appel ')
                ->setParameters([
                    'num' => $number,
                    'data_type' => $data_type,
                    'flux_appel' => $flux
                ])
                ->getQuery()
                ->getSingleScalarResult();
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


    // get favorites number

    public function getFavoritesNumberDateRange($number,$start,$end) {
        try {
            $qr = $this->createQueryBuilder('t')
                ->select('t.num_b, sum(t.duration) as dur , count(t.num_b) as nb')
                ->where('t.num_a = :num')
                ->andWhere('t.day_time BETWEEN :start AND :end')
                ->groupBy('t.num_b')
                ->orderBy('dur','DESC')
                ->addOrderBy('nb', 'DESC')
                ->setMaxResults(15)
                ->setParameters([
                    'num' => $number,
                    'start' => $start,
                    'end' => $end
                ])
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


    public function getFavoritesNumber($number) {
        try {
            $qr = $this->createQueryBuilder('t')
                ->select('t.num_b, sum(t.duration) as dur , count(t.num_b) as nb')
                ->where('t.num_a = :num')
                ->groupBy('t.num_b')
                ->orderBy('dur','DESC')
                ->addOrderBy('nb', 'DESC')
                ->setMaxResults(15)
                ->setParameters([
                    'num' => $number
                ])
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


    // /**
    //  * @return TRecord[] Returns an array of TRecord objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TRecord
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
