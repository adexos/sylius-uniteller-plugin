<?php declare(strict_types=1);
/**
 * This file is part of the Adexos package.
 * (c) Adexos <contact@adexos.fr>
 */

namespace Tests\Adexos\SyliusUnitellerPlugin\Action;

use Adexos\SyliusUnitellerPlugin\Action\CaptureAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Model\Token;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Tests\GenericActionTest;
use Tests\Adexos\SyliusUnitellerPlugin\Traits\ApiAwareTestTrait;
use Tests\Adexos\SyliusUnitellerPlugin\Traits\GatewayAwareTestTrait;
use Tests\Adexos\SyliusUnitellerPlugin\Traits\MockTrait;
use Tmconsulting\Uniteller\Client;
use Tmconsulting\Uniteller\Payment\Uri;

class CaptureActionTest extends GenericActionTest
{
    use MockTrait, GatewayAwareTestTrait, ApiAwareTestTrait;

    protected $requestClass = Capture::class;
    protected $actionClass = CaptureAction::class;

    public function couldBeConstructedWithoutAnyArguments()
    {
        $this->expectNotToPerformAssertions();
        parent::couldBeConstructedWithoutAnyArguments();
    }

    public function testCaptureSupport()
    {
        $action  = new CaptureAction();
        $request = new Capture(new ArrayObject());

        $this->assertTrue($action->supports($request));
    }

    public function testSupportsApiClass()
    {
        $this->expectNotToPerformAssertions();
        $action = new CaptureAction();
        $action->setApi(new Client());
    }

    public function testExceptionThrowsIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CaptureAction();
        $action->execute(new \stdClass());
    }

    public function testHttpReplyExceptionThrowsWhenRequestExcecuted()
    {
        $this->expectException(HttpRedirect::class);

        $expectedRedirection = 'https://google.com/?q=url_generated';
        $model = new \ArrayObject([
            'Order_IDP'     => mt_rand(10000, 99999),
            'Subtotal_P'    => 10,
            'Customer_IDP'  => mt_rand(10000, 99999),
            'URL_RETURN_NO' => 'https://google.com/?q=failure',
        ]);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->any())
            ->method('execute');

        $clientMock = $this->createClientMock();
        $clientMock
            ->expects($this->once())
            ->method('payment')
            ->willReturn(new Uri($expectedRedirection));

        $action  = new CaptureAction();
        $action->setGateway($gatewayMock);
        $action->setApi($clientMock);

        $token = new Token();
        $token->setTargetUrl('targetUri');

        $request = new Capture($token);
        $request->setModel($model);

        try {
            $action->execute($request);
        } catch (HttpRedirect $exception) {
            $this->assertEquals($expectedRedirection, $exception->getHeaders()['Location']);

            throw $exception;
        }
    }
}
