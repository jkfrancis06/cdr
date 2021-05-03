<?php


namespace App\Controller;


use App\Entity\CPerson;
use App\Entity\DumpT;
use App\Entity\TRecord;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class MatriceController extends AbstractController
{

    /**
     * @Route("/matrice/communication/{range?}", name="matrice_communication")
     *
     */
    public function communicationMatriceController($range = null){
        $date_range = [];
        if ($range != null){
            $date_range = explode('&',$range,2);
        }
        $cperson_repo = $this->getDoctrine()->getManager()->getRepository(CPerson::class);
        $cpersons = $cperson_repo->findAll();
        $com_data = [];
        foreach ($cpersons as $cperson) {
            $com_data[$cperson->getCNumber()."( ".$cperson->getANom()." )"] = [];
            foreach ($cpersons as $cperson_comp){
                if ($range != null){
                    $t_record_rep = $this->getDoctrine()->getManager()->getRepository(TRecord::class)
                        ->countCommunicationDateBetween($cperson->getCNumber(),$cperson_comp->getCNumber(),$date_range[0],$date_range[1]);
                }else{
                    $t_record_rep = $this->getDoctrine()->getManager()->getRepository(TRecord::class)
                        ->countCommunicationBetween($cperson->getCNumber(),$cperson_comp->getCNumber());
                }
                $com_data[$cperson->getCNumber()."( ".$cperson->getANom()." )"][$cperson_comp->getCNumber()."( ".$cperson->getANom()." )"] = $t_record_rep["data"];
            }
        }
        return $this->render('matrices/matrice_communication.html.twig',[
            "matrices" => $com_data,
            "range" => $date_range,
            "is_active" => "matrice_communication"
        ]);
    }


    /**
     * @Route("/matrice/communication-details/nb={numbers}&&start={start}&&end={end}", name="matrice_communication_details")
     *
     */
    public function communicationMatriceDetailsController(PaginatorInterface $paginator,$numbers,$start,$end,Request $request){

        if ($numbers != null){
            $date_name_range = explode('&',$numbers,2);

            $part1 = explode('(',$date_name_range[0],2);
            $part2 = explode('(',$date_name_range[1],2);
            $num_a = $part1[0];
            $num_b = $part2[0];

            if (\DateTime::createFromFormat('Y-m-d H:i:s', $start) == false || \DateTime::createFromFormat('Y-m-d H:i:s', $end) == false) {


                $cp_repo = $this->getDoctrine()->getRepository(CPerson::class);
                $a_person = $cp_repo->findOneBy([
                    'c_number' => $num_a
                ]);
                $b_person = $cp_repo->findOneBy([
                    'c_number' => $num_b
                ]);

                $serializer = new Serializer(array(new ObjectNormalizer()));
                $a_person_serialized = $serializer->normalize($a_person, null);
                $b_person_serialized = $serializer->normalize($b_person, null);

                // compter toutes les communications

                $com = $this->getDoctrine()->getRepository(TRecord::class)
                    ->getCommunications($num_a,$num_b,$start,$end);

                // succeed out calls

                $com_success = $this->getDoctrine()->getRepository(TRecord::class)
                    ->getSuccessOutCalls($num_a,$num_b);

                // drop calls

                $com_drop = $this->getDoctrine()->getRepository(TRecord::class)
                    ->getDropOutCalls($num_a,$num_b);

                // sms

                $sms = $this->getDoctrine()->getRepository(TRecord::class)
                    ->getsms($num_a,$num_b);

                // get incoming calls
                $rep = $this->getDoctrine()->getRepository(TRecord::class);

                $in_com = $this->getDoctrine()->getRepository(TRecord::class)
                            ->getInCalls($num_a,$num_b);

            }else{


                $cp_repo = $this->getDoctrine()->getRepository(CPerson::class);
                $a_person = $cp_repo->findOneBy([
                    'c_number' => $num_a
                ]);
                $b_person = $cp_repo->findOneBy([
                    'c_number' => $num_b
                ]);

                $serializer = new Serializer(array(new ObjectNormalizer()));
                $a_person_serialized = $serializer->normalize($a_person, null);
                $b_person_serialized = $serializer->normalize($b_person, null);

                // compter toutes les communications

                $com = $this->getDoctrine()->getRepository(TRecord::class)
                    ->getCommunicationDateBetween($num_a,$num_b,$start,$end);

                // succeed out calls

                $com_success = $this->getDoctrine()->getRepository(TRecord::class)
                    ->getSuccessOutCallsbetween($num_a,$num_b,$start,$end);

                // drop calls

                $com_drop = $this->getDoctrine()->getRepository(TRecord::class)
                    ->getDropOutCallsbetween($num_a,$num_b,$start,$end);

                // sms

                $sms = $this->getDoctrine()->getRepository(TRecord::class)
                    ->getsmsbetween($num_a,$num_b,$start,$end);

                // get incoming calls
                $rep = $this->getDoctrine()->getRepository(TRecord::class);

                $in_com = $this->getDoctrine()->getRepository(TRecord::class)
                    ->getInCallsDateBetween($num_a,$num_b,$start,$end);
            }




            $com_page = $paginator->paginate(
                $com, /* query NOT result */
                $request->query->getInt('page_com_all', 1)/*page number*/,
                10/*limit per page*/,
                ['pageParameterName' => 'page_com_all']
            );

            $com_success_page = $paginator->paginate(
                $com_success, /* query NOT result */
                $request->query->getInt('page_com_success', 1)/*page number*/,
                10/*limit per page*/,
                ['pageParameterName' => 'page_com_success']
            );

            $in_com_page = $paginator->paginate(
                $in_com, /* query NOT result */
                $request->query->getInt('page_in_com', 1)/*page number*/,
                10/*limit per page*/,
                ['pageParameterName' => 'page_in_com']
            );

            $drop_com_page = $paginator->paginate(
                $com_drop, /* query NOT result */
                $request->query->getInt('page_drop_com', 1)/*page number*/,
                10/*limit per page*/,
                ['pageParameterName' => 'page_drop_com']
            );

            $sms_page = $paginator->paginate(
                $sms, /* query NOT result */
                $request->query->getInt('sms_page', 1)/*page number*/,
                10/*limit per page*/,
                ['pageParameterName' => 'sms_page']
            );

            return $this->render('matrices/details_matrice_communication.html.twig',[
                'page_com_all' => $com_page,
                'page_in_com' => $in_com_page,
                'page_com_success' => $com_success_page,
                'page_drop_com' => $drop_com_page,
                'sms_page' => $sms_page,
                'data' => [
                    'b_person' => $b_person_serialized,
                    'a_person' => $a_person_serialized,
                    'all_com_count' => sizeof($com),
                    'all_com' => $com_page,
                    'success_com_count' => sizeof($com_success),
                    'success_com' => $com_success,
                    'drop_com_count' => sizeof($com_drop),
                    'drop_com' => $com_drop,
                    'sms_count' => sizeof($sms),
                    'sms' => $sms,
                    'in_com_count' => sizeof($in_com),
                    'in_com' => $in_com,
                    'dates' => ["start" =>$start, "end" => $end]
                ]
            ]);

        }else{
            throw new NotFoundHttpException('Not found');
        }


    }

}