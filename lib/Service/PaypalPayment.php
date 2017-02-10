<?php

/*
 * @package    agitation/paypal-bundle
 * @link       http://github.com/agitation/paypal-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\PaypalBundle\Service;

use Agit\OrderBundle\Entity\OrderInterface;
use Agit\OrderBundle\Entity\Payment;
use Agit\OrderBundle\Service\PaymentModuleInterface;
use Agit\ValidationBundle\ValidationService;
use Symfony\Component\HttpFoundation\Request;

class PaypalPayment implements PaymentModuleInterface
{
    private $validationService;

    private $paypalWorker;

    private $paypalApi;

    private $paypalConfig;

    public function __construct(ValidationService $validationService, PaypalWorker $paypalWorker, PaypalConfig $paypalConfig)
    {
        $this->validationService = $validationService;
        $this->paypalWorker = $paypalWorker;
        $this->paypalConfig = $paypalConfig;
    }

    public function getId()
    {
        return "paypal";
    }

    public function getName()
    {
        return "PayPal";
    }

    public function isActive()
    {
        return $this->paypalConfig->isActive();
    }

    public function getMethods()
    {
        return ["paypal" => "PayPal"];
    }

    public function getActiveMethods()
    {
        return $this->getMethods();
    }

    public function getFormConfig()
    {
        return [];
    }

    public function initPayment(OrderInterface $order, $method, array $parameters)
    {
        return $this->paypalWorker->initPayment($order, $method, $parameters);
    }

    public function getForwardPage(Payment $payment)
    {
        return $this->paypalWorker->getForwardPage($payment);
    }

    public function processPayment(Payment $payment, $action, Request $request)
    {
        return $this->paypalWorker->processPayment($payment, $action, $request);
    }
}
