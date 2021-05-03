<?php


namespace App\Controller;
use App\Entity\CPerson;
use App\Entity\DumpHuriEntrant;
use App\Entity\DumpHuriSortant;
use App\Entity\DumpT;
use App\Entity\TRecord;
use App\Form\CPersonType;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class WizardController extends AbstractController
{

    /**
     * @Route("/wizard/{c_number?}", name="home_wizard")
     */
    public function homeWizard(Request  $request, $c_number = null,FileUploader $fileUploader){

        $is_new = true;
        $repo = $this->getDoctrine()->getManager()->getRepository(CPerson::class);
        if($c_number != null) {
            $c_person = $repo->findOneBy([
               'c_number' => $c_number
            ]);
            $is_new = false;
        }else{
            $c_person = new CPerson();
        }
        $c_persons = [];
        $repo = $this->getDoctrine()->getManager()->getRepository(CPerson::class);

        $a_person_serialized = [];

        if ($repo->findAll() != null){
            $c_persons = $repo->findAll();
            $serializer = new Serializer(array(new ObjectNormalizer()));
            $a_person_serialized = $serializer->normalize($c_persons, null);
        }


        $form = $this->createForm(CPersonType::class, $c_person);

        $em = $this->getDoctrine()->getManager();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $cdrFile = $form->get('c_file_name')->getData();

            $form_c_number = $form->get('c_number')->getData();
            $firstCharacter = substr($form_c_number, 0, 1);
            switch($firstCharacter) {
                case "3":
                    $c_person->setCOperator("HURI");
                    break;
                case "4":
                    $c_person->setCOperator("TELMA");
                    break;
                case "7":
                    $c_person->setCOperator("HURI");
                    break;
            }

            if($cdrFile != null){
                $uploadedFileName = $fileUploader->upload($cdrFile);
                $c_person->setCFileName($uploadedFileName);
            }



            if ($is_new == false) {
                $em->flush();
            }else{
                $em->persist($c_person);
                $em->flush();
            }

            $request->getSession()->getFlashBag()->add('add_c_person', 'Le sujet a été ajouté avec succès');

            $repo = $this->getDoctrine()->getManager()->getRepository(CPerson::class);

            if ($repo->findAll() != null){
                $c_persons = $repo->findAll();
                $serializer = new Serializer(array(new ObjectNormalizer()));
                $a_person_serialized = $serializer->normalize($c_persons, null);
            }


        }

        return $this->render('home/wizard.html.twig',[
            'form' => $form->createView(),
            'c_persons' => $a_person_serialized
        ]);
    }

    /**
     * @Route("/wizard-remove/{c_number}", name="remove_number_wizard")
     */
    public function removeNumber($c_number, Request $request){
        $repo = $this->getDoctrine()->getManager()->getRepository(CPerson::class);
        $c_person = $repo->findOneBy([
            'c_number' => $c_number
        ]);
        try {
            unlink($this->getParameter('targetdirectory').'/'.$c_person->getCFileName());
        }catch (\Exception $exception){

        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($c_person);
        $em->flush();

        $request->getSession()->getFlashBag()->add('remove_c_person', 'Le sujet a été supprimé avec succès');

        return $this->redirectToRoute('home_wizard');



    }


    /**
     * @Route("/wizard-validate/dump-data", name="dump_data")
     */

    public function dumpWizardData(Request $request){

        // Truncate tables

        $this->getDoctrine()
            ->getRepository(DumpT::class)
            ->truncateTable(DumpT::class);

        $this->getDoctrine()
            ->getRepository(DumpT::class)
            ->truncateTable(TRecord::class);

        $this->getDoctrine()
            ->getRepository(DumpT::class)
            ->truncateTable(DumpHuriEntrant::class);

        $this->getDoctrine()
            ->getRepository(DumpT::class)
            ->truncateTable(DumpHuriSortant::class);

        $repo = $this->getDoctrine()->getManager()->getRepository(CPerson::class);
        $persons = $repo->findAll();

        if ($persons != null) {
            foreach ($persons as $person){
                $res = $this->getDoctrine()
                    ->getRepository(DumpT::class)
                    ->dumpCSV($this->getParameter('targetdirectory').'/'.$person->getCFileName());
            }
            $this->getDoctrine()
                ->getRepository(TRecord::class)
                ->dumpAll();
        }
        $request->getSession()->getFlashBag()->add('dump_success', 'Donnees traitees avec succès');

        return $this->redirectToRoute('home');

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