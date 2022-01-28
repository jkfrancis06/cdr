<?php

namespace App\Controller;

use BackupManager\Filesystems\Destination;
use BackupManager\Manager as Manager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackupController extends AbstractController
{

    private $_dbDir;
    private $backupManager;


    public function __construct(string $dbDir,Manager $backupManager)
    {
        $this->_dbDir = $dbDir;
        $this->backupManager = $backupManager;
    }

    /**
     * @Route("/backup", name="backup")
     *
     */
    public function index(): Response
    {


        $files = [];

        $data = [];

        if (file_exists($this->_dbDir)) {
            $files = array_diff(scandir($this->_dbDir), array('.', '..'));

            foreach ($files as $file) {

                $tmp = [];
                $tmp['original_name'] = $file;
                $tmp['name'] =  strtok($file, '.') ;


                $tmp['date'] =  date('d/m/Y H:i:s', intval($tmp['name']));

                array_push($data,$tmp);

            }

        }


        return $this->render('backup/index.html.twig', [
            'controller_name' => 'BackupController',
            'saves' => $data,
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
