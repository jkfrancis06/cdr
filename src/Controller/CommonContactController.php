<?php


namespace App\Controller;

use App\Entity\CPerson;
use App\Entity\TRecord;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
        $link = $this->exportMatriceCommonContact($com_data,$date_range);
        return $this->render('matrices/contacts_communs.html.twig',[
            "link" => $link,
            "matrices" => $com_data,
            "range" => $date_range,
            "is_active" => "matrice_contact_communs"
        ]);
    }



    public function exportMatriceCommonContact($matrice, $range = null) {



        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->mergeCells('A1:H3');
        $sheet->setCellValue( 'A1','MATRICE DE CONTACTS EN COMMUNS');

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

        $tp = '/exports/'.'Matrice_de_contact'.uniqid().'.xlsx';

        $file = $this->getParameter('kernel.project_dir').'/public'.$tp;
        $this->checkDir();
        $writer = new Xlsx($spreadsheet);
        $writer->save($file);


        return $tp;
    }

    public function checkDir(){
        if (!file_exists($this->getParameter('kernel.project_dir').'/public/exports')) {
            mkdir($this->getParameter('kernel.project_dir').'/public/exports', 0777, true);
        }
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