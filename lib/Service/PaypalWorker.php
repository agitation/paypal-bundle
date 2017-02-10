<?php

/*
 * @package    agitation/paypal-bundle
 * @link       http://github.com/agitation/paypal-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\PaypalBundle\Service;

use Agit\IntlBundle\Tool\Translate;
use Agit\OrderBundle\Entity\OrderInterface;
use Agit\OrderBundle\Entity\Payment;
use Agit\OrderBundle\Exception\PaymentProviderCallException;
use Agit\OrderBundle\Exception\PaymentRequestException;
use Agit\OrderBundle\Object\ForwardPage;
use Agit\OrderBundle\Service\PaymentWorker;
use Agit\ValidationBundle\ValidationService;
use Symfony\Component\HttpFoundation\Request;

class PaypalWorker
{
    private $validator;

    private $paymentWorker;

    private $paypalApi;

    private $paypalConfig;

    public function __construct(ValidationService $validator, PaymentWorker $paymentWorker, PaypalApi $paypalApi, PaypalConfig $paypalConfig)
    {
        $this->paymentWorker = $paymentWorker;
        $this->paypalApi = $paypalApi;
        $this->paypalConfig = $paypalConfig;
        $this->validator = $validator;
    }

    public function initPayment(OrderInterface $order, $method, array $details)
    {
        $this->validator->validate("array", $details, 0, 0);

        $payment = $this->paymentWorker->createPayment($order, "paypal", "paypal", $details);
        $ecResult = $this->paypalApi->callSetExpressCheckout($payment);

        // update payment details with received token
        $details = $payment->getDetails();
        $details["token"] = $ecResult["token"];
        $payment->setDetails($details);
        $this->paymentWorker->persistPayment($payment);

        return $payment;
    }

    public function getForwardPage(Payment $payment)
    {
        $config = $this->paypalConfig->getConfig();
        $details = $payment->getDetails();
        $fields = ["cmd" => "_express-checkout", "token" => $details["token"]];

        return new ForwardPage($config["paymentPageUrl"], "get", $fields);
    }

    public function processPayment(Payment $payment, $action, Request $request)
    {
        if ($action !== "confirm") {
            throw new PaymentRequestException(sprintf("Invalid action for this payment module: %s.", $method));
        }

        if ($payment->getStatus() !== $payment::STATUS_OPEN) {
            throw new PaymentRequestException(sprintf("Payment %s has already been processed.", $payment->getFullCode()));
        }

        $details = $payment->getDetails();

        if (! isset($details["token"])) { // somehow a payment was submitted which wasn't initialized yet.
            throw new PaymentRequestException(sprintf("Payment %s has not been initialized yet.", $payment->getFullCode()));
        }

        try {
            // first get details (especially PayerID), then execute payment
            foreach (["get" => "callGetExpressCheckoutDetails", "do" => "callDoExpressCheckoutPayment"] as $type => $method) {
                $txnDetails = $this->paypalApi->$method($payment);
                $details = $payment->getDetails() + $txnDetails;
                $payment->setDetails($details);
            }
        } catch (PaymentProviderCallException $e) {
            $this->paymentWorker->paymentFailed(
                $payment,
                Translate::noop("The PayPal request for payment %s has failed, most likely because the transaction has already been processed."),
                [$payment->getFullCode()],
                $e->getMessage()
            );
        }

        if (strtolower($details["paymentinfo_0_paymentstatus"]) !== "completed") {
            $this->paymentWorker->paymentFailed(
                $payment,
                Translate::noop("The transaction status for payment %s indicates a failed payment."),
                [$payment->getFullCode()],
                $details["paymentinfo_0_paymentstatus"]
            );
        }

        $this->paymentWorker->checkReceivedAmount($payment, (float) $details["paymentinfo_0_amt"]);

        // ok, everything’s fine

        $this->paymentWorker->finishPayment($payment, $payment::STATUS_COMPLETE);
    }
}
