<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Interface\QrCodeGeneratorInterface;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WebPWriter;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class QrCodeGenerator implements QrCodeGeneratorInterface
{

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function qrCodeGenerator(array|null $data): ResultInterface
    {
        if (!isset($data['format']) || $data['format'] == 'png') {
            $writer = new PNGWriter();
        }
        elseif ($data['format'] == 'svg') {
            $writer = new SVGWriter();
        }
        elseif ($data['format'] == 'webp') {
            $writer = new WebPWriter();
        }
        else {
            throw new HttpException(
                statusCode: JsonResponse::HTTP_BAD_REQUEST,
                message: 'Invalid value for format type',
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // Crear una nueva instancia de código QR con la URL proporcionada
        $qrCode = QRCode::create($data['message'] ?? '')
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize($data['size'] ?? 300)
            ->setMargin($data['margin'] ?? 10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0)) // Establecer el color de primer plano
            ->setBackgroundColor(new Color(255, 255, 255)); // Establecer el color de fondo

        // creamos una etiqueta con la fuente a NotoSans con tamaño 20
        $label = Label::create($data['label'] ?? '')->setFont(new NotoSans(20));

        // Escribir el código QR en el formato especificado
        return $writer->write($qrCode, null, $label);
    }

}