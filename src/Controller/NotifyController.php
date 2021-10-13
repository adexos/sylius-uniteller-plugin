<?php declare(strict_types=1);
/**
 * This file is part of the Adexos package.
 * (c) Adexos <contact@adexos.fr>
 */

namespace Adexos\SyliusUnitellerPlugin\Controller;

use Payum\Core\Payum;
use Payum\Core\Request\Notify;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotifyController
{
    private Payum $payum;
    private PaymentRepositoryInterface $paymentRepository;

    public function __construct(Payum $payum, PaymentRepositoryInterface $paymentRepository)
    {
        $this->payum = $payum;
        $this->paymentRepository = $paymentRepository;
    }

    public function doAction(Request $request): Response
    {
        $orderId = $request->get('Order_ID');
        $status = $request->get('Status');
        $signature = $request->get('Signature');

        /** @var PaymentInterface $payment */
        $payment = $this->paymentRepository->createQueryBuilder('p')
            ->innerJoin('p.order', 'o')
            ->where('o.number = :orderId' )
            ->setParameter('orderId', $orderId)
            ->getQuery()
            ->getOneOrNullResult();

        $notifyToken = $payment->getDetails()['extraData']['notifyToken'];

        if (null === $token = $this->payum->getTokenStorage()->find($notifyToken)) {
            throw new NotFoundHttpException(sprintf("A token with hash `%s` could not be found.", $notifyToken));
        }

        /** @var TokenInterface $token */
        $gateway = $this->payum->getGateway($token->getGatewayName());
        $gateway->execute(new Notify($token));

        return new Response("[accepted]");

    }
}
