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
use Agit\OrderBundle\Entity\Payment;
use Agit\OrderBundle\Exception\PaymentProviderCallException;
use Agit\OrderBundle\Service\OrderUrlService;

class Api
{
    private $orderUrlService;

    private $parameters;

    private $settings;

    public function __construct(OrderUrlService $orderUrlService, Config $config)
    {
        $this->orderUrlService = $orderUrlService;
        $this->parameters = $config->getParameters();
        $this->settings = $config->getSettings();
    }

    public function callSetExpressCheckout(Payment $payment)
    {
        $expectedResultFields = ['token'];
        $postFields = ['METHOD' => 'SetExpressCheckout'];
        $postFields += $this->getCommonPostFields($payment);

        return $this->doCall($postFields, $expectedResultFields);
    }

    public function callGetExpressCheckoutDetails(Payment $payment)
    {
        $expectedResultFields = ['token', 'payerid'];

        $postFields = [
            'METHOD' => 'GetExpressCheckoutDetails',
            'TOKEN' => $payment->getDetails()['token']
        ];

        $postFields += $this->getCommonPostFields($payment);
        $txnDetails = $this->doCall($postFields, $expectedResultFields);

        if ($txnDetails['checkoutstatus'] !== 'PaymentActionNotInitiated')
        {
            throw new PaymentProviderCallException(sprintf(
                'Payment %s has already been processed: %s',
                $payment->getId(),
                $txnDetails['checkoutstatus']
            ));
        }

        return $txnDetails;
    }

    public function callDoExpressCheckoutPayment(Payment $payment)
    {
        $expectedResultFields = ['paymentinfo_0_paymentstatus'];
        $details = $payment->getDetails();

        $postFields = [
            'METHOD' => 'DoExpressCheckoutPayment',
            'TOKEN' => $details['token'],
            'PAYERID' => $details['payerid']
        ];

        $postFields += $this->getCommonPostFields($payment);

        return $this->doCall($postFields, $expectedResultFields);
    }

    private function doCall(array $postFields, array $expectedResultFields = [])
    {
        $headers = ['X-PAYPAL-APPLICATION-ID' => $this->parameters['appId']];

        $request = [];

        foreach ($postFields as $key => $value)
        {
            $request[] = "$key=" . urlencode($value);
        }

        $request = implode('&', $request);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->parameters['nvpApiUrl']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_VERBOSE, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);

        $response = curl_exec($curl);

        if (curl_errno($curl))
        {
            throw new PaymentProviderCallException(sprintf('Connection error %s: %s', curl_errno($curl), curl_error($curl)));
        }

        curl_close($curl);

        $result = $this->parseNvp($response);

        if (! isset($result['ack']))
        {
            throw new PaymentProviderCallException(sprintf('The response from PayPal is missing the required `%s` field.', 'ack'));
        }
        elseif (strpos($result['ack'], 'Success') !== 0)
        {
            $errorMessage = $result['ack']; // fallback

            if (isset($result['l_longmessage0']))
            {
                $errorMessage = $result['l_longmessage0'];
            }

            throw new PaymentProviderCallException('Error while calling PayPal: ' . $errorMessage);
        }
        foreach ($expectedResultFields as $field)
        {
            if (! isset($result[$field]))
            {
                throw new PaymentProviderCallException(sprintf('The response from PayPal is missing the required `%s` field.', $field));
            }
        }


        return $result;
    }

    private function parseNvp($nvpStr)
    {
        $nvpArray = [];

        while (strlen((string)$nvpStr))
        {
            $keypos = strpos($nvpStr, '=');
            $valuepos = strpos($nvpStr, '&') ? strpos($nvpStr, '&') : strlen((string)$nvpStr);

            $keyval = substr($nvpStr, 0, $keypos);
            $valval = substr($nvpStr, $keypos + 1, $valuepos - $keypos - 1);

            $nvpArray[strtolower(urldecode($keyval))] = urldecode($valval);
            $nvpStr = substr($nvpStr, $valuepos + 1, strlen($nvpStr));
        }

        return $nvpArray;
    }

    private function getCommonPostFields($payment)
    {
        $txnId = $payment->getId();

        $fields =
        [
            'VERSION' => '204',
            'USER' => $this->settings['agit.payment.paypal.api_username'],
            'PWD' => $this->settings['agit.payment.paypal.api_password'],
            'SIGNATURE' => $this->settings['agit.payment.paypal.api_signature'],

            'PAYMENTREQUEST_0_PAYMENTACTION' => 'SALE',
            'PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD' => 'InstantPaymentOnly',

            'PAYMENTREQUEST_0_AMT' => number_format($payment->getAmount(), 2, '.', ''),
            'PAYMENTREQUEST_0_CURRENCYCODE' => $payment->getCurrency()->getId(),
            'PAYMENTREQUEST_0_DESC' => sprintf(Translate::t('Payment ID %s'), $txnId),

            'NOSHIPPING' => '1',
            'ALLOWNOTE' => '0',
            'SOLUTIONTYPE' => 'Sole',
            'GIFTMESSAGEENABLE' => '0',
            'GIFTRECEIPTENABLE' => '0',
            'GIFTWRAPENABLE' => '0',

            'RETURNURL' => $this->orderUrlService->createProcessorUrl($payment, 'confirm'),
            'CANCELURL' => $this->orderUrlService->createCheckoutPageUrl($payment)
        ];

        return $fields;
    }
}
