<?php declare(strict_types=1);
/**
 * This file is part of the Adexos package.
 * (c) Adexos <contact@adexos.fr>
 */

namespace Adexos\SyliusUnitellerPlugin\Action;

use Adexos\SyliusUnitellerPlugin\Action\Api\UnitellerApiAware;
use Adexos\Uniteller\Client;
use Adexos\Uniteller\Model\Payment;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use Payum\Uniteller\Action\Api\BaseApiAwareAction;
use Tmconsulting\Uniteller\ClientInterface;

class CaptureAction extends UnitellerApiAware implements GenericTokenFactoryAwareInterface
{
    use GenericTokenFactoryAwareTrait;

    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var Capture $request */
        $model = ArrayObject::ensureArrayObject($request->getModel());

        /** @var Payment $payment */
        $payment = unserialize($model['payment'], ['allowed_classes' => [Payment::class]]);

        $token = $request->getToken();
        if ($token) {
            $payment->setUrlReturnNo($token->getTargetUrl());
            $payment->setUrlReturnOk($token->getAfterUrl());
        }

        if (!isset($model['extraData']) && $token) {
            $model['extraData'] = [
                'captureToken' => $token->getHash(),
                'notifyToken' => $this->generateNotifyToken($token, $model)->getHash(),
                'refundToken' => $this->generateRefundToken($token, $model)->getHash()
            ];
        }

        /** @var Client $client */
        $client = $this->api;
        $request = $client->payment($payment);
        $params = [];
        parse_str($request->getBody()->getContents(), $params);
        throw new HttpPostRedirect($request->getUri(), $params);
    }

    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }

    private function generateRefundToken(TokenInterface $token, ArrayObject $model): TokenInterface
    {
        return $this->tokenFactory->createRefundToken($token->getGatewayName(), $token->getDetails() ?? $model);
    }

    private function generateNotifyToken(TokenInterface $token, ArrayObject $model): TokenInterface
    {
        return $this->tokenFactory->createNotifyToken($token->getGatewayName(), $token->getDetails() ?? $model);
    }
}
