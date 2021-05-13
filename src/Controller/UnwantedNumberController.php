<?php


namespace App\Controller;

use App\Entity\DumpHuri;
use App\Entity\DumpT;
use App\Entity\TRecord;
use App\Entity\UnwantedNumber;
use App\Form\UnwantedNumberType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UnwantedNumberController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{


    /**
     * @Route("/unwanted-number", name="unwanted_number")
     */
    public function homeWizard(Request  $request){

        // creates a task object and initializes some data for this example
        $unwanted = new UnwantedNumber();

        $form = $this->createForm(UnwantedNumberType::class, $unwanted);

        $trecord_repo = $this->getDoctrine()->getManager()->getRepository(TRecord::class);

        $unwanted_repo = $this->getDoctrine()->getManager()->getRepository(UnwantedNumber::class);

        $unwanted_numbers_serialized = [];


        $form->handleRequest($request);

        if ($form->isSubmitted()){
            $record = $trecord_repo->findOneBy([
                'num_a' => $unwanted->getNumber()
            ]);
            if ($record != null){
                $numberError = new FormError("Le numero indesirable ne peut pas être le numero d'un sujet. Spprimer plutot directement le sujet dans le wizard");
                $form->get('number')->addError($numberError);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $rows_count = $trecord_repo->countRecords($unwanted->getNumber());
            $unwanted->setUnwantedRowsCount($rows_count);
            $trecord_repo->deleteUnwantedRecords($unwanted->getNumber());

            $request->getSession()->getFlashBag()->add('unwanted_add', 'Le numéro a été filtré des enregistrements');

            $un = $unwanted_repo->findOneBy([
                'number' => $unwanted->getNumber()
            ]);

            if ($un == null){
                $em = $this->getDoctrine()->getManager();
                $em->persist($unwanted);
                $em->flush();
            }

        }

        if ($unwanted_repo->findAll() != null){
            $unwanted_numbers = $unwanted_repo->findAll();
            $serializer = new Serializer(array(new ObjectNormalizer()));
            $unwanted_numbers_serialized = $serializer->normalize($unwanted_numbers, null);
        }

        return $this->render('unwanted/number.html.twig',[
            'form' => $form->createView(),
            'unwanted_numbers' => $unwanted_numbers_serialized
        ]);

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


}