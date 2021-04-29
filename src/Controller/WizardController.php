<?php


namespace App\Controller;
use App\Entity\CPerson;
use App\Entity\DumpT;
use App\Entity\TRecord;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class WizardController extends AbstractController
{

    /**
     * @Route("/wizard", name="home_wizard")
     */
    public function homeWizard(){

        // truncate tables

        $data = $this->getDoctrine()
            ->getRepository(DumpT::class)
            ->truncateTable();

        return $this->render('home/wizard.html.twig',$data);
    }






    /**
     * @Route("/wizard/cdr-upload", name="upload-cdr")
     */
    public function uploadCdr(Request $request, FileUploader $fileUploader)
    {

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        if ($uploadedFile) {
            $uploadedFileName = $fileUploader->upload($uploadedFile);

            // Copy file to DB

            $res = $this->getDoctrine()
                ->getRepository(DumpT::class)
                ->dumpCSV($this->getParameter('targetdirectory').'/'.$uploadedFileName);

            $data = [];

            if ($res["error"] == 1){
                $data["status"] = 500;
                $data["message"] = "Erreur dans le format du fichier";
                return new JsonResponse(
                    $data,500
                );
            }else{
                $data["status"] = 200;
                $data["nb_rows"] = $res["nb_rows"];
                $data["mem_usage"] = $res["mem_usage"];
                $data["path"] = $this->getParameter('targetdirectory').'/'.$uploadedFileName;
                $data["file_name"] = $uploadedFileName;
                return new JsonResponse(
                    $data,200
                );
            }

        }else{
            $data = [];
            $data["status"] = 500;
            $data["message"] = "Erreur d'upload";
            return new JsonResponse(
                $data,500
            );
        }

        // ...
    }

    /**
     * @Route("/wizard/validate", name="wizard_validate")
     */

    public function postWizardData(Request $request){
        $json_data = $request->getContent();
        $data = json_decode($json_data,true);


        foreach ($data as $rows) {
            $c_person = new CPerson();
            $c_person->setCNumber($rows["p_number"]);
            $c_person->setCOperator($rows["operator"]);
            $c_person->setCFileName($rows["success_data"]["file_name"]);
            $em = $this->getDoctrine()->getManager();
            $em->persist($c_person);
            $em->flush();
        }

        $this->getDoctrine()
            ->getRepository(TRecord::class)
            ->dumpAll();

        $home_route = $request->attributes->get('home');

        $data = [];
        $data["status"] = 200;
        $data["message"] = "";
        $data["route"] = $home_route;

        return new JsonResponse($data,200);

    }


}