<?php


namespace App\Controller;


use App\Entity\CPerson;
use App\Entity\TRecord;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class DashboardController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function home(){

        $cperson_rep = $this->getDoctrine()->getManager()->getRepository(CPerson::class);
        $persons = $cperson_rep->findAll();

        $res_data_array = [];

        foreach ($persons as $person) {
            $person_data = [];

            // count communications
            $com_count = $this->getDoctrine()->getManager()->getRepository(TRecord::class)
                ->countAllCommuniactions($person->getCNumber());
            $person_data["com_count"] = $com_count["data"];

            // count sent calls
            $a_call_count = $this->getDoctrine()->getManager()->getRepository(TRecord::class)
                ->countAllSentReceivedCalls($person->getCNumber(),'Voix','Sortant');
            $person_data["a_call_count"] = $a_call_count["data"];

            // count received calls
            $b_call_count = $this->getDoctrine()->getManager()->getRepository(TRecord::class)
                ->countAllSentReceivedCalls($person->getCNumber(),'Voix','Entrant');
            $person_data["b_call_count"] = $b_call_count["data"];

            // count sent sms
            $a_sms_count = $this->getDoctrine()->getManager()->getRepository(TRecord::class)
                ->countAllSentReceivedCalls($person->getCNumber(),'SMS','Sortant');
            $person_data["a_sms_count"] = $a_sms_count["data"];

            // count received sms
            $b_sms_count = $this->getDoctrine()->getManager()->getRepository(TRecord::class)
                ->countAllSentReceivedCalls($person->getCNumber(),'SMS','Entrant');
            $person_data["b_sms_count"] = $b_sms_count["data"];

            $person_data["link"] = $person->getCFileName();
            $person_data["c_operator"] = $person->getCOperator();
            $person_data["c_number"] = $person->getCNumber();

            array_push($res_data_array,$person_data);


        }

        return $this->render('home/home.html.twig',[
            "response"=>$res_data_array
        ]);
    }

    /**
     * This route has a greedy pattern and is defined first.
     *
     * @Route("/number/{c_number}", name="c_number_page")
     */

    public function cNumberDetails(string $c_number){

        $repo = $this->getDoctrine()->getManager()->getRepository(CPerson::class);
        $db_c_number = $repo->findOneBy([
            'c_number' => $c_number
        ]);

        if ($db_c_number == null) {
            throw new NotFoundHttpException('Ce numero n\'existe pas');
        }
        return $this->render('home/number_details.html.twig',[
            'c_name' => "",
            'c_number' => $db_c_number->getCNumber(),
            'c_operator' => $db_c_number->getCOperator(),
            'c_file_name' => $db_c_number->getCFileName()
        ]);

    }


    /**
     * This route has a greedy pattern and is defined first.
     *
     * @Route("/number/commnunications/{c_number}", name="xhr_number_com")
     */

    public function getNumberCommunications(string $c_number) {
        $res = $this->getDoctrine()->getManager()->getRepository(TRecord::class)
                        ->getFavoritesNumber($c_number);
        $fav_numbers = $res["data"];
        $fav_numbers_array = [];
        foreach ($fav_numbers as $fav_number){
            $temp = [];
            $temp["num_b"] = $fav_number["num_b"];
            $temp["duration"] = gmdate("H:i:s",$fav_number["dur"]);
            $temp["nb"] = $fav_number["nb"];
            array_push($fav_numbers_array,$temp);
        };

        $repo = $this->getDoctrine()->getManager()->getRepository(TRecord::class);
        $communications = $repo->findBy([
            'num_a'=> $c_number
        ]);


        $serializer = new Serializer(
            array(
                new DateTimeNormalizer(array('datetime_format' => 'Y-m-d H:i:s')),
                new GetSetMethodNormalizer()
            ),
            array(
                'json' => new JsonEncoder()
            )
        );

        $response = [];
        $response["fav_numbers_array"] = $fav_numbers_array;
        $response["com_list"] = json_decode($serializer->serialize($communications,'json'),true);


        return new JsonResponse($response,200);
    }


    /**
     * This route has a greedy pattern and is defined first.
     *
     * @Route("/number/commnunications-details/date", name="xhr_number_com_date")
     */

    public function getNumberDateCommunications(Request $request) {
        $json_data = $request->getContent();
        $data = json_decode($json_data,true);
        $res = $this->getDoctrine()->getManager()->getRepository(TRecord::class)
            ->getFavoritesNumberDateRange($data["c_number"], $data["start"], $data["end"]);
        $fav_numbers = $res["data"];
        $fav_numbers_array = [];
        foreach ($fav_numbers as $fav_number){
            $temp = [];
            $temp["num_b"] = $fav_number["num_b"];
            $temp["duration"] = gmdate("H:i:s",$fav_number["dur"]);
            $temp["nb"] = $fav_number["nb"];
            array_push($fav_numbers_array,$temp);
        }
        return new JsonResponse($fav_numbers_array,200);
    }

}