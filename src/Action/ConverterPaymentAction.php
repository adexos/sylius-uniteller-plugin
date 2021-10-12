<?php declare(strict_types=1);
/**
 * This file is part of the Adexos package.
 * (c) Adexos <contact@adexos.fr>
 */

namespace Adexos\SyliusUnitellerPlugin\Action;

use Adexos\Uniteller\Model\Customer;
use Adexos\Uniteller\Model\Payment;
use Adexos\Uniteller\Model\Receipt;
use Adexos\Uniteller\Model\ReceiptLine;
use Adexos\Uniteller\Model\ReceiptPayment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Sylius\Component\Core\Model\OrderInterface;

class ConverterPaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var \Sylius\Component\Core\Model\PaymentInterface $payment */
        $payment = $request->getSource();
        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $total = (float) $payment->getAmount() / 100;


        $receipt = (new Receipt())
            ->setTotal($total)
            ->setCustomer((new Customer())
                ->setPhone((string) $order->getCustomer()->getPhoneNumber())
                ->setEmail((string) $order->getCustomer()->getEmail())
            )
            ->addPayment((new ReceiptPayment())
                ->setAmount($total)
            )
        ;

        foreach ($order->getItems() as $item) {
            $receipt->addLine((new ReceiptLine())
                ->setQty($item->getQuantity())
                ->setPrice((float) $item->getUnitPrice() /100)
                ->setSum((float) $item->getTotal() / 100)
                ->setName($item->getProductName())
            );
        }

        if ($order->getShippingTotal() > 0) {
            $receipt->addLine((new ReceiptLine())
                ->setQty(1)
                ->setName('Shipping')
                ->setSum($order->getShippingTotal() / 100 )
                ->setPrice($order->getShippingTotal() / 100)
            );
        }


        $requestPayment = new Payment();
        $requestPayment->setCurrency($payment->getCurrencyCode())
            ->setSubtotalP($total)
            ->setOrderIdp($payment->getOrder()->getNumber())
            ->setReceipt($receipt)
        ;


        $details['payment'] = serialize($requestPayment);
        $request->setResult((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof \Sylius\Component\Core\Model\PaymentInterface &&
            $request->getTo() === 'array'
            ;
    }
}
