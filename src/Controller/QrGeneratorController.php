<?php

declare(strict_types=1);

namespace App\Controller;

use App\Response\QrGenerateResponse;
use App\Service\Interface\QrCodeGeneratorInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1', name: 'api_')]
class QrGeneratorController extends AbstractController
{


    public function __construct(protected QrCodeGeneratorInterface $qrCodeGenerator)
    {
    }

    #[Route('/qr/generator', name: 'v1_qr_generator', methods: ['POST'])]
    public function index(Request $request): JsonResponse|QrGenerateResponse
    {
        $qrCodeData = json_decode($request->getContent(), true);

        if (is_null($qrCodeData)) {
            return new JsonResponse(['message' => 'Invalid body request'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $qrGenerated = $this->qrCodeGenerator->qrCodeGenerator($qrCodeData);
        } catch (Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], $e->getStatusCode());
        }

        if ($qrCodeData['getDataUri']) {
            return new JsonResponse($qrGenerated->getDataUri());
        } else {
            return new QrGenerateResponse($qrGenerated);
        }
    }
}
