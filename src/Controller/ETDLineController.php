<?php


namespace App\Controller;

use App\Dto\PartialMessage;
use App\Dto\UpdateETDLinesMessageCounters;
use App\Entity\ETD;
use App\Entity\ETDLine;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ConfigurationService;
use App\Service\ConversationService;
use App\Service\ETDLineService;
use App\Service\ETDService;
use App\Service\MailerService;
use App\Type\UserType;
use Exception;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/etd/{etdId}/etdlines", name="app_etdlines_", requirements={"etdId"="\d+"})
 */
class ETDLineController extends CustomAPIController
{
    /**
     * @Route("", name="home", requirements={"etdId"="\d+"}, methods={"GET"})
     * @param Request $request
     * @param ETDLineService $etdLineService
     * @param ETDService $etdService
     * @param ConfigurationService $configurationService
     * @param int $etdId
     * @return Response
     */
    public function home(Request $request, ETDLineService $etdLineService, ETDService $etdService, ConfigurationService $configurationService, int $etdId): ?Response
    {
        try {
            $etdWithCounts = $etdService->findETDWithCounts($etdId);
            $etd = $etdWithCounts['etd'];
            if ($etd === null) {
                return new JsonResponse('ETD is not found', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $isUpdatable = $etdService->canUserUpdateETD($etd, $this->getUser());
            $conversationsCount = $etdLineService->getConversationCount($etdId);
            $etdLines = $etdLineService->getParentETDLines($this->getUser(), $etdId);
            $qtyModifiedToCreatePartial = $configurationService->getQtyModifiedToCreatePartial();

            return $this->render('etdlines.html.twig', [
                'user' => $this->serialize($this->getUser(), 'json', ['ignored_attributes' => ['vendor']]),
                'etd' => $this->serialize($etd, 'json', $this->getUserTypeSerializeGroups($this->getUser()->getType())),
                'etdLines' => $this->serialize($etdLines, 'json', $this->getUserTypeSerializeGroups($this->getUser()->getType())),
                'qtyModifiedToCreatePartial' => $qtyModifiedToCreatePartial,
                'conversationsCount' => $this->serialize($conversationsCount, 'json', null),
                'isReadOnly' => $this->serialize(!$isUpdatable,'json', null),
                'vendorNo' => $etdWithCounts['vendorNo'],
                'etdConversationCount' => $etdWithCounts['countMessages'],
            ]);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/validateupdates", name="validateupdates", methods={"POST"})
     * @param Request $request
     * @param ETDLineService $etdLineService
     * @param MailerService $mailerService
     * @param int $etdId
     * @return JsonResponse
     */
    public function validateUpdates(Request $request, ETDLineService $etdLineService, MailerService $mailerService, int $etdId): JsonResponse
    {
        try {
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
                $etdUpdated = $etdLineService->updateETDLines($this->getUser(), $etdLinesFromRequest, $etdId, $requestContent['message'], $counters, $partialMessages);
                if($etdUpdated) {
                    $this->_entityManager->flush();
                    $mailerService->sendUpdateETDLinesEmail($etdUpdated, $counters, $requestContent['message'], $this->getUser());
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
     * @Route("/{etdLineId}/conversation/download", name="app_etd_conversation_download", requirements={"etdId"="\d+"}, methods={"GET"})
     * @param ETDService $etdService
     * @param ETDLineService $etdLineService
     * @param ConversationService $conversationService
     * @param Request $request
     * @param Pdf $knpSnappyPdf
     * @param int $etdId
     * @param int $etdLineId
     * @return Response
     */
    public function conversationDownload(ETDService $etdService, ETDLineService $etdLineService, ConversationService $conversationService, Request $request, Pdf $knpSnappyPdf, int $etdId, int $etdLineId)
    {
        try {
            $messages = $conversationService->getConversationMessagesByETDLineId($etdLineId);
            $pdf = $this->createConversationPDF($etdService, $knpSnappyPdf, $etdId, $messages, $etdLineId, $etdLineService);
            if($pdf instanceof JsonResponse) {
                return $pdf;
            }
            return new Response(
                $pdf,
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'attachment; filename="conversation.pdf"'
                )
            );
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @Route("/{etdLineId}/conversation", name="conversation_get", requirements={"etdId"="\d+", "etdLineId"="\d+"}, methods={"GET"})
     * @param ConversationService $conversationService
     * @param int $etdLineId
     * @return JsonResponse
     */
    public function getConversation(ConversationService $conversationService, int $etdLineId): Response
    {
        try {
            if ($etdLineId === null) {
                return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
            }
            $conversationMessages = $conversationService->getConversationMessagesByETDLineId($etdLineId);
            return new Response($this->serialize($conversationMessages, 'json', ['groups' => 'messages']));
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @Route("/{etdLineId}/conversation/addmessage", name="conversation_addmessage", requirements={"etdId"="\d+", "etdLineId"="\d+"}, methods={"POST"})
     * @param ETDLineService $ETDLineService
     * @param Request $request
     * @param int $etdLineId
     * @return JsonResponse
     */
    public function addConversationMessage(ETDLineService $ETDLineService, Request $request, int $etdLineId): JsonResponse
    {
        try {
            if ($etdLineId === null) {
                return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
            }

            $messageContent = $ETDLineService->addConversationMessage($etdLineId, $this->getUser(), $request->getContent());
            $this->_entityManager->flush();

            return new JsonResponse($messageContent);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}