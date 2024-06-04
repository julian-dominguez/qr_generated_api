<?php

namespace App\Tests\Controller;

use App\Controller\QrGeneratorController;
use App\Response\QrGenerateResponse;
use App\Service\Interface\QrCodeGeneratorInterface;
use Endroid\QrCode\Writer\Result\ResultInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class QrGeneratorControllerTest extends TestCase
{
    private QrCodeGeneratorInterface $qrCodeGeneratorMock;
    private ResultInterface $qrGenerated;

    protected function setUp(): void
    {
        $this->qrCodeGeneratorMock = $this->createMock(QrCodeGeneratorInterface::class);
        $this->qrGenerated = $this->createMock(ResultInterface::class);
    }

    /**
     * Prueba que el método index lanza una HttpException cuando no se proporcionan datos.
     *
     * @return void
     * @throws HttpException
     */
    public function testIndexThrowsHttpExceptionWhenNoDataProvided(): void
    {
        $controller = new QrGeneratorController($this->qrCodeGeneratorMock);
        $request = new Request();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('No data provided');
        $this->expectExceptionCode(JsonResponse::HTTP_BAD_REQUEST);

        $controller->index($request);
    }

    /**
     * Prueba que el método index devuelve un QrGenerateResponse cuando getDataUri es false.
     *
     * @return void
     */
    public function testIndexReturnsQrGenerateResponseWhenGetDataUriIsFalse(): void
    {
        $qrCodeData = ['getDataUri' => false];
        $request = new Request([], [], [], [], [], [], json_encode($qrCodeData));

        $this->qrCodeGeneratorMock->expects($this->once())
            ->method('qrCodeGenerator')
            ->with($qrCodeData)
            ->willReturn($this->qrGenerated);

        $controller = new QrGeneratorController($this->qrCodeGeneratorMock);

        $response = $controller->index($request);

        $this->assertInstanceOf(QrGenerateResponse::class, $response);
    }

    /**
     * Prueba si el método index devuelve una respuesta JSON con un URI de datos cuando el parámetro 'getDataUri'
     * se establece en true en los datos de la petición.
     *
     * @return void
     */
    public function testIndexReturnsJsonResponseWithDataUriWhenGetDataUriIsTrue(): void
    {
        $qrCodeData = ['getDataUri' => true];
        $request = new Request([], [], [], [], [], [], json_encode($qrCodeData));

        $this->qrGenerated->expects($this->once())
            ->method('getDataUri')
            ->willReturn('data:image/png;base64,iVBORw0KGg...');

        $this->qrCodeGeneratorMock->expects($this->once())
            ->method('qrCodeGenerator')
            ->with($qrCodeData)
            ->willReturn($this->qrGenerated);

        $controller = new QrGeneratorController($this->qrCodeGeneratorMock);

        $response = $controller->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame('"data:image\/png;base64,iVBORw0KGg..."', $response->getContent());
    }

}
