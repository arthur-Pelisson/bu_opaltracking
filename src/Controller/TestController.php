<?php


namespace App\Controller;

use App\Dto\PartialMessage;
use App\Dto\UpdateETDLinesMessageCounters;
use App\Entity\ETD;
use App\Entity\ETDLine;
use App\Entity\ETDLineTag;
use App\Repository\ETDLineTagRepository;
use App\Repository\ETDRepository;
use App\Repository\UserRepository;
use App\Service\ConversationService;
use App\Service\ETDLineService;
use App\Service\ETDService;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/test", name="app_test_")
 */
class TestController extends CustomAPIController
{
    /**
     * @Route("/validateupdates", name="validateupdates", methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param ETDLineService $etdLineService
     * @return JsonResponse
     */
    public function validateUpdates(Request $request, UserRepository $userRepository, ETDLineService $etdLineService): JsonResponse
    {
        try {
            $etdId = 91;
            $requestContent = $request->toArray();
            if ($requestContent !== null && !empty($requestContent)) {
                $etdLinesAsArray = $requestContent['etdLines'];

                /** @var ETDLine[] $etdLinesFromRequest */
                foreach ($etdLinesAsArray as $etdLineAsArray) {
                    if($etdLineAsArray !== null) {
                        $etdLinesFromRequest[] = $this->denormalize($etdLineAsArray, ETDLine::class);
                    }
                }

                if ($etdLinesFromRequest === null || empty($etdLinesFromRequest)) {
                    return new JsonResponse('ETDLines content is empty !', Response::HTTP_BAD_REQUEST);
                }

                $partialMessages = [];
                foreach ($requestContent['partialMessages'] as $message) {
                    $partialMessages[] = $this->denormalize($message, PartialMessage::class);
                }
                $counters = $this->denormalize($requestContent['counters'], UpdateETDLinesMessageCounters::class);
                $etdUpdated = $etdLineService->updateETDLines($userRepository->findOneBy(['code' => 'FKONG']), $etdLinesFromRequest, $etdId, $requestContent['message'], $counters, $partialMessages);
                if($etdUpdated) {
                    $this->_entityManager->flush();
                }
                return new JsonResponse(null, Response::HTTP_NO_CONTENT);
            }

            return new JsonResponse('No ETDLines are provided !', Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/conversation/download", name="conv_download", methods={"GET"})
     * @param ETDService $etdService
     * @param ConversationService $conversationService
     * @param Request $request
     * @param Pdf $knpSnappyPdf
     * @return Response
     */
    public function conversationDownload(ETDService $etdService, ConversationService $conversationService, Request $request, Pdf $knpSnappyPdf)
    {
        $etdId = 111;
        try {
            $messages = $conversationService->getConversationMessagesByETDId($etdId);
            $etd = $etdService->findETDById($etdId);
            $title = $etd->getVendor()->getNo() . ' ' . $etd->getEtdDate()->format('Y-m-d') . ' conversation';

            $html = $this->renderView('pdf/conversation.html.twig', array(
                'messages'  => $messages,
                'title' => $title
            ));
            $pdf = $knpSnappyPdf->getOutputFromHtml($html, array(
                'lowquality' => false,
                'encoding' => 'utf-8',
            ));
            $filename = 'test';
            return new Response(
                $pdf,
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'attachment; filename="'.$filename.'.pdf"'
                )
            );
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/export/csv", name="export_csv", methods={"GET"})
     * @param ETDService $etdService
     * @param ETDLineService $etdLineService
     * @param UserRepository $userRepository
     * @param Request $request
     * @return Response
     */
    public function exportCSV(ETDService $etdService, ETDLineService $etdLineService, UserRepository $userRepository, Request $request): Response
    {
        try {
            $etd = $etdService->findETDById(98);
            $user = $userRepository->findOneBy(['code' => 'PBELIN']);
            $testCsv = $etdLineService->getETDLinesAsCSV($etd, $user);
            return new Response(null, Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/email/test", name="email_send", methods={"GET"})
     * @param MailerService $mailerService
     * @param Request $request {
     *    @var string "emailTo"
     * }
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function emailTest(MailerService $mailerService, Request $request, UrlGeneratorInterface $urlGeneratorInterface): Response
    {
        // $bodyData = $request->toArray();
        // $email = $bodyData["emailTo"];

        try {
            $mailerService->testMail('opaltracking@opal.fr', "pelisson.arthur@gmail.com", 'opaltracking@opal.fr');
            return new Response(null, Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/email/etd/render/{id}", name="email", methods={"GET"})
     * @param ETDService $etdService
     * @param Request $request {
     *  @var int "ETDId"
     * }
     * @return Response
     */
    public function emailETDRender(ETDService $etdService, Request $request): Response
    {
        $id = $request->get('id');
        try {
            $etd = $etdService->findETDById($id);
            $counters = new UpdateETDLinesMessageCounters();
            $counters->setCountApprovedChanged(0);
            $counters->setCountRejectedChanged(1);
            $counters->setCountValidateETDDateChanged(1);
            $counters->setCountValidateQtyChanged(1);
            $counters->setCountValidateShipByChanged(1);

            $subject = 'ETD ' . $etd->getEtdDate()->format('Y-m-d') . ' has been updated';
            $etdUrl = '/etd/' . $etd->getId() . '/etdlines';

            return $this->render('emails/etdupdated.html.twig', [
                'etd' => $etd,
                'counters' => $counters,
                'title' => $subject,
                'etdUrl' => $etdUrl,
                'message' => 'Ceci est mon message'
            ]);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 
     * @Route("/updateetdcounts", name="updateetdcounts", methods={"POST"})
     * @param ETDService $etdService
     * @param Request $request {
     *   @var array "etdIds": [1, 2, 3]
     * }
     * @return Response
     */
    public function updateETDsCounts(ETDService $etdService, Request $request): Response
    {
        try {
            $etdIds = $request->toArray();
            $updatedETDIds = $etdService->updateETDsCounts($etdIds);
            $this->_entityManager->flush();

            return new JsonResponse($updatedETDIds, Response::HTTP_OK);

        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * This function is used to test the update of tags of the ETDLine entity
     * @Route("/updatetags", name="updatetags", methods={"POST"})
     * @param ETDService $etdService
     * @param ETDLineTagRepository $ETDLineTagRepository
     * @param ETDRepository $ETDRepository
     * @param Request $request {
     *     @var array "EtdsId": [1, 2, 3]
     * }
     * @return Response
     */
    public function updateTags(Request $request, ETDService $etdService, ETDLineTagRepository $ETDLineTagRepository, ETDRepository $ETDRepository): Response
    {

        $EtdsId = $request->toArray();

        try {
            $tag1 = $ETDLineTagRepository->find($EtdsId[0]);
            $tag1->setEtdChanged(true);
            $tag2 = $ETDLineTagRepository->find($EtdsId[1]);
            $tag2->setShipByChanged(true);
            $tag3 = $ETDLineTagRepository->find($EtdsId[2]);
            $tag3->setQtyChanged(true);

            $this->_entityManager->flush();

            return new JsonResponse(null, Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}