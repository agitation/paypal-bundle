<?php
declare(strict_types=1);
/*
 * @package    agitation/paypal-bundle
 * @link       http://github.com/agitation/paypal-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\PaypalBundle\Service;

use Agit\SettingBundle\Service\SettingService;

class Config
{
    private $active;

    private $parameters;

    private $settingNames = [
        'tixys.payment.paypal.active',
        'tixys.payment.paypal.environment',
        'tixys.payment.paypal.api_username',
        'tixys.payment.paypal.api_password',
        'tixys.payment.paypal.api_signature'
    ];

    private $settings;

    public function __construct($parameters, SettingService $settingService)
    {
        $this->settings = $settingService->getValuesOf($this->settingNames);
        $environment = $this->settings['tixys.payment.paypal.environment'];
        $this->active = $this->settings['tixys.payment.paypal.active'];
        $this->parameters = $parameters['environment'][$environment];
    }

    public function isActive()
    {
        return $this->active;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getSettings()
    {
        return $this->settings;
    }
}
