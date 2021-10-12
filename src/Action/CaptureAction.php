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
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Uniteller\Action\Api\BaseApiAwareAction;
use Tmconsulting\Uniteller\ClientInterface;

class CaptureAction extends UnitellerApiAware
{
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var Capture $request */
        $model = ArrayObject::ensureArrayObject($request->getModel());

        /** @var Payment $payment */
        $payment = unserialize($model['payment'], ['allowed_classes' => [Payment::class]]);

        if ($request->getToken()) {
            $payment->setUrlReturnOk($request->getToken()->getAfterUrl());
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
}
