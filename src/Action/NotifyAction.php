<?php declare(strict_types=1);
/**
 * This file is part of the Adexos package.
 * (c) Adexos <contact@adexos.fr>
 */

namespace Adexos\SyliusUnitellerPlugin\Action;

use Adexos\SyliusUnitellerPlugin\Action\Api\UnitellerApiAware;
use Adexos\Uniteller\ClientInterface;
use Adexos\Uniteller\Model\Order;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;

class NotifyAction extends UnitellerApiAware
{
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest);

        /** @var ClientInterface $client */
        $client = $this->api;

        $order = new Order();
        $order->setStatus($httpRequest->request['Status'])
            ->setOrderId($httpRequest->request['Order_ID']);

        if (! $client->isValidOrder($order, $httpRequest->request['Signature'])) {
            throw new HttpResponse('Notification signature is invalid.', 400);
        }

        $model->replace($httpRequest->request);

        throw new HttpResponse('OK');
    }

    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
