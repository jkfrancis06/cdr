<?php

namespace App\Controller;

use App\Form\XlsxConvertType;
use App\Service\FileUploader;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Yectep\PhpSpreadsheetBundle\Factory;
use function Symfony\Component\Translation\t;

class XlsController extends AbstractController
{
    /*private $spreadsheet;
    public function __construct(Factory $spreadsheetFactory) {
        $this->spreadsheet = $spreadsheetFactory;
    }*/


    /**
     * @Route("/convert", name="convert")
     */
    public function form(Request $request,FileUploader $fileUploader): Response
    {

        $form = $this->createForm(XlsxConvertType::class);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $files = $form['xlsxFiles']->getData();

            $id = uniqid();

            $dir = $this->getParameter('targetdirectory').'/convert/'.$id;
            $dirCsv = $this->getParameter('targetdirectory').'/convert/'.$id.'/csv';

            mkdir($dir, 0777, true);
            mkdir($dirCsv, 0777, true);


            $zip = new \ZipArchive();

            $zipName = $id.'.zip';

            $arr = [];

            foreach ($files as $file) {

                $fileName = $fileUploader->upload($file,$dir);

                $result = $this->convertCsv($dir.'/'.$fileName);

               $fp = fopen($dirCsv.'/'. pathinfo($fileName, PATHINFO_FILENAME).'.csv', 'w');

               foreach ($result as $fields) {
                   fputcsv($fp, $fields,";");
               }

               array_push($arr, $dirCsv.'/'. pathinfo($fileName, PATHINFO_FILENAME).'.csv');

            }

            $files = $arr;

            // Create new Zip Archive.
            $zip = new \ZipArchive();

            $zipName = 'Documents.zip';

            $zip->open($zipName,  \ZipArchive::CREATE);
            foreach ($files as $file) {
                $zip->addFromString(basename($file),  file_get_contents($file));
            }
            $zip->close();

            $response = new Response(file_get_contents($zipName));
            $response->headers->set('Content-Type', 'application/zip');
            $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipName . '"');
            $response->headers->set('Content-length', filesize($zipName));


            @unlink($zipName);

            (new Filesystem())->remove($dir);




