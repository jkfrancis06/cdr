<?php

namespace App\Repository;

use App\Entity\TRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Query\Expr\Join;
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

    public function dumpTelmaTrecord()  {

        /* INSERT INTO test_import_two (name, name1, name2)
        (SELECT name, name1, name2 FROM test_import_one WHERE id = 2) */


        $separator = "\r\n'";

        $db = $this->getEntityManager();
        $query = "INSERT INTO trecord (num_a, num_b, duration, day_time,data_type,flux_appel, a_nom, b_nom)
              SELECT  num_a, num_b ,duration,day_time,data_type,flux_appel,a_nom,b_nom
              FROM dump_t";
        $stmt = $db->getConnection()->prepare($query);
        $params = array(
        );
        $qr = $stmt->execute($params);

        return $qr;

    }

    public function dumpHuriTrecord()  {

        /* INSERT INTO test_import_two (name, name1, name2)
        (SELECT name, name1, name2 FROM test_import_one WHERE id = 2) */


        $separator = "\r\n'";

        $db = $this->getEntityManager();
        $query = "INSERT INTO trecord (num_a, num_b, duration, day_time,data_type,flux_appel, a_nom, b_nom)
              SELECT  num_a, num_b ,duration,day_time,data_type,flux_appel,a_nom,b_nom
              FROM dump_huri";
        $stmt = $db->getConnection()->prepare($query);
        $params = array(
        );
        $qr = $stmt->execute($params);

        return $qr;

    }

    public function parseNames(){
        $db = $this->getEntityManager();
        $query = "UPDATE trecord t
                    SET b_nom = 
                    case when b_nom = '' or b_nom = '0'
	                    then (select b_nom b 
		                      from trecord u
		                      where u.num_a like CONCAT(SUBSTRING (t.num_b, 1, 1),'%') AND u.num_b like t.num_b
                              group by b
                              limit 1)
	                    else b_nom
                    end
                ";
        $stmt = $db->getConnection()->prepare($query);
        $params = array(
        );
        $qr = $stmt->execute($params);

        return $qr;


    }


    public function restoreRecords($number)  {

        /* INSERT INTO test_import_two (name, name1, name2)
        (SELECT name, name1, name2 FROM test_import_one WHERE id = 2) */


        $separator = "\r\n'";

        $db = $this->getEntityManager();
        $query1 = "INSERT INTO trecord (num_a, num_b, duration, day_time,data_type,flux_appel, a_nom, b_nom)
              SELECT  num_a, num_b ,duration,day_time,data_type,flux_appel,a_nom,b_nom
              FROM dump_huri
              WHERE num_b = :number";

        $query2 = "INSERT INTO trecord (num_a, num_b, duration, day_time,data_type,flux_appel, a_nom, b_nom)
              SELECT  num_a, num_b ,duration,day_time,data_type,flux_appel,a_nom,b_nom
              FROM dump_t
              WHERE num_b = :number";
        $stmt1 = $db->getConnection()->prepare($query1);
        $stmt2 = $db->getConnection()->prepare($query2);
        $params = array(
            'number' => $number
        );
        $qr1 = $stmt1->execute($params);
        $qr2 = $stmt2->execute($params);

    }


    public function deleteNumberRecords($number) {


        $qr = $this->createQueryBuilder('trecord')
            ->delete()
            ->where('trecord.num_a LIKE :number')
            ->setParameters([
                'number' => $number
            ])
            ->getQuery()
            ->getResult();

    }


    public function checkIdentity($number) {

        $first_char = substr($number,0,1);

        $qr = $this->createQueryBuilder('trecord')
            ->select('trecord.b_nom')
            ->where('trecord.num_a LIKE :char')
            ->andWhere('trecord.num_b LIKE :number')
            ->setParameters([
                'char' => $first_char.'%',
                'number' => $number
            ])
            ->getQuery()
            ->getResult();


        if (sizeof($qr) == 0) {
            return "PROBABLEMENT NON ID";
        }
        if (sizeof($qr) != 0 && ($qr[0]["b_nom"] == "" || $qr[0]["b_nom"] == "0") ) {
            return "NON IDENTIFIE";
        }
        if (sizeof($qr) != 0 && $qr[0]["b_nom"] != "" && $qr[0]["b_nom"] != "0") {
            return $qr[0]["b_nom"];
        }
    }



    public function countRecords($number) {


    $qr = $this->createQueryBuilder('trecord')
        ->select('count(trecord.id)')
        ->where('trecord.num_b LIKE :number')
        ->setParameters([
            'number' => $number
        ])
        ->getQuery()
        ->getSingleScalarResult();

    return $qr;

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






    // paginator query

    public function paginateMatriceDetails($num_a,$num_b,$start,$end){
        if ($start == 0 || $end == 0){
            return $this->createQueryBuilder('trecord')
                ->select('trecord')
                ->where('trecord.num_a = :num_a')
                ->andWhere('trecord.num_b = :num_b')
                ->setParameters([
                    'num_a' => $num_a,
                    'num_b' => $num_b
                ]);
        }else{
            return $this->createQueryBuilder('trecord')
                ->select('trecord')
                ->where('trecord.num_a = :num_a')
                ->andWhere('trecord.num_b = :num_b')
                ->andWhere('trecord.day_time BETWEEN :start AND :end')
                ->setParameters([
                    'num_a' => $num_a,
                    'num_b' => $num_b,
                    'start' => $start,
                    'end' => $end
                ]);
        }

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
                ->select('t.num_b, t.b_nom , sum(t.duration) as dur, count(t.num_b) as nb')
                ->where('t.num_a = :num')
                ->groupBy('t.num_b')
                ->addGroupBy('t.b_nom')
                ->orderBy('nb', 'DESC')
                ->addOrderBy('dur','DESC')
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




    // count communications between two numbers

    public function countCommunicationBetween(string $num_a, string $num_b){
        try {
            $qr = $this->createQueryBuilder('t')
                ->select('count(t.id)')
                ->where('t.num_a = :num_a AND t.num_b = :num_b')
                ->orWhere('t.num_a = :num_a AND t.num_b = :num_b')
                ->setParameters([
                    'num_a' => $num_a,
                    'num_b' => $num_b
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

    // count communications between two numbers

    public function getCommunicationBetween(string $num_a, string $num_b){
        try {
            $qr = $this->createQueryBuilder('t')
                ->select('count(t.id)')
                ->where('t.num_a = :num_a AND t.num_b = :num_b')
                ->orWhere('t.num_a = :num_a AND t.num_b = :num_b')
                ->setParameters([
                    'num_a' => $num_a,
                    'num_b' => $num_b
                ])
                ->getQuery()
                ->getSingleScalarResult();

            return $qr;
        }catch (Exception $exception){
            $res = [];
            $res["status"] = 500;
            $res["data"] = 0;
            return $res;
        }
    }


    // count communications between two numbers and dates

    public function countCommunicationDateBetween(string $num_a, string $num_b, $start, $end){
        try {
            $qr = $this->createQueryBuilder('t')
                ->select('count(t.id)')
                ->where('t.num_a = :num_a AND t.num_b = :num_b')
                ->andWhere('t.day_time BETWEEN :start AND :end')
                ->setParameters([
                    'num_a' => $num_a,
                    'num_b' => $num_b,
                    'start' => $start,
                    'end' => $end,
                ])
                ->getQuery()
                ->getSingleScalarResult();
            $res = [];
            $res["status"] = 200;
            $res["data"] = $qr;
            return $res;
        }catch (Exception $exception){
            $res = [];
            return $res;
        }
    }


    // count communications between two numbers and dates

    public function getCommunicationDateBetween(string $num_a, string $num_b, $start, $end){
        try {
            $qr = $this->createQueryBuilder('t')
                ->select('t.num_a, t.num_b, t.day_time, t.flux_appel, t.data_type, t.duration')
                ->where('t.num_a = :num_a AND t.num_b = :num_b')
                ->andWhere('t.day_time BETWEEN :start AND :end')
                ->setParameters([
                    'num_a' => $num_a,
                    'num_b' => $num_b,
                    'start' => $start,
                    'end' => $end,
                ])
                ->getQuery()
                ->getResult();
            return $qr;
        }catch (Exception $exception){
            $res = [];
            return $res;
        }
    }

    public function getCommunications(string $num_a, string $num_b){
        try {
            $qr = $this->createQueryBuilder('t')
                ->select('t.num_a, t.num_b, t.day_time, t.flux_appel, t.data_type, t.duration')
                ->where('t.num_a = :num_a AND t.num_b = :num_b')
                ->setParameters([
                    'num_a' => $num_a,
                    'num_b' => $num_b
                ])
                ->getQuery()
                ->getResult();
            return $qr;
        }catch (Exception $exception){
            $res = [];
            return $res;
        }
    }



    public function getInCallsDateBetween(string $num_a, string $num_b, $start, $end){
        try {
            $qr = $this->createQueryBuilder('t')
                ->select('t.num_a, t.num_b, t.day_time, t.flux_appel, t.data_type, t.duration')
                ->where('t.num_a = :num_a AND t.num_b = :num_b')
                ->andWhere('t.flux_appel LIKE \'Entrant\'')
                ->andWhere('t.data_type LIKE \'Voix\'')
                ->andWhere('t.day_time BETWEEN :start AND :end')
                ->setParameters([
                    'num_a' => $num_a,
                    'num_b' => $num_b,
                    'start' => $start,
                    'end' => $end,
                ])
                ->getQuery()
                ->getResult();
            return $qr;
        }catch (Exception $exception){
            return [];
        }
    }


    public function getInCalls(string $num_a, string $num_b){
        try {
            $qr = $this->createQueryBuilder('t')
                ->select('t.num_a, t.num_b, t.day_time, t.flux_appel, t.data_type, t.duration')
                ->where('t.num_a = :num_a AND t.num_b = :num_b')
                ->andWhere('t.flux_appel LIKE \'Entrant\'')
                ->andWhere('t.data_type LIKE \'Voix\'')
                ->setParameters([
                    'num_a' => $num_a,
                    'num_b' => $num_b
                ])
                ->getQuery()
                ->getResult();
            return $qr;
        }catch (Exception $exception){
            return [];
        }
    }


    // count sms between two numbers and dates and criteria

    public function getsmsbetween(string $num_a, string $num_b, $start, $end){
        try {
            $qr = $this->createQueryBuilder('t')
                ->select('t.num_a, t.num_b, t.day_time, t.flux_appel, t.data_type, t.duration')
                ->where('t.num_a = :num_a AND t.num_b = :num_b')
                ->andWhere('t.day_time BETWEEN :start AND :end')
                ->andWhere('t.data_type LIKE \'SMS\'')
                ->setParameters([
                    'num_a' => $num_a,
                    'num_b' => $num_b,
                    'start' => $start,
                    'end' => $end,
                ])
                ->getQuery()
                ->getResult();
            $res = [];
            return $qr;
        }catch (Exception $exception){
            $res = [];
            return $res;
        }
    }

    public function getsms(string $num_a, string $num_b){
        try {
            $qr = $this->createQueryBuilder('t')
                ->select('t.num_a, t.num_b, t.day_time, t.flux_appel, t.data_type, t.duration')
                ->where('t.num_a = :num_a AND t.num_b = :num_b')
                ->andWhere('t.data_type LIKE \'SMS\'')
                ->setParameters([
                    'num_a' => $num_a,
                    'num_b' => $num_b
                ])
                ->getQuery()
                ->getResult();
            $res = [];
            return $qr;
        }catch (Exception $exception){
            $res = [];
            return $res;
        }
    }


    // count success calls between two numbers and dates and criteria


    public function getSuccessOutCallsbetween(string $num_a, string $num_b, $start, $end){
        $db = $this->getEntityManager();
        $str = 'SELECT trecord.num_a, trecord.num_b, trecord.day_time, trecord.flux_appel, trecord.data_type, trecord.duration
                FROM trecord
                INNER JOIN (
	                select trecord.num_b, trecord.num_a, trecord.day_time, trecord.flux_appel, trecord.data_type, trecord.duration
	                from trecord
	                where (num_b = ? and num_a = ?) and (data_type LIKE \'Voix\' and duration > 0 and flux_appel LIKE \'Entrant\') and day_time between ? and ?
                ) as qr
                ON trecord.day_time = qr.day_time and trecord.num_a = qr.num_b and trecord.num_b = qr.num_a
                where (trecord.num_a = ? and trecord.num_b = ?) and (trecord.data_type = \'Voix\' and trecord.duration > 0 and trecord.flux_appel LIKE \'Sortant\') and trecord.day_time between ? and ? ';


        $stmt = $db->getConnection()->prepare($str);
        $params = array(
            $num_a,
            $num_b,
            $start,
            $end,
            $num_a,
            $num_b,
            $start,
            $end,
        );
        $qr = $stmt->execute($params);
        $res = $stmt->fetchAll();
        return $res;
    }

    public function getSuccessOutCalls(string $num_a, string $num_b){
        $db = $this->getEntityManager();
        $str = 'SELECT trecord.num_a, trecord.num_b, trecord.day_time, trecord.flux_appel, trecord.data_type, trecord.duration
                FROM trecord
                INNER JOIN (
	                select trecord.num_b, trecord.num_a, trecord.day_time, trecord.flux_appel, trecord.data_type, trecord.duration
	                from trecord
	                where (num_b = ? and num_a = ?) and (data_type LIKE \'Voix\' and duration > 0 and flux_appel LIKE \'Entrant\') 
                ) as qr
                ON trecord.day_time = qr.day_time and trecord.num_a = qr.num_b and trecord.num_b = qr.num_a
                where (trecord.num_a = ? and trecord.num_b = ?) and (trecord.data_type = \'Voix\' and trecord.duration > 0 and trecord.flux_appel LIKE \'Sortant\') ';


        $stmt = $db->getConnection()->prepare($str);
        $params = array(
            $num_a,
            $num_b,
            $num_a,
            $num_b
        );
        $qr = $stmt->execute($params);
        $res = $stmt->fetchAll();
        return $res;
    }


    // count drop calls between two numbers and dates and criteria

    public function getDropOutCallsbetween(string $num_a, string $num_b, $start, $end){
        $db = $this->getEntityManager();
        $str = 'select num_a, num_b, day_time, data_type
                from trecord
                where (num_a = ? and num_b = ?) and (data_type LIKE \'Voix\') and (flux_appel LIKE \'Sortant\')  and day_time between ? and ?
                except
                select num_b, num_a, day_time, data_type
                from trecord
                where (num_b = ? and num_a = ?) and (data_type LIKE \'Voix\') and (flux_appel LIKE \'Entrant\') and day_time between ? and ?
                order by day_time asc ';


        $stmt = $db->getConnection()->prepare($str);
        $params = array(
            $num_a,
            $num_b,
            $start,
            $end,
            $num_a,
            $num_b,
            $start,
            $end,
        );
        $qr = $stmt->execute($params);
        $res = $stmt->fetchAll();
        return $res;
    }

    public function getDropOutCalls(string $num_a, string $num_b){
        $db = $this->getEntityManager();
        $str = 'select num_a, num_b, day_time, data_type
                from trecord
                where (num_a = ? and num_b = ?) and (data_type LIKE \'Voix\') and (flux_appel LIKE \'Sortant\') 
                except
                select num_b, num_a, day_time, data_type
                from trecord
                where (num_b = ? and num_a = ?) and (data_type LIKE \'Voix\') and (flux_appel LIKE \'Entrant\') 
                order by day_time asc ';


        $stmt = $db->getConnection()->prepare($str);
        $params = array(
            $num_a,
            $num_b,
            $num_a,
            $num_b,
        );
        $qr = $stmt->execute($params);
        $res = $stmt->fetchAll();
        return $res;
    }




    // get common contacts between two numbers

    public function getCommonContactsBetweenDates($num_a,$num_b,$start,$end){
        $str = '
            SELECT t.num_b, t.b_nom
            FROM trecord t
            inner JOIN (
	            select num_b, b_nom
	            from trecord
	            where (num_b NOT ILIKE \'401\' and num_b NOT ILIKE \'00403\' and num_b NOT ILIKE \'telma\' and num_b NOT ILIKE \'777\') and num_a = :num_a and day_time between :start and :end
            ) as qr
            ON t.num_b = qr.num_b 
            where (t.num_b NOT ILIKE \'401\' and t.num_b NOT ILIKE \'00403\' and t.num_b NOT ILIKE \'telma\' and t.num_b NOT ILIKE \'777\') and t.num_a = :num_b and t.day_time between :start and :end
            group by t.num_b, t.b_nom
        ';

        $db = $this->getEntityManager();

        $stmt = $db->getConnection()->prepare($str);
        $params = array(
            'num_a' => $num_a,
            'num_b' => $num_b,
            'start' => $start,
            'end' => $end,
        );
        $qr = $stmt->execute($params);
        $res = $stmt->fetchAll();
        return $res;

    }




    // get common contacts between two numbers

    public function getCommonContacts($num_a,$num_b){
        $str = '
            SELECT t.num_b, t.b_nom
            FROM trecord t
            inner JOIN (
	            select num_b, b_nom
	            from trecord
	            where (num_b NOT ILIKE \'401\' and num_b NOT ILIKE \'00403\' and num_b NOT ILIKE \'telma\' and num_b NOT ILIKE \'777\') and num_a = :num_a
            ) as qr
            ON t.num_b = qr.num_b 
            where (t.num_b NOT ILIKE \'401\' and t.num_b NOT ILIKE \'00403\' and t.num_b NOT ILIKE \'telma\' and t.num_b NOT ILIKE \'777\') and t.num_a = :num_b
            group by t.num_b, t.b_nom
        ';

        $db = $this->getEntityManager();

        $stmt = $db->getConnection()->prepare($str);
        $params = array(
            'num_a' => $num_a,
            'num_b' => $num_b
        );
        $qr = $stmt->execute($params);
        $res = $stmt->fetchAll();
        return $res;

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
