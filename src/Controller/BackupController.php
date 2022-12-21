<?php

namespace App\Controller;

use App\Form\BackupType;
use BackupManager\Filesystems\Destination;
use BackupManager\Manager as Manager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupController extends AbstractController
{

    private $_dbDir;
    private $backupManager;


    public function __construct($dbDir,Manager $backupManager)
    {
        $this->_dbDir = $dbDir;
        $this->backupManager = $backupManager;
    }

    /**
     * @Route("/dir", name="dir")
     *
     */
    public function test(Request  $request): Response
    {


        $process = new Process((array)'ls -lsa /srv/app/ressources/db');
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return new Response($process->getOutput());
    }

    /**
     * @Route("/backup", name="backup")
     *
     */
    public function index(Request  $request): Response
    {

        $form = $this->createForm(BackupType::class);

        $form->handleRequest($request);


        $backupName = $form->get('nom')->getData();

        if ($backupName != null && file_exists($this->_dbDir.'/'.$backupName.'.sql.gz')){

            $fileError = new FormError("Une sauvegarde avec ce nom existe deja");
            $form->get('nom')->addError($fileError);
        }


        if ($form->isSubmitted() && $form->isValid()) {

            $now = new \DateTime(null, new \DateTimeZone('Africa/Nairobi'));


            $this->backupManager->makeBackup()->run('development',
                [new Destination('local',
                    $backupName == null ? $now->getTimestamp() : $backupName .'.sql')],
                'gzip');

            $this->addFlash('success_backup', 'La sauvegarde a ete cree avec succes');



            return $this->redirectToRoute('backup');

        }


        $files = [];

        $data = [];


        if (file_exists($this->_dbDir)) {
            $files = array_diff(scandir($this->_dbDir), array('.', '..'));


            foreach ($files as $file) {

                $tmp = [];
                $tmp['original_name'] = $file;
                $tmp['name'] =  strtok($file, '.') ;

                $tmp['date'] = date ("d-m-Y H:i:s.", filemtime($this->_dbDir.'/'.$file));

                array_push($data,$tmp);

            }

        }

        usort($data, function ($a, $b) {
            return strtotime($b["date"]) - strtotime($a["date"]) ;
        });



        return $this->render('backup/index.html.twig', [
            'controller_name' => 'BackupController',
            'saves' => $data,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/backup/new", name="new_backup")
     *
     */
    public function new(): Response
    {

        $now = new \DateTime(null, new \DateTimeZone('Africa/Nairobi'));


        $this->backupManager->makeBackup()->run('development', [new Destination('local', $now->getTimestamp().'.sql')], 'gzip');

        $this->addFlash('success_backup', 'La sauvegarde a ete cree avec succes');



        return $this->redirectToRoute('backup');

    }


    /**
     * @Route("/backup-restore/{name}", name="backup_restore")
     *
     */
    public function restore($name): Response
    {

        if (file_exists($this->_dbDir.'/'.$name.'.sql.gz')) {
            $this->backupManager->makeRestore()->run('local', $name.'.sql.gz', 'development', 'gzip');
        }

        $this->addFlash('success_restore', 'La sauvegarde a ete restoree avec succes');



        return $this->redirectToRoute('backup');

    }

    /**
     * @Route("/backup-delete/{name}", name="backup_remove")
     *
     */
    public function delete($name): Response
    {


        if (file_exists($this->_dbDir.'/'.$name.'.sql.gz')) {
            unlink($this->_dbDir.'/'.$name.'.sql.gz');
        }

        $this->addFlash('success_remove', 'La sauvegarde a ete supprimee avec succes');



        return $this->redirectToRoute('backup');

    }

}