            return $response;




        }


        return $this->render('home/convert.html.twig',[
            'form' => $form->createView()
        ]);

    }


    private function convertCsv($file){

        /* @var $readerXlsx Xlsx */
        $readerXlsx  = $this->spreadsheet->createReader('Xlsx');

        // Load reader
        try {
            $spreadsheet = $readerXlsx->load($file);
        }catch (Exception $exception) {
            throw new HttpException(500,$exception->getMessage());
        }

        $spreadsheetsCount = $spreadsheet->getSheetCount();

        // get name and number row

        $numberNameRow = $spreadsheet->getSheet(1)->getCell('B4')->getValue();

        if ($numberNameRow != null) {

            /** Extract number and name in string **/
            $arr = explode('/', $numberNameRow);

            $arr1 = explode(':', $arr[0]);

            $number = trim($arr1[1]);
            $name = trim($arr1[2]);
        }else{
            throw new \HttpException('Incorrect Format', 500);
        }

        /**Incoming Sheet**/

        $incomingTable = [];

        for ($i = 1; $i<= 2; $i++) {

            $incomingSheet =  $spreadsheet->getSheet($i-1);

            $highestRow = $incomingSheet->getHighestRow();

            $startRow = 11;

            $count = 11;



            $currentFlux = ($i == 1) ? 'Entrant' : 'Sortant' ;
            $type = "Voix";

            foreach ($incomingSheet->getRowIterator($startRow ,$highestRow) as $row){

                if ($count == 11 && $this->isRowEmpty($row) ) {
                    break;
                }

                if ($this->isRowEmpty($row)) {
                    continue;
                }else{


                    $numB = '';
                    $nomB = '';
                    $duree = '';
                    $date = '';



                    foreach ($row->getCellIterator('C') as $cell) {


                        $currentCoordinates = $cell->getCoordinate();
                        $first_character = mb_substr($currentCoordinates, 0, 1);

                        if ($cell->getValue() == null) {
                            if ($first_character != 'H'){
                                continue;
                            }

                        }

                        if ($first_character == 'J'){
                            break;
                        }

                        if ($first_character == 'C' && strpos( $cell->getValue(), 'SMS' ) !== false){
                            $type = "SMS";
                        }


                        /**NumB**/

                        if ($first_character == 'C'){
                            if (!is_float($cell->getValue())) {
                                continue 2;
                            }
                            $numB = $cell->getValue();
                        }



                        /**NomB**/
                        if ($first_character == 'D'){
                            if ($cell->getValue() == "Client appelé") {
                                continue 2;
                            }
                            $nomB = $cell->getValue();
                        }

                        /**Duree**/
                        if ($first_character == 'H'){
                            if (!is_int($cell->getValue())) {
                                continue 2;
                            }
                            $duree = intval($cell->getValue());
                        }

                        /**Date**/
                        if ($first_character == 'I'){
                            if (!is_float($cell->getValue())) {
                                continue 2;
                            }
                            $dateObject = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cell->getValue());
                            $date = $dateObject->format('d-m-Y H:i:s');
                        }
                    }

                    array_push($incomingTable,[
                        $number,
                        $numB,
                        $name,
                        $nomB,
                        $duree,
                        $date,
                        $currentFlux,
                        $type,
                    ]);




                }



                $count++;

            }

        }



        return $incomingTable;


    }


    /**
     * @Route("/xls", name="xls")
     * @throws HttpException
     */
    public function index(): Response
    {

        $file = $this->getParameter('targetdirectory').'/test1.xlsx';

        /* @var $readerXlsx Xlsx */
        $readerXlsx  = $this->spreadsheet->createReader('Xlsx');

        // Load reader
        try {
            $spreadsheet = $readerXlsx->load($file);
        }catch (Exception $exception) {
            throw new HttpException($exception->getMessage(), 500);
        }

        $spreadsheetsCount = $spreadsheet->getSheetCount();

        // get name and number row

        $numberNameRow = $spreadsheet->getSheet(1)->getCell('B4')->getValue();

        if ($numberNameRow != null) {

            /** Extract number and name in string **/
            $arr = explode('/', $numberNameRow);

            $arr1 = explode(':', $arr[0]);

            $number = trim($arr1[1]);
            $name = trim($arr1[2]);
        }else{
            throw new \HttpException('Incorrect Format', 500);
        }

        /**Incoming Sheet**/

        $incomingTable = [];

        for ($i = 1; $i<= 2; $i++) {

            $incomingSheet =  $spreadsheet->getSheet($i-1);

            $highestRow = $incomingSheet->getHighestRow();

            $startRow = 11;

            $count = 11;



            $currentFlux = ($i == 1) ? 'Entrant' : 'Sortant' ;
            $type = "Voix";

            foreach ($incomingSheet->getRowIterator($startRow ,$highestRow) as $row){

                if ($count == 11 && $this->isRowEmpty($row) ) {
                    break;
                }

                if ($this->isRowEmpty($row)) {
                    continue;
                }else{


                    $numB = '';
                    $nomB = '';
                    $duree = '';
                    $date = '';



                    foreach ($row->getCellIterator('C') as $cell) {


                        $currentCoordinates = $cell->getCoordinate();
                        $first_character = mb_substr($currentCoordinates, 0, 1);

                        if ($cell->getValue() == null) {
                            if ($first_character != 'H'){
                                continue;
                            }

                        }

                        if ($first_character == 'J'){
                            break;
                        }

                        if ($first_character == 'C' && strpos( $cell->getValue(), 'SMS' ) !== false){
                            $type = "SMS";
                        }


                        /**NumB**/

                        if ($first_character == 'C'){
                            if (!is_float($cell->getValue())) {
                                continue 2;
                            }
                            $numB = $cell->getValue();
                        }



                        /**NomB**/
                        if ($first_character == 'D'){
                            if ($cell->getValue() == "Client appelé") {
                                continue 2;
                            }
                            $nomB = $cell->getValue();
                        }

                        /**Duree**/
                        if ($first_character == 'H'){
                            if (!is_int($cell->getValue())) {
                                continue 2;
                            }
                            $duree = intval($cell->getValue());
                        }

                        /**Date**/
                        if ($first_character == 'I'){
                            if (!is_float($cell->getValue())) {
                                continue 2;
                            }
                            $dateObject = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cell->getValue());
                            $date = $dateObject->format('d-m-Y H:i:s');
                        }
                    }

                    array_push($incomingTable,[
                        $number,
                        $numB,
                        $name,
                        $nomB,
                        $duree,
                        $date,
                        $currentFlux,
                        $type,
                    ]);




                }



                $count++;

            }

        }



        $fp = fopen($this->getParameter('targetdirectory').'/result1.csv', 'w');

        foreach ($incomingTable as $fields) {
            fputcsv($fp, $fields,";");
        }




        return $this->json([
            'message' => '',
            'path' => '',
        ]);
    }





    private function isRowEmpty(Row $row) {
        $is_row_empty = true;
        foreach ($row->getCellIterator() as $cell) {
            if ($cell->getValue() !== null && $cell->getValue() !== '') {
                $is_row_empty = false;
                break;
            }
        }

        return $is_row_empty;
    }

}
