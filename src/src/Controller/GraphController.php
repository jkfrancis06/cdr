<?php


namespace App\Controller;

use App\Entity\CPerson;
use App\Entity\TRecord;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;



class GraphController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{


    /**
     * @Route("/shema-commun", name="schema-commun")
     */

    public function schemaCommun(){

        return $this->render('schema/commun.html.twig', [
            'is_active' => 'schema-commun'
        ]);
    }

    /**
     * @Route("/communication-graph/{range?}", name="communication_graph")
     */

    public function communicationGraph($range = null, Request $request){

        $date_range = [];
        if ($range != null){
            $date_range = explode('&',$range,2);
        }

        $p_repo = $this->getDoctrine()->getManager()->getRepository(CPerson::class);
        $t_repo = $this->getDoctrine()->getManager()->getRepository(TRecord::class);
        $c_persons = $p_repo->findAll();

        $nodes_array = [];
        $edges_array = [];
        foreach ($c_persons as $c_person){
            $tp = [];
            if ($c_person->getANom() == "" || $c_person->getANom() == "0"){
                $tp["a_nom"] = strtoupper("NON ID (".$c_person->getCNumber() ." )");
            }else{
                $tp["a_nom"] = $c_person->getANom();
            }
            $tp["c_number"] = $c_person->getCNumber();
            $tp["c_pic_name"] = $c_person->getCPicName();
            array_push($nodes_array,$tp);
            foreach ($c_persons as $row){
                $temp = [];
                if(sizeof($date_range ) > 0){

                    $record = $this->getDoctrine()->getManager()->getRepository(TRecord::class)
                                ->getCommunicationDateBetween($c_person->getCNumber(),$row->getCNumber(), $date_range[0], $date_range[1]);

                }else{
                    $record = $t_repo->findOneBy([
                        'num_a' => $c_person->getCNumber(),
                        'num_b' => $row->getCNumber()
                    ]);
                }
                if ($record != null || sizeof($record) > 0 ){
                    $temp["source"] = $c_person->getCNumber();
                    $temp["dest"] = $row->getCNumber();
                    array_push($edges_array,$temp);
                }
            }

        }

        return new JsonResponse([
            'nodes' => $nodes_array,
            'edges' => $edges_array
        ]);

    }

    /**
     * @Route("/draw", name="draw")
     */

    public function drawGraph(){
        return  $this->render('schema/draw.html.twig');
    }


}