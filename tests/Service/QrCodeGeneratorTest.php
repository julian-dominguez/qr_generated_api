<?php

namespace App\Tests\Service;

use App\Service\QrCodeGenerator;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class QrCodeGeneratorTest extends TestCase
{
    /**
     * Prueba si el método qrCodeGenerator devuelve una instancia de ResultInterface.
     *
     * @return void
     * @throws Exception
     */
    public function testQrCodeGeneratorReturnsResultInterface(): void
    {
        $qrCodeGenerator = new QrCodeGenerator();
        $data = ['format' => 'png'];
        $result = $qrCodeGenerator->qrCodeGenerator($data);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    /**
     *  prueba si el método qrCodeGenerator lanza una HttpException por un formato no válido.
     *
     * @return void
     * @throws Exception
     * @throws HttpException si no se lanza la excepción esperada
     */
    public function testQrCodeGeneratorThrowsHttpExceptionForInvalidFormat(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid value for format type');
        $this->expectExceptionCode(JsonResponse::HTTP_BAD_REQUEST);

        $qrCodeGenerator = new QrCodeGenerator();
        $data = ['format' => 'invalid'];
        $qrCodeGenerator->qrCodeGenerator($data);
    }

}
