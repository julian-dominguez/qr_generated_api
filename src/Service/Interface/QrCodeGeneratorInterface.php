<?php

declare(strict_types=1);

namespace App\Service\Interface;

use Endroid\QrCode\Writer\Result\ResultInterface;

interface QrCodeGeneratorInterface
{
    /**
     * Genera una imagen de código QR con los valores requeridos.
     *
     * @param array $data
     * @return ResultInterface La URI de datos del código QR generado.
     */
    public function qrCodeGenerator(array $data): ResultInterface;

}