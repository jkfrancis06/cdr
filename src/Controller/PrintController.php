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
        $sheet = $spreadsheet->getActiveSheet()->mergeCells('A1:M3');
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


        $tp = '/exports/'.'Tableau de bord'.uniqid().'.xlsx';

        $file = $this->getParameter('kernel.project_dir').'/public'.$tp;
        $writer = new Xlsx($spreadsheet);
        $writer->save($file);



        return new JsonResponse([
            'link' => $tp
        ],200);
    }




}