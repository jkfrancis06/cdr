<?php


namespace App\Controller;
use App\Entity\CPerson;
use App\Entity\DumpHuri;
use App\Entity\DumpHuriEntrant;
use App\Entity\DumpHuriSortant;
use App\Entity\DumpT;
use App\Entity\TRecord;
use App\Entity\UnwantedNumber;
use App\Form\CPersonType;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
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

        // huri number verification pattern
        $pattern_huri = '/^[37][0-9]{6}$/';
        $pattern_telma = '/^[4][0-9]{6}$/';

        $is_new = true;  // check if is new user to add or not (modify user)

        $file_data = [];

        $repo = $this->getDoctrine()->getManager()->getRepository(CPerson::class);
        // if number in url
        if($c_number != null) {
            // find it in the database
            $c_person = $repo->findOneBy([
               'c_number' => $c_number
            ]);
            // if NOT FOUND in the database
            if ($c_person == null) {
                $c_person = new CPerson();
            }else{
                $is_new = false;  //
            }
        }else{
            $c_person = new CPerson();
        }

        $a_person_serialized = [];
        // serialize data to send
        if ($repo->findAll() != null){
            $c_persons = $repo->findAll();
            $serializer = new Serializer(array(new ObjectNormalizer()));
            $a_person_serialized = $serializer->normalize($c_persons, null);
        }


        $form = $this->createForm(CPersonType::class, $c_person);

        $em = $this->getDoctrine()->getManager();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $cdrFile = $form->get('c_file_name')->getData();
            if ($cdrFile == null && $is_new){
                $fileError = new FormError("Veuillez envoyer un fichier");
                $form->get('c_file_name')->addError($fileError);
            }


            if ($cdrFile != null ) {


                if ($cdrFile->getClientOriginalExtension() == "csv") {


                    // opens csv file
                    if (($handle = fopen($cdrFile->getPathname(), "r")) !== false) {
                        // loop through lines
                        while (($data = fgetcsv($handle,"",";")) !== false) {

                            $file_data = $data;
                            // check if is huri csv and remove hidden characters
                            if (preg_match($pattern_huri,preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0] ))){

                                $c_person->setCOperator("HURI");
                                // check if cdr has been already uploaded
                                $c_person_repo = $this->getDoctrine()->getManager()->getRepository(CPerson::class);
                                $bdd_person = $c_person_repo->findOneBy([
                                    'c_number' => preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0] )
                                ]);

                                if ($bdd_person != null) {
                                    $fileError = new FormError("Le CDR de ce numéro a déjà été uploadé");
                                    $form->get('c_file_name')->addError($fileError);
                                }

                            }else {


                                if (preg_match($pattern_telma,$data[5])){

                                    $c_person->setCOperator("TELMA");
                                    if (preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[5] ) == ""){
                                        $fileError = new FormError("Numero incorrect dans le fichier CDR, Ligne 1 Colonne 6");
                                        $form->get('c_file_name')->addError($fileError);
                                    }else{
                                        // check if cdr has been already uploaded
                                        $bdd_person = $repo->findOneBy([
                                            'c_number' => $data[5]
                                        ]);
                                        if ($bdd_person != null) {
                                            $fileError = new FormError("Le CDR de ce numéro a déjà été uploadé");
                                            $form->get('c_file_name')->addError($fileError);
                                        }
                                    }

                                }else{
                                    $fileError = new FormError("Le numero ne correspond a aucun operateur");
                                    $form->get('c_file_name')->addError($fileError);
                                }

                            }

                            break;
                        }
                        fclose($handle);
                    }

                }else{

                    $fileError = new FormError("Seuls les fichiers csv sont autorisés");
                    $form->get('c_file_name')->addError($fileError);

                }

            }
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $cdrFile = $form->get('c_file_name')->getData();

            if($cdrFile != null){

                $uploadedFileName = $fileUploader->upload($cdrFile);
                $c_person->setCFileName($uploadedFileName);

                $dir = $this->getParameter('kernel.project_dir').'/public/upload/';

                if (($handle = fopen($dir.''.$uploadedFileName, "r")) !== false) {
                    while (($data = fgetcsv($handle,"",";")) !== false) {
                        if ($c_person->getCOperator() == "HURI") {
                            $c_person->setCNumber(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]));
                            $c_person->setANom($data[2]);
                        }else{
                            $c_person->setCNumber(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[5]));
                            $c_person->setANom($data[12]);
                        }
                        break;
                    }
                    fclose($handle);
                }
            }

            $imgFile = $form->get('c_image_name')->getData();
            if ($imgFile != null){
                $uploadedImgName = $fileUploader->upload($imgFile,$this->getParameter('kernel.project_dir').'/public/img');
                $c_person->setCPicName($uploadedImgName);
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

            $request->getSession()->getFlashBag()->add('unwanted_add', 'Le sujet a été ajouté avec succès');

        }


        return $this->render('home/wizard.html.twig',[
            'form' => $form->createView(),
            'c_persons' => $a_person_serialized,
            'is_new' => $is_new,
            'number' => $c_number
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
        $dump_t_repo = $this->getDoctrine()->getManager()->getRepository(DumpT::class);
        $dump_h_repo = $this->getDoctrine()->getManager()->getRepository(DumpHuri::class);
        $t_rec_repo = $this->getDoctrine()->getManager()->getRepository(TRecord::class);

        if ($c_person != null) {

            $t_rec_del = $t_rec_repo->deleteNumberRecords($c_person->getCNumber());

            if ($c_person->getCOperator() == "TELMA"){
               $dump_t_del =  $dump_t_repo->deleteNumberRecords($c_person->getCNumber());
            }else{
                $dump_h_del = $dump_h_repo->deleteNumberRecords($c_person->getCNumber());
            }

            try {
                unlink($this->getParameter('targetdirectory').'/'.$c_person->getCFileName());
            }catch (\Exception $exception){

            }
            $em = $this->getDoctrine()->getManager();
            $em->remove($c_person);
            $em->flush();

            $request->getSession()->getFlashBag()->add('remove_c_person', 'Le sujet a été supprimé avec succès');
        }

        return $this->redirectToRoute('home_wizard');



    }


    /**
     * @Route("/wizard-validate/dump-data", name="dump_data")
     */

    public function dumpWizardData(Request $request){


        $dump_t_repo = $this->getDoctrine()->getManager()->getRepository(DumpT::class);
        $dump_h_repo = $this->getDoctrine()->getManager()->getRepository(DumpHuri::class);
        $t_rec_repo = $this->getDoctrine()->getManager()->getRepository(TRecord::class);
        $unwanted_repo = $this->getDoctrine()->getManager()->getRepository(UnwantedNumber::class);

        $repo = $this->getDoctrine()->getManager()->getRepository(CPerson::class);
        $persons = $repo->findAll();

        $person_array = [];


        if ($persons != null) {
            foreach ($persons as $person){

                array_push($person_array,$person->getCNumber());

                $record = $t_rec_repo->findOneBy([
                   'num_a' => $person->getCNumber()
                ]);


                if ($record == null){
                    if ($person->getCOperator() == "TELMA"){

                        $dump = $dump_t_repo->findOneBy([
                            'num_a' => $person->getCNumber()
                        ]);

                        if ($dump == null) {
                            $encode = $this->getDoctrine()
                                ->getRepository(DumpT::class)
                                ->standardTEncoding($this->getParameter('targetdirectory'), $person->getCFileName());

                            $res = $this->getDoctrine()
                                ->getRepository(DumpT::class)
                                ->dumpCSV($this->getParameter('targetdirectory').'/'.$person->getCFileName());
                        }

                        $this->getDoctrine()
                            ->getRepository(TRecord::class)
                            ->dumpTelmaTrecord($person->getCNumber());


                    }else{

                        $dump = $dump_h_repo->findOneBy([
                            'num_a' => $person->getCNumber()
                        ]);

                        if ($dump == null) {
                            $encode = $this->getDoctrine()
                                ->getRepository(DumpHuri::class)
                                ->standardHEncoding($this->getParameter('targetdirectory'), $person->getCFileName());


                            $res = $this->getDoctrine()
                                ->getRepository(DumpHuri::class)
                                ->dumpHuriCSV($this->getParameter('targetdirectory').'/'.$person->getCFileName());
                        }

                        $this->getDoctrine()
                            ->getRepository(TRecord::class)
                            ->dumpHuriTrecord($person->getCNumber());



                    }
                }

            }


            $this->getDoctrine()
                ->getRepository(TRecord::class)
                ->parseNames();

            // apply filter


            $unwanted_numbers = $unwanted_repo->findAll();
            if ($unwanted_numbers != null){
                foreach ($unwanted_numbers as $unwanted_number){
                    if (!in_array($unwanted_number->getNumber(),$person_array)){
                        $t_rec_repo->deleteUnwantedRecords($unwanted_number->getNumber());
                    }
                }
            }



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