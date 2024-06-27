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
use OpenApi\Attributes as OA;

#[Route('/api/v1', name: 'api_')]
class QrGeneratorController extends AbstractController
{

    public function __construct(protected QrCodeGeneratorInterface $qrCodeGenerator)
    {
    }

    #[Route('/qr/generator', name: 'v1_qr_generator', methods: ['POST'])]
    #[OA\Tag("QR Generator")]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    description: 'Información que se incluye en el QR. Puede ser una cadena de texto o un link.',
                    type: 'string',
                    example: 'https://example.com'

                ),
                new OA\Property(
                    property: 'label',
                    description: 'Etiqueta del QR. Por defecto es "QR Code".',
                    type: 'string',
                    example: 'QR Code'

                ),
                new OA\Property(
                    property: 'getDataUri',
                    description: 'Indica si se debe retornar la URI de la imagen del QR. Por defecto es true.',
                    type: 'boolean',
                    example: true
                ),
                new OA\Property(
                    property: 'format',
                    description: 'Formato de la imagen del QR. Por defecto es PNG. Formatos validos: (svg, png, webp).',
                    type: 'string',
                    example: 'png'
                ),
                new OA\Property(
                    property: 'size',
                    description: 'Tamaño de la imagen del QR. Por defecto es 200.',
                    type: 'integer',
                    example: 300
                ),
                new OA\Property(
                    property: 'margin',
                    description: 'Margen de la imagen del QR. Por defecto es 10.',
                    type: 'integer',
                    example: 10
                ),
                new OA\Property(
                    property: 'fontSize',
                    description: 'Tamaño de la fuente del QR. Por defecto es 20.',
                    type: 'integer',
                    example: 20
                )
            ],
            example: [
                'data' => 'https://example.com',
                'label' => 'QR Code',
                'getDataUri' => true,
                'format' => 'png',
                'size' => 300,
                'margin' => 10,
                'fontSize' => 20
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized response',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'code',
                    type: 'integer',
                    example: 401
                ),
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'JWT Token not found || Expired JWT Token'
                )

            ]
        )
    )]
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
