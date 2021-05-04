<?php


namespace App\Controller;


use App\Entity\CPerson;
use App\Entity\DumpT;
use App\Entity\TRecord;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        $link ="";

        $link = $this->exportMatriceCom($com_data,$date_range);

        return $this->render('matrices/matrice_communication.html.twig',[
            "matrices" => $com_data,
            "range" => $date_range,
            "is_active" => "matrice_communication",
            "link" => $link
        ]);
    }



    public function exportMatriceCom($matrice, $range = null) {


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->mergeCells('A1:H3');
        $sheet->setCellValue( 'A1','MATRICE DE COMMUNICATION');

        $spreadsheet
            ->getActiveSheet()
            ->getStyle('A1:H3')
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('0dcaf0');

        $sheet->getStyle('A1:H3')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:H3')->getFont()->setSize('18');


        $sheet->setCellValue( 'A5','Filtre de date: ');
        if ($range != null) {
            $sheet->setCellValue( 'B5',$range[0].' AU '. $range[1]);
        }

        $initial_row = 7;
        $init_letter = 'B';
        $i = 0;
        foreach ($matrice as $key => $rows){
            $sheet->setCellValue( $init_letter.''.$initial_row,$key);
            $sheet->getStyle($init_letter.''.$initial_row)->getFont()->setBold(true);
            $init_letter++;
        }

        $initial_row++;
        $l = 'A';

        foreach ($matrice as $key => $rows){
            $init_letter = 'A';
            $sheet->setCellValue( $init_letter.''.$initial_row,$key);
            $sheet->getStyle($init_letter.''.$initial_row)->getFont()->setBold(true);
            foreach ($rows as $key1 => $row){
                $init_letter++;
                $l++;
                if ($key == $key1){
                    $sheet->setCellValue( $init_letter.''.$initial_row,'N/A');
                }else{
                    $sheet->setCellValue( $init_letter.''.$initial_row,$row);
                }
                $sheet->getStyle($init_letter.''.$initial_row)->getFont()->setBold(false);
            }
            $initial_row++;
        }

        foreach (range('A',$l) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A5:Z'.$initial_row)->getAlignment()->setHorizontal('center');

        $tp = '/exports/'.'Matrice_de_commmunication'.uniqid().'.xlsx';

        $file = $this->getParameter('kernel.project_dir').'/public'.$tp;
        $writer = new Xlsx($spreadsheet);
        $writer->save($file);


        return $tp;
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

            $data = [
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
            ];

            $link ="";

            $link = $this->exportMatriceDetails($data);

            return $this->render('matrices/details_matrice_communication.html.twig',[
                'link' => $link,
                'page_com_all' => $com_page,
                'page_in_com' => $in_com_page,
                'page_com_success' => $com_success_page,
                'page_drop_com' => $drop_com_page,
                'sms_page' => $sms_page,
                'data' => $data
            ]);

        }else{
            throw new NotFoundHttpException('Not found');
        }


    }



    public function exportMatriceDetails($data) {


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->mergeCells('A1:H3');
        $sheet->setCellValue( 'A1','DETAILS DES COMMUNICATIONS ENTRE '.
            $data["a_person"]["cNumber"].'( '. $data["a_person"]["aNom"].' ) ET '.   $data["b_person"]["cNumber"].'( '. $data["b_person"]["aNom"].' )'
            );



        $spreadsheet
            ->getActiveSheet()
            ->getStyle('A1:H3')
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('0dcaf0');

        $sheet->getStyle('A1:H3')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:H3')->getFont()->setSize('18');


        $sheet->setCellValue( 'A5','Filtre de date ');
        $sheet->setCellValue( 'B5',$data["dates"]["start"].' ET '.$data["dates"]["end"]);


        // start block count

        $sheet->setCellValue( 'A7','Appels Entrants');
        $sheet->setCellValue( 'B7','Appels Sortants');
        $sheet->setCellValue( 'C7','Repondeur (Echec)');
        $sheet->setCellValue( 'D7','SMS');
        $sheet->setCellValue( 'E7','Total des communications');

        $sheet->getStyle('A7:H7')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A7:H7')->getFont()->setBold(true);

        $sheet->setCellValue( 'A8',$data["in_com_count"]);
        $sheet->setCellValue( 'B8',$data["success_com_count"]);
        $sheet->setCellValue( 'C8',$data["drop_com_count"]);
        $sheet->setCellValue( 'D8',$data["sms_count"]);
        $sheet->setCellValue( 'E8',$data["all_com_count"]);

        $sheet->getStyle('A8:H8')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A8:H8')->getFont()->setBold(false);


        // end block

        $initial_row = 9;
        $t = $initial_row + 2;


        // start block communications list

        $sheet = $spreadsheet->getActiveSheet()->mergeCells('A'. $initial_row.':H'. $t);
        $sheet->setCellValue( 'A'. $initial_row,'LISTE DES COMMUNICATIONS');

        $sheet->getStyle('A'. $initial_row.':H'. $t)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A'. $initial_row.':H'. $t)->getFont()->setSize('14');

        $initial_row = $t+1;

        $sheet->setCellValue( 'A'. $initial_row,'Flux');
        $sheet->setCellValue( 'B'. $initial_row,'Type');
        $sheet->setCellValue( 'C'. $initial_row,'Numero A');
        $sheet->setCellValue( 'D'. $initial_row,'Numero B');
        $sheet->setCellValue( 'E'. $initial_row,'Durée');
        $sheet->setCellValue( 'F'. $initial_row,'Date');

        $sheet->getStyle('A'. $initial_row.':H'. $initial_row)->getFont()->setBold(true);

        $initial_row ++;

        foreach ($data["all_com"] as $com) {
            $sheet->setCellValue( 'A'.$initial_row, $com["flux_appel"]);
            $sheet->setCellValue( 'B'.$initial_row, $com["data_type"]);
            $sheet->setCellValue( 'C'.$initial_row, $com["num_a"]);
            $sheet->setCellValue( 'D'.$initial_row, $com["num_b"]);
            $sheet->setCellValue( 'E'.$initial_row, gmdate('H:i:s',$com["duration"]));
            $sheet->setCellValue( 'F'.$initial_row, $com["day_time"]->format('Y-m-d H:i:s'));
            $initial_row ++;
        }

        // end block


        $initial_row ++;

        // start block appels sortants

        $t = $initial_row + 2;


        $sheet = $spreadsheet->getActiveSheet()->mergeCells('A'.$initial_row.':H'.$t);
        $sheet->setCellValue( 'A'.$initial_row ,'APPELS SORTANTS');

        $sheet->getStyle('A'.$initial_row.':H'.$t)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A'.$initial_row.':H'.$t)->getFont()->setSize('14');

        $initial_row = $t+1;

        $sheet->setCellValue( 'A'.$initial_row,'Flux');
        $sheet->setCellValue( 'B'.$initial_row,'Type');
        $sheet->setCellValue( 'C'.$initial_row,'Numero A');
        $sheet->setCellValue( 'D'.$initial_row,'Numero B');
        $sheet->setCellValue( 'E'.$initial_row,'Durée');
        $sheet->setCellValue( 'F'.$initial_row,'Date');

        $sheet->getStyle('A'.$initial_row.':H'.$initial_row)->getFont()->setBold(true);

        $initial_row ++;

        foreach ($data["all_com"] as $com) {
            $sheet->setCellValue( 'A'.$initial_row, $com["flux_appel"]);
            $sheet->setCellValue( 'B'.$initial_row, $com["data_type"]);
            $sheet->setCellValue( 'C'.$initial_row, $com["num_a"]);
            $sheet->setCellValue( 'D'.$initial_row, $com["num_b"]);
            $sheet->setCellValue( 'E'.$initial_row, gmdate('H:i:s',$com["duration"]));
            $sheet->setCellValue( 'F'.$initial_row, $com["day_time"]->format('Y-m-d H:i:s'));
            $initial_row ++;
        }

        //end block

        $initial_row++;


        // start block appels entrants


        $t = $initial_row + 2;


        $sheet = $spreadsheet->getActiveSheet()->mergeCells('A'.$initial_row.':H'.$t);
        $sheet->setCellValue( 'A'.$initial_row ,'APPELS ENTRANTS');

        $sheet->getStyle('A'.$initial_row.':H'.$t)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A'.$initial_row.':H'.$t)->getFont()->setSize('14');

        $initial_row = $t+1;

        $sheet->setCellValue( 'A'.$initial_row,'Flux');
        $sheet->setCellValue( 'B'.$initial_row,'Type');
        $sheet->setCellValue( 'C'.$initial_row,'Numero A');
        $sheet->setCellValue( 'D'.$initial_row,'Numero B');
        $sheet->setCellValue( 'E'.$initial_row,'Durée');
        $sheet->setCellValue( 'F'.$initial_row,'Date');

        $sheet->getStyle('A'.$initial_row.':H'.$initial_row)->getFont()->setBold(true);

        $initial_row ++;

        foreach ($data["in_com"] as $com) {
            $sheet->setCellValue( 'A'.$initial_row, $com["flux_appel"]);
            $sheet->setCellValue( 'B'.$initial_row, $com["data_type"]);
            $sheet->setCellValue( 'C'.$initial_row, $com["num_a"]);
            $sheet->setCellValue( 'D'.$initial_row, $com["num_b"]);
            $sheet->setCellValue( 'E'.$initial_row, gmdate('H:i:s',$com["duration"]));
            $sheet->setCellValue( 'F'.$initial_row, $com["day_time"]->format('Y-m-d H:i:s'));
            $initial_row ++;
        }

        $initial_row++;
        // endblock

        // start block echec

        $t = $initial_row + 2;


        $sheet = $spreadsheet->getActiveSheet()->mergeCells('A'.$initial_row.':H'.$t);
        $sheet->setCellValue( 'A'.$initial_row ,'APPELS ECHOUES (REPONDEUR)');

        $sheet->getStyle('A'.$initial_row.':H'.$t)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A'.$initial_row.':H'.$t)->getFont()->setSize('14');

        $initial_row = $t+1;

        $sheet->setCellValue( 'A'.$initial_row,'Type');
        $sheet->setCellValue( 'B'.$initial_row,'Numero A');
        $sheet->setCellValue( 'C'.$initial_row,'Numero B');
        $sheet->setCellValue( 'D'.$initial_row,'Date');

        $sheet->getStyle('A'.$initial_row.':H'.$initial_row)->getFont()->setBold(true);

        $initial_row ++;

        foreach ($data["in_com"] as $com) {
            $sheet->setCellValue( 'A'.$initial_row, $com["data_type"]);
            $sheet->setCellValue( 'B'.$initial_row, $com["num_a"]);
            $sheet->setCellValue( 'C'.$initial_row, $com["num_b"]);
            $sheet->setCellValue( 'D'.$initial_row, $com["day_time"]->format('Y-m-d H:i:s'));
            $initial_row ++;
        }

        $initial_row++;

        // block sms


        $t = $initial_row + 2;


        $sheet = $spreadsheet->getActiveSheet()->mergeCells('A'.$initial_row.':H'.$t);
        $sheet->setCellValue( 'A'.$initial_row ,'SMS');

        $sheet->getStyle('A'.$initial_row.':H'.$t)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A'.$initial_row.':H'.$t)->getFont()->setSize('14');

        $initial_row = $t+1;

        $sheet->setCellValue( 'A'.$initial_row,'Flux');
        $sheet->setCellValue( 'B'.$initial_row,'Type');
        $sheet->setCellValue( 'C'.$initial_row,'Numero A');
        $sheet->setCellValue( 'D'.$initial_row,'Numero B');
        $sheet->setCellValue( 'E'.$initial_row,'Durée');
        $sheet->setCellValue( 'F'.$initial_row,'Date');

        $sheet->getStyle('A'.$initial_row.':H'.$initial_row)->getFont()->setBold(true);

        $initial_row ++;

        foreach ($data["sms"] as $com) {
            $sheet->setCellValue( 'A'.$initial_row, $com["flux_appel"]);
            $sheet->setCellValue( 'B'.$initial_row, $com["data_type"]);
            $sheet->setCellValue( 'C'.$initial_row, $com["num_a"]);
            $sheet->setCellValue( 'D'.$initial_row, $com["num_b"]);
            $sheet->setCellValue( 'E'.$initial_row, gmdate('H:i:s',$com["duration"]));
            $sheet->setCellValue( 'F'.$initial_row, $com["day_time"]->format('Y-m-d H:i:s'));
            $initial_row ++;
        }

        $initial_row++;


        foreach (range('A','H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A5:Z'.$initial_row)->getAlignment()->setHorizontal('center');

        $tp = '/exports/'.'communications_details'.uniqid().'.xlsx';

        $file = $this->getParameter('kernel.project_dir').'/public'.$tp;
        $writer = new Xlsx($spreadsheet);
        $writer->save($file);


        return $tp;
    }

}