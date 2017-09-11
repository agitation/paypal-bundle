<?php
declare(strict_types=1);
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

class Worker
{
    /**
     * @var ValidationService
     */
    private $validator;

    /**
     * @var PaymentWorker
     */
    private $paymentWorker;

    /**
     * @var Api
     */
    private $api;

    /**
     * @var Config
     */
    private $config;

    public function __construct(ValidationService $validator, PaymentWorker $paymentWorker, Api $api, Config $config)
    {
        $this->paymentWorker = $paymentWorker;
        $this->api = $api;
        $this->config = $config;
        $this->validator = $validator;
    }

    public function initPayment(OrderInterface $order, $method, array $details)
    {
        // we expect an empty array
        $this->validator->validate('array', $details, 0, 0);

        $payment = $this->paymentWorker->createPayment($order, 'pp', 'pp', $details);
        $ecResult = $this->api->callSetExpressCheckout($payment);

        // update payment details with received token
        $details = $payment->getDetails();
        $details['token'] = $ecResult['token'];
        $payment->setDetails($details);
        $this->paymentWorker->persistPayment($payment);

        return $payment;
    }

    public function getForwardPage(Payment $payment)
    {
        $config = $this->config->getParameters();
        $details = $payment->getDetails();
        $fields = ['cmd' => '_express-checkout', 'token' => $details['token']];

        return new ForwardPage($config['paymentPageUrl'], 'get', $fields);
    }

    public function processPayment(Payment $payment, $action, Request $request)
    {
        if ($action !== 'confirm')
        {
            throw new PaymentRequestException(sprintf('Invalid action for this payment module: %s.', $method));
        }

        if ($payment->getStatus() !== $payment::STATUS_OPEN)
        {
            throw new PaymentRequestException(sprintf('Payment %s has already been processed.', $payment->getId()));
        }

        $details = $payment->getDetails();

        if (! isset($details['token']))
        { // somehow a payment was submitted which wasn't initialized yet.
            throw new PaymentRequestException(sprintf('Payment %s has not been initialized yet.', $payment->getId()));
        }

        try
        {
            // first get details (especially PayerID), then execute payment
            foreach (['callGetExpressCheckoutDetails', 'callDoExpressCheckoutPayment'] as $method)
            {
                $txnDetails = $this->api->$method($payment);
                $details = $payment->getDetails() + $txnDetails;
                $payment->setDetails($details);
            }
        }
        catch (PaymentProviderCallException $e)
        {
            $this->paymentWorker->paymentFailed(
                $payment,
                Translate::noop('The PayPal request for payment %s has failed, most likely because the transaction has already been processed.'),
                [$payment->getId()],
                $e->getMessage()
            );
        }

        if (strtolower($details['paymentinfo_0_paymentstatus']) !== 'completed')
        {
            $this->paymentWorker->paymentFailed(
                $payment,
                Translate::noop('The transaction status for payment %s indicates a failed payment.'),
                [$payment->getId()],
                $details['paymentinfo_0_paymentstatus']
            );
        }

        $this->paymentWorker->checkReceivedAmount($payment, (float) $details['paymentinfo_0_amt']);

        // ok, everything’s fine

        $this->paymentWorker->finishPayment($payment, $payment::STATUS_COMPLETE);
    }
}
