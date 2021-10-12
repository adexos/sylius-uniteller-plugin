<?php declare(strict_types=1);
/**
 * This file is part of the Adexos package.
 * (c) Adexos <contact@adexos.fr>
 */

namespace Adexos\SyliusUnitellerPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetStatusInterface;
use Tmconsulting\Uniteller\Order\Status;

class StatusAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $this->getOrderId($model) && null === $this->getParameter($model, 'Status')) {
            $request->markNew();
            return;
        }

        if ($this->getOrderId($model) && null === $this->getParameter($model, 'Status')) {
            $request->markPending();

            return;
        }

        switch (Status::resolve($this->getParameter($model, 'Status'))) {
            case Status::AUTHORIZED:
                $request->markAuthorized();
                break;
            case Status::NOT_AUTHORIZED:
                $request->markFailed();
                break;
            case Status::PAID:
                $request->markCaptured();
                break;
            case Status::CANCELLED:
                $request->markCanceled();
                break;
            case Status::WAITING:
                $request->markPending();
                break;
            default:
                $request->markUnknown();
                break;
        }
    }

    public function supports($request)
    {
        return $request instanceof GetStatusInterface
            && $request->getModel() instanceof \ArrayAccess;
    }

    protected function getParameter(ArrayObject $model, $key)
    {
        return $model->get($key);
    }

    protected function getOrderId(ArrayObject $model)
    {
        return $model->get('Order_ID', $model->get('Order_IDP'));
    }
}
