<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ETDLineService;
use App\Tool\StringTools;
use App\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CustomAPIController extends AbstractController
{
    protected SerializerInterface $serializer;
    protected ValidatorInterface $validator;
    protected DenormalizerInterface $normalizer;
    protected EntityManagerInterface $_entityManager;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator, DenormalizerInterface $normalizer, EntityManagerInterface $entityManager)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->normalizer = $normalizer;
        $this->_entityManager = $entityManager;
    }

    protected function getVendorNo() {
        if($this->getUser()->getVendor() !== null) {
            return $this->getUser()->getVendor()->getNo();
        }
        return null;
    }

    protected function getUserTypeSerializeGroups(string $userType): array
    {
        switch ($userType) {
            case UserType::PURCHASER:
                return ['groups' => 'purchaser'];
            case UserType::VENDOR:
                return ['groups' => 'vendor'];
            default:
                throw new InvalidArgumentException('userType is not handled');
        }
    }

    #region QueryParams
    protected function doesQueryParamExist(?array $queryParams, string $queryParamName): bool
    {
        return $queryParams !== null
            && array_key_exists($queryParamName, $queryParams)
            && !StringTools::is_null_or_empty($queryParams[$queryParamName]);
    }

    protected function getQueryParams(Request $request) {
        $parts = parse_url($request->getRequestUri());
        if(array_key_exists('query', $parts)) {
            parse_str($parts['query'], $query);
            return $query;
        }
        return null;
    }

    protected function setRequestParams(Request $request): void
    {
        $params = $this->getQueryParams($request);
        if($params !== null) {
            foreach ($params as $key => $param) {
                $request->query->set($key, $params[$key]);
            }
        }
    }
    #endregion

    #region Serializer
    protected function serialize($data, string $format, ?array $context): string
    {
        if($context === null) {
            $context = [];
        }
        $context['circular_reference_handler'] = static function ($object) {
            return $object->getId();
        };
        $context['json_encode_options'] = JSON_INVALID_UTF8_IGNORE;

        return $this->serializer->serialize($data, $format, $context);
    }

    protected function deserialize($data, string $class, string $format)
    {
        return $this->serializer->deserialize($data, $class, $format);
    }

    /**
     * @throws ExceptionInterface
     */
    protected function denormalize($data, string $format)
    {
        return $this->normalizer->denormalize($data, $format, 'array', [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]);
    }
    #endregion

    protected function createConversationPDF($etdService, $knpSnappyPdf, $etdId, $messages, $etdLineId = null, ETDLineService $etdLineService = null): string
    {
        $etd = $etdService->findETDById($etdId);
        if(!$etd) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $title = $etd->getVendor()->getNo() . ' ' . $etd->getEtdDate()->format('Y-m-d');
        if($etdLineId && $etdLineService) {
            $etdLine = $etdLineService->findOneBy(['id' => $etdLineId]);
            if(!$etdLine) {
                return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
            }
            $title .= ' ' . $etdLine->getNavisionDocNo() . ' ' . $etdLine->getItemReference();
        }
        $title .= ' conversation';

        $html = $this->renderView('pdf/conversation.html.twig', array(
            'messages'  => $messages,
            'title' => $title
        ));
        return $knpSnappyPdf->getOutputFromHtml($html, array(
            'lowquality' => false,
            'encoding' => 'utf-8',
        ));
    }
}