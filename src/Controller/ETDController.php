<?php


namespace App\Controller;

use App\Entity\ETD;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ConversationService;
use App\Service\ETDLineService;
use App\Service\ETDService;
use App\Service\FileService;
use App\Type\ETDScreenType;
use App\Type\UserType;
use Exception;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ETDController extends CustomAPIController
{
    const ETDS_TEMPLATE = 'etds.html.twig';
    const ETDS_TEMPLATE_IGNORED_ATTRIBUTES = ['ignored_attributes' => ['vendor']];

    #region Export

    /**
     * @Route("/{etdId}/export", name="app_etd_export", requirements={"etdId"="\d+"}, methods={"GET"})
     * @param ETDLineService $etdLineService
     * @param ETDService $etdService
     * @param Request $request
     * @param int $etdId
     * @return Response
     */
    public function exportETDLinesFile(ETDLineService $etdLineService, ETDService $etdService, Request $request, int $etdId): Response
    {
        try {
            $etd = $this->getAuthorizedETD($etdId, $etdService);
            $fileContent = $etdLineService->getETDLinesAsCSV($etd, $this->getUser());
            $fileName = 'export_etd_' . $etd->getVendor()->getNo() . '_' . $etd->getEtdDate()->format('Y-m-d') .  '.csv';
            return new Response(
                $fileContent,
                200,
                array(
                    'Content-Disposition'   => 'attachment; filename="' . $fileName .'"'
                )
            );
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #endregion

    #region Files
    /**
     * @Route("/{etdId}/files", name="app_import_files", requirements={"etdId"="\d+"}, methods={"POST"})
     * @param ETDService $etdService
     * @param FileService $fileService
     * @param Request $request
     * @param int $etdId
     * @return Response
     */
    public function importFiles(ETDService $etdService, FileService $fileService, Request $request, int $etdId): Response
    {
        try {
            $etd = $this->getAuthorizedETD($etdId, $etdService);
            $files = $request->files;
            if($files=== null || count($files) === 0) {
                return new JsonResponse('No files uploaded',  Response::HTTP_NOT_ACCEPTABLE);
            }
            $fileService->importFiles($etd, $files);
            $files = $fileService->getETDFiles($etd);
            return new Response($this->serialize($files, 'json', null));
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/{etdId}/files", name="app_get_files", requirements={"etdId"="\d+"}, methods={"GET"})
     * @param ETDService $etdService
     * @param FileService $fileService
     * @param Request $request
     * @param int $etdId
     * @return Response
     */
    public function getFiles(ETDService $etdService, FileService $fileService, Request $request, int $etdId): Response
    {
        try {
            $etd = $this->getAuthorizedETD($etdId, $etdService);
            $files = $fileService->getETDFiles($etd);
            return new Response($this->serialize($files, 'json', null));
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/{etdId}/file/{fileName}", name="app_delete_file", requirements={"etdId"="\d+"}, methods={"DELETE"})
     * @param ETDService $etdService
     * @param FileService $fileService
     * @param Request $request
     * @param int $etdId
     * @param string $fileName
     * @return Response
     */
    public function deleteFile(ETDService $etdService, FileService $fileService, Request $request, int $etdId, string $fileName): Response
    {
        try {
            $etd = $this->getAuthorizedETD($etdId, $etdService);
            $fileContent = $fileService->deleteFile($etd, $fileName);
            return new Response($this->serialize($fileContent, 'json', null));
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @Route("/{etdId}/file/{fileName}", name="app_download_file", requirements={"etdId"="\d+"}, methods={"GET"})
     * @param ETDService $etdService
     * @param FileService $fileService
     * @param Request $request
     * @param int $etdId
     * @param string $fileName
     * @return Response
     */
    public function downloadFile(ETDService $etdService, FileService $fileService, Request $request, int $etdId, string $fileName): Response
    {
        try {
            $etd = $this->getAuthorizedETD($etdId, $etdService);
            $fileName = urldecode($fileName);
            $fileContent = $fileService->downloadFile($etd, $fileName);
            return new Response(
                $fileContent,
                200,
                array(
                    'Content-Disposition'   => 'attachment; filename="' . $fileName .'"'
                )
            );
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getAuthorizedETD(int $etdId, ETDService $etdService) {
        $etd = $etdService->findETDById($etdId);
        if ($etd === null) {
            return new JsonResponse('ETD is not found', Response::HTTP_NOT_FOUND);
        }
        /** @var User $user */
        $user = $this->getUser();
        if($user->getType() === UserType::VENDOR && $etd->getVendor() !== $user->getVendor()) {
            return new JsonResponse('You cannot access to the files', Response::HTTP_UNAUTHORIZED);
        }
        return $etd;
    }

    #endregion

    #region ETD

    /**
     * @Route("/", name="app_home", methods={"GET"})
     * @param ETDService $etdService
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function home(ETDService $etdService, PaginatorInterface $paginator, Request $request): Response
    {
        try {
            $page = $request->query->getInt('page', 1);
            $pagination = $etdService->getETDsPagination($paginator, $page, $this->getUser(), false, true);
            return $this->renderETDs($pagination, ETDScreenType::ACTIVE);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/opens", name="app_opens", methods={"GET"})
     * @param ETDService $etdService
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function opens(ETDService $etdService, PaginatorInterface $paginator, Request $request): Response
    {
        try {
            $this->setRequestParams($request);
            $page = $request->query->getInt('page', 1);
            $pagination = $etdService->getETDsPagination($paginator, $page, $this->getUser(), false, false);
            return $this->renderETDs($pagination, ETDScreenType::OPEN);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/archives", name="app_archives", methods={"GET"})
     * @param ETDService $etdService
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function archives(ETDService $etdService, PaginatorInterface $paginator, Request $request): Response
    {
        try {
            $page = $request->query->getInt('page', 1);
            $pagination = $etdService->getETDsPagination($paginator, $page, $this->getUser(), true);
            return $this->renderETDs($pagination, ETDScreenType::ARCHIVE);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function renderETDs(PaginationInterface $pagination, string $etdScreenType): Response
    {
        return $this->render(self::ETDS_TEMPLATE, [
            'user' => $this->serialize($this->getUser(), 'json', self::ETDS_TEMPLATE_IGNORED_ATTRIBUTES),
            'etds' => $this->serialize($pagination, 'json', $this->getUserTypeSerializeGroups($this->getUser()->getType())),
            'pagination' => $pagination,
            'etdScreenType' => $etdScreenType,
            'totalCount' => $pagination->getTotalItemCount()
        ]);
    }

    /**
     * @Route("/getlastetdsupdated", name="app_getlastetdupdated", methods={"GET"})
     * @param ETDService $etdService
     * @param Request $request
     * @return JsonResponse
     */
    public function getLastETDsUpdated(ETDService $etdService, Request $request): Response
    {
        try {
            $user = $this->getUser();
            if ($user === null) {
                return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
            }

            $lastETDsUpdated = $etdService->getLastETDsUpdated($user);
            return new Response($this->serialize($lastETDsUpdated, 'json', $this->getUserTypeSerializeGroups($user->getType())), Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @Route("/etd/{etdId}/close", name="app_etd_close", requirements={"etdId"="\d+"}, methods={"POST"})
     * @param ETDService $etdService
     * @param Request $request
     * @param int $etdId
     * @return JsonResponse
     */
    public function closeETD(ETDService $etdService, Request $request, int $etdId): JsonResponse
    {
        try {
            if ($etdId === null) {
                return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
            }

            $etdClosed = $etdService->closeETD($etdId, $this->getUser(), $request->getContent());
            $this->_entityManager->flush();

            return new JsonResponse($etdClosed, Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @Route("/etd/{etdId}/open", name="app_etd_open", requirements={"etdId"="\d+"}, methods={"POST"})
     * @param ETDService $etdService
     * @param Request $request
     * @param int $etdId
     * @return JsonResponse
     */
    public function openETD(ETDService $etdService, Request $request, int $etdId): JsonResponse
    {
        try {
            if ($etdId === null) {
                return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
            }

            $etdOpened = $etdService->openETD($etdId, $this->getUser(), $request->getContent());
            $this->_entityManager->flush();

            return new JsonResponse($etdOpened, Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #endregion

    #region Conversation

    /**
     * @Route("/etd/{etdId}/conversation/download", name="app_etd_conversation_download", requirements={"etdId"="\d+"}, methods={"GET"})
     * @param ETDService $etdService
     * @param ConversationService $conversationService
     * @param Pdf $knpSnappyPdf
     * @param int $etdId
     * @return Response
     */
    public function conversationDownload(ETDService $etdService, ConversationService $conversationService, Pdf $knpSnappyPdf, int $etdId)
    {
        try {
            $messages = $conversationService->getConversationMessagesByETDId($etdId);
            $pdf = $this->createConversationPDF($etdService, $knpSnappyPdf, $etdId, $messages);
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
     * @Route("/etd/{etdId}/conversation", name="app_etd_getconversation", requirements={"etdId"="\d+"}, methods={"GET"})
     * @param ConversationService $conversationService
     * @param int $etdId
     * @return JsonResponse
     */
    public function getConversation(ConversationService $conversationService, int $etdId): Response
    {
        try {
            if ($etdId === null) {
                return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
            }
            $conversationMessages = $conversationService->getConversationMessagesByETDId($etdId);
            return new Response($this->serialize($conversationMessages, 'json', ['groups' => 'messages']), Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/etd/{etdId}/conversation/addmessage", name="app_etd_conversation_addmessage", requirements={"etdId"="\d+"}, methods={"POST"})
     * @param ETDService $etdService
     * @param Request $request
     * @param int $etdId
     * @return JsonResponse
     */
    public function addConversationMessage(ETDService $etdService, Request $request, int $etdId): JsonResponse
    {
        try {
            if ($etdId === null) {
                return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
            }

            $messageContent = $etdService->addConversationMessage($etdId, $this->getUser(), $request->getContent());
            $this->_entityManager->flush();

            return new JsonResponse($messageContent, Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #endregion
}