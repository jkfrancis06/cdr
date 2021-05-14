<?php


namespace App\Controller;

use App\Entity\DumpHuri;
use App\Entity\DumpT;
use App\Entity\TRecord;
use App\Entity\UnwantedNumber;
use App\Form\UnwantedNumberType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UnwantedNumberController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{


    /**
     * @Route("/unwanted-number", name="unwanted_number")
     */
    public function homeWizard(PaginatorInterface $paginator,Request  $request){

        // creates a task object and initializes some data for this example
        $unwanted = new UnwantedNumber();

        $form = $this->createForm(UnwantedNumberType::class, $unwanted);

        $trecord_repo = $this->getDoctrine()->getManager()->getRepository(TRecord::class);

        $unwanted_repo = $this->getDoctrine()->getManager()->getRepository(UnwantedNumber::class);

        $unwanted_numbers_serialized = [];


        $form->handleRequest($request);

        if ($form->isSubmitted()){

            if ($form->get('number')->isEmpty() &&
                $form->get('char')->isEmpty() &&
                $form->get('supto')->isEmpty() &&
                $form->get('infto')->isEmpty() &&
                $form->get('rangeinf')->isEmpty() &&
                $form->get('rangesup')->isEmpty() ){


                $filter_error = new FormError("L'un des filtres doit etre saisit");
                $form->get('number')->addError($filter_error);
                $form->get('char')->addError($filter_error);
                $form->get('supto')->addError($filter_error);
                $form->get('infto')->addError($filter_error);
                $form->get('rangeinf')->addError($filter_error);
                $form->get('rangesup')->addError($filter_error);

            }

            if (!$form->get('number')->isEmpty()){
                $record = $trecord_repo->findOneBy([
                    'num_a' => $unwanted->getNumber()
                ]);
                if ($record != null){
                    $numberError = new FormError("Le numero indesirable ne peut pas être le numero d'un sujet. Spprimer plutot directement le sujet dans le wizard");
                    $form->get('number')->addError($numberError);
                }
            }

            if (!$form->get('rangeinf')->isEmpty() || !$form->get('rangesup')->isEmpty() ){

                if ($form->get('rangeinf')->getData() > $form->get('rangesup')->getData()){
                    $filter_error = new FormError("Le munimum ne peut pas etre superieur a max");
                    $form->get('rangeinf')->addError($filter_error);
                    $form->get('rangesup')->addError($filter_error);
                }

            }
            /* */

        }

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$form->get('number')->isEmpty()) {
                $this->insertNumbers($form->get('number')->getData(),$form->get('description')->getData());
            }

            if (!$form->get('char')->isEmpty()) {

                $matching_numbers = $trecord_repo->getDistinctNumbersByChar($form->get('char')->getData());

                foreach ($matching_numbers as $matching_number) {
                    $this->insertNumbers($matching_number["num_b"],$form->get('description')->getData());
                }

            }

            if (!$form->get('infto')->isEmpty()) {
                $matching_numbers = $trecord_repo->getDistinctNumbersByComp($form->get('infto')->getData(),"inf");
                foreach ($matching_numbers as $matching_number) {
                    $this->insertNumbers($matching_number["num_b"],$form->get('description')->getData());
                }
            }

            if (!$form->get('supto')->isEmpty()) {
                $matching_numbers = $trecord_repo->getDistinctNumbersByComp($form->get('supto')->getData(),"sup");
                foreach ($matching_numbers as $matching_number) {
                    $this->insertNumbers($matching_number["num_b"],$form->get('description')->getData());
                }
            }


            if (!$form->get('rangeinf')->isEmpty() && !$form->get('rangesup')->isEmpty()) {
                $matching_numbers = $trecord_repo->getDistinctNumbersByRange($form->get('rangeinf')->getData(),$form->get('rangesup')->getData());
                foreach ($matching_numbers as $matching_number) {
                    $this->insertNumbers($matching_number["num_b"],$form->get('description')->getData());
                }
            }



            $request->getSession()->getFlashBag()->add('unwanted_add', 'Le numéro a été filtré des enregistrements');



        }

        $unwanted_numbers = $unwanted_repo->findAll();
        $unwanted_page = $paginator->paginate(
            $unwanted_numbers, /* query NOT result */
            $request->query->getInt('unwanted_page', 1)/*page number*/,
            10/*limit per page*/,
            ['pageParameterName' => 'unwanted_page']
        );


        return $this->render('unwanted/number.html.twig',[
            'form' => $form->createView(),
            'unwanted_page' => $unwanted_page
        ]);

    }

    public function insertNumbers($number, $description = null) {
        $new_number = new UnwantedNumber();
        $new_number->setNumber($number);
        $trecord_repo = $this->getDoctrine()->getManager()->getRepository(TRecord::class);
        $unwanted_repo = $this->getDoctrine()->getManager()->getRepository(UnwantedNumber::class);
        $rows_count = $trecord_repo->countRecords($new_number->getNumber());
        $new_number->setUnwantedRowsCount($rows_count);
        $new_number->setDescription($description);
        $un = $unwanted_repo->findOneBy([
            'number' => $new_number->getNumber()
        ]);

        if ($un == null){
            $em = $this->getDoctrine()->getManager();
            $em->persist($new_number);
            $em->flush();
            $trecord_repo->deleteUnwantedRecords($new_number->getNumber());
        }

    }



    /**
     * @Route("/unwanted-number/restore/{id}", name="restore_unwanted_number")
     */

    public function restoreUnwantedNumber($id, Request $request) {

        $unwanted_repo = $this->getDoctrine()->getManager()->getRepository(UnwantedNumber::class);
        $t_record_repo = $this->getDoctrine()->getManager()->getRepository(TRecord::class);

        $unwanted = $unwanted_repo->find($id);

        if ($unwanted != null) {

            $t_record_repo->restoreRecords($unwanted->getNumber());
            $em = $this->getDoctrine()->getManager();
            $em->remove($unwanted);
            $em->flush();

            $request->getSession()->getFlashBag()->add('restore_unwanted', 'Les enregistrements ont été restaurés');

        }

        return $this->redirectToRoute('unwanted_number');


    }


    /**
     * @Route("/unwanted-number/get-distinct-numbers", name="xhr_get_distinc_numbers")
     */

    public function getDistinctNumbers(){
        $t_record_repo = $this->getDoctrine()->getManager()->getRepository(TRecord::class);
        $numbers_array = [];
        $numbers_array = $t_record_repo->getDistinctNumbers();

        return new JsonResponse($numbers_array,200);
    }



}