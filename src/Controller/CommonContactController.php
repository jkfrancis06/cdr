<?php


namespace App\Controller;

use App\Entity\CPerson;
use App\Entity\TRecord;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class CommonContactController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{

    /**
     * @Route("/matrice/contact-communs/{range?}", name="matrice_contact_communs")
     *
     */

    public function contactCommuns($range = null){
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
                        ->getCommonContactsBetweenDates($cperson->getCNumber(),$cperson_comp->getCNumber(),$date_range[0],$date_range[1]);
                }else{
                    $t_record_rep = $this->getDoctrine()->getManager()->getRepository(TRecord::class)
                        ->getCommonContacts($cperson->getCNumber(),$cperson_comp->getCNumber());
                }
                $com_data[$cperson->getCNumber()."( ".$cperson->getANom()." )"][$cperson_comp->getCNumber()."( ".$cperson->getANom()." )"] = sizeof($t_record_rep);
            }
        }
        return $this->render('matrices/contacts_communs.html.twig',[
            "matrices" => $com_data,
            "range" => $date_range,
            "is_active" => "matrice_contact_communs"
        ]);
    }



    /**
     * @Route("/matrice/common-details/nb={numbers}&&start={start}&&end={end}", name="matrice_commun_details")
     *
     */

    public function communicationMatriceDetailsController(PaginatorInterface $paginator,$numbers,$start,$end,Request $request)
    {

        if ($numbers != null) {
            $date_name_range = explode('&', $numbers, 2);

            $part1 = explode('(', $date_name_range[0], 2);
            $part2 = explode('(', $date_name_range[1], 2);
            $num_a = $part1[0];
            $num_b = $part2[0];
        }

        if (\DateTime::createFromFormat('Y-m-d H:i:s', $start) == false || \DateTime::createFromFormat('Y-m-d H:i:s', $end) == false) {
            $commons = $this->getDoctrine()->getRepository(TRecord::class)
                ->getCommonContacts($num_a,$num_b,$start,$end);
        }else{
            $commons = $this->getDoctrine()->getRepository(TRecord::class)
                ->getCommonContactsBetweenDates($num_a,$num_b,$start,$end);
        }


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
        $data = [];
        foreach ($commons as $common){
            $temp = [];
            $temp["b_nom"] = $common["b_nom"];
            $temp["has_cdr"] = false;
            $temp["num_b"] =  $common["num_b"];
            $cperson_repo = $this->getDoctrine()->getManager()->getRepository(CPerson::class);
            $cperson = $cperson_repo->findOneBy([
                'c_number' => $common["num_b"]
            ]);
            if ($cperson != null){
                $temp["has_cdr"] = true;
            }
            array_push($data,$temp);
        }

        $tp = array_column($data, 'num_b');
        array_multisort($tp, SORT_ASC, $data);

        $common_page = $paginator->paginate(
            $data, /* query NOT result */
            $request->query->getInt('common_details_page', 1)/*page number*/,
            10/*limit per page*/,
            ['pageParameterName' => 'common_details_page']
        );

        return $this->render('matrices/details_contact_communs.html.twig',[
            "data" => [
                "commons" => $common_page,
                'b_person' => $b_person_serialized,
                'a_person' => $a_person_serialized,
                'dates' => ["start" =>$start, "end" => $end]
            ]
        ]);

    }

}