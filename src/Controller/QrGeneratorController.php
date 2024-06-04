<?php

declare(strict_types=1);

namespace App\Controller;

use App\Response\QrGenerateResponse;
use App\Service\Interface\QrCodeGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
            throw new HttpException(
                statusCode: JsonResponse::HTTP_BAD_REQUEST,
                message: 'No data provided',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $qrGenerated = $this->qrCodeGenerator->qrCodeGenerator($qrCodeData);

        if ($qrCodeData['getDataUri']) {
            return new JsonResponse($qrGenerated->getDataUri());
        } else {
            return new QrGenerateResponse($qrGenerated);
        }
    }
}
