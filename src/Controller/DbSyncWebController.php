<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */


namespace App\Controller;

use Egnx\DbSyncWeb\Adapters\MysqlAdapterLib\MysqlAdapter;
use Egnx\DbSyncWeb\Adapters\MysqlAdapterLib\MysqlFactory;
use Egnx\DbSyncWeb\ProtocolLib\Processor;
use Egnx\DbSyncWeb\Share\ProcessResult;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Driver\PDO\Connection;

class DbSyncWebController extends AbstractController
{
    /**
     * @Route("/dbsyncweb", name="db_sync_web")
     */
    public function index(Request $request): Response
    {

        $em = $this->getDoctrine()->getManager();
        $conn = $em->getConnection();

        $conn = MysqlFactory::getconnection(
            $conn->getHost(),
            $conn->getPort(),
            $conn->getUsername(),
            $conn->getPassword(),
            $conn->getDatabase()
        );

        $result = new ProcessResult();
        $result->Statut = "ok";
        $response = new JsonResponse($result);

        try {


            foreach ($request->files as $file) {

                $inputFile = $file->getPathname();

                $mySqlAdapter = new MysqlAdapter();
                $mySqlAdapter->setDbconnection($conn);
                $tempFile = tempnam(sys_get_temp_dir(), "result");
                rename($tempFile, $tempFile . ".zip");
                $tempFile = $tempFile . ".zip";

                $processor = new Processor();

                $processor->proceedarchive($mySqlAdapter, $inputFile, $tempFile);

                if (filesize($tempFile) == 0) {
                    unlink($tempFile);

                } else {
                    $response = new  BinaryFileResponse($tempFile);
                    $response->headers->set('Content-Type', 'octet-stream');
                    $response->deleteFileAfterSend(true);
                }


            }
        }
        catch (\Exception $ex)
        {
            $result = new ProcessResult();
            $result->Statut = "error";
            $result->ErrorDescription = $ex->getMessage();
            $response = new JsonResponse($result);
        }

        return  $response;
    }
}


