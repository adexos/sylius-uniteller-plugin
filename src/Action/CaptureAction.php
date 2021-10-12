<?php declare(strict_types=1);
/**
 * This file is part of the Adexos package.
 * (c) Adexos <contact@adexos.fr>
 */

namespace Adexos\SyliusUnitellerPlugin\Action;

use Adexos\SyliusUnitellerPlugin\Action\Api\UnitellerApiAware;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
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

        if (null === $model['URL_RETURN_OK'] && $request->getToken()) {
            $model['URL_RETURN_OK'] = $request->getToken()->getAfterUrl();
        }

        /** @var ClientInterface $client */
        $client = $this->api;
        $uri    = $client->payment($model->toUnsafeArray())->getUri();

        throw new HttpRedirect($uri);
    }

    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
