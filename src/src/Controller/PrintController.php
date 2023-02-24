<?php


namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class PrintController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{

    /**
     * @Route("/print/home", name="print_home")
     */

    public function exportHome(Request $request) {
        $json_data = $request->getContent();

        $data = json_decode($json_data,true);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->mergeCells('A1:H3');
        $sheet->setCellValue( 'A1','TABLEAU DE BORD');

        $spreadsheet
            ->getActiveSheet()
            ->getStyle('A1:H3')
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('0dcaf0');

        $sheet->getStyle('A1:H3')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:H3')->getFont()->setSize('18');


        $sheet->setCellValue( 'A5','Numero');
        $sheet->setCellValue( 'B5','Nom');
        $sheet->setCellValue( 'C5','Opérateur');
        $sheet->setCellValue( 'D5','Appels Entrants');
        $sheet->setCellValue( 'E5','Appels Sortants');
        $sheet->setCellValue( 'F5','SMS Entrants');
        $sheet->setCellValue( 'G5','SMS Sortants');
        $sheet->setCellValue( 'H5','Total Communications');

        $sheet->getStyle('A5:H5')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A5:H5')->getFont()->setBold(true);

        $initial_row = 6;

        foreach ($data as $row){
            $sheet->setCellValue( 'A'.$initial_row ,$row["c_number"]);
            if ($row["a_name"] == "0" || $row["a_name"] == ""){
                $sheet->setCellValue( 'B'.$initial_row ,strtoupper('non identife'));
                $sheet->getStyle('B'.$initial_row)->getFont()->getColor()->setARGB('d73038');

            }else{
                $sheet->setCellValue( 'B'.$initial_row ,strtoupper($row["a_name"]));
            }
            $sheet->setCellValue( 'C'.$initial_row , $row["c_operator"]);
            $sheet->setCellValue( 'D'.$initial_row , $row["b_call_count"]);
            $sheet->setCellValue( 'E'.$initial_row ,$row["a_call_count"]);
            $sheet->setCellValue( 'F'.$initial_row ,$row["b_sms_count"]);
            $sheet->setCellValue( 'G'.$initial_row ,$row["a_sms_count"]);
            $sheet->setCellValue( 'H'.$initial_row ,$row["com_count"]);
            $sheet->getStyle('A'.$initial_row.':H'.$initial_row)->getFont()->setBold(false);
            $initial_row++;
        }



        foreach (range('A','H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A4:H'.$initial_row)->getAlignment()->setHorizontal('center');

        $tp = '/exports/'.'Tableau de bord'.uniqid().'.xlsx';
        $this->checkDir();
        $file = $this->getParameter('kernel.project_dir').'/public'.$tp;
        $writer = new Xlsx($spreadsheet);
        $writer->save($file);





        return new JsonResponse([
            'link' => $tp
        ],200);
    }

    public function checkDir(){
        if (!file_exists($this->getParameter('kernel.project_dir').'/public/exports')) {
            mkdir($this->getParameter('kernel.project_dir').'/public/exports', 0777, true);
        }
    }




    /**
     * @Route("/print/user-com-details", name="print_user_details")
     */

    public function exportUserDetails(Request $request) {
        $json_data = $request->getContent();

        $data = json_decode($json_data,true);



        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->mergeCells('A1:H3');
        $sheet->setCellValue( 'A1','DETAIL DES COMMUNICATIONS DE '.strtoupper($data["c_person"]["c_name"]));

        $spreadsheet
            ->getActiveSheet()
            ->getStyle('A1:H3')
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('0dcaf0');

        $sheet->getStyle('A1:H3')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:H3')->getFont()->setSize('18');

        // show user informations


        $sheet->setCellValue( 'A5','Nom: ');
        $sheet->setCellValue( 'B5',$data["c_person"]["c_name"]);
        $sheet->setCellValue( 'A6','Numéro: ');
        $sheet->setCellValue( 'B6',$data["c_person"]["c_number"]);


        // set header for infos

        $spreadsheet->getActiveSheet()->mergeCells('A8:H10');
        $spreadsheet
            ->getActiveSheet()
            ->getStyle('A8:H10')
            ->getFont()->setSize('14');

        if ($data["frequent_users_range"]["range"] == "") {
            $sheet->setCellValue( 'A8','CONTACTS FREQUENTS ');
        }else{
            $s_d = new \DateTime($data["date_time_range_com"]["start"]);
            $e_d = new \DateTime($data["date_time_range_com"]["end"]);
            $start = $s_d->format('Y-m-d H:i:s');
            $end = $e_d->format('Y-m-d H:i:s');
            $sheet->setCellValue( 'A8','CONTACTS FREQUENTS ENTRE LE '.$start.' ET LE '.$end);
        }

        // favorites table header

        $sheet->setCellValue( 'A9','#');
        $sheet->setCellValue( 'B9','Numéro');
        $sheet->setCellValue( 'C9','Nom');
        $sheet->setCellValue( 'D9','Durée totale');
        $sheet->setCellValue( 'E9','Nombre de communications');


        $init_cell = 11;
        $i = 0;

        // favorites table content

        foreach ($data["favorites"] as $favorite) {
            $sheet->setCellValue( 'A'.$init_cell, $i);
            $sheet->setCellValue( 'B'.$init_cell, $favorite["num_b"]);
            $sheet->setCellValue( 'C'.$init_cell, $favorite["b_nom"]);
            $sheet->setCellValue( 'D'.$init_cell, $favorite["duration"]);
            $sheet->setCellValue( 'E'.$init_cell, $favorite["nb"]);
            $sheet->getStyle('A'.$init_cell.':H'.$init_cell)->getFont()->setBold(false);
            $i++;
            $init_cell++;

        }

        $init_cell ++;

        $merge = $init_cell+2;


        foreach (range('A','H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A4:H'.$init_cell)->getAlignment()->setHorizontal('center');



        $tp = '/exports/'.'DetailsComm'.$data["c_person"]["c_number"].uniqid().'.xlsx';

        $this->checkDir();
        $file = $this->getParameter('kernel.project_dir').'/public'.$tp;
        $writer = new Xlsx($spreadsheet);
        $writer->save($file);



        return new JsonResponse([
            'link' => $tp
        ],200);
    }




}