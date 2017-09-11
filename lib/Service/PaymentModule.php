<?php
declare(strict_types=1);
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
use Symfony\Component\HttpFoundation\Request;

class PaymentModule implements PaymentModuleInterface
{
    private $worker;

    private $config;

    public function __construct(Worker $worker, Config $config)
    {
        $this->worker = $worker;
        $this->config = $config;
    }

    public function getId()
    {
        return 'pp';
    }

    public function getName()
    {
        return 'PayPal';
    }

    public function isActive()
    {
        return $this->config->isActive();
    }

    public function getMethods()
    {
        return ['pp' => 'PayPal'];
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
        return $this->worker->initPayment($order, $method, $parameters);
    }

    public function getForwardPage(Payment $payment)
    {
        return $this->worker->getForwardPage($payment);
    }

    public function processPayment(Payment $payment, $action, Request $request)
    {
        return $this->worker->processPayment($payment, $action, $request);
    }
}
