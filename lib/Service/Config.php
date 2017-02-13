<?php

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
        "agit.payment.paypal.active",
        "agit.payment.paypal.environment",
        "agit.payment.paypal.api_username",
        "agit.payment.paypal.api_password",
        "agit.payment.paypal.api_signature"
    ];

    private $settings;

    public function __construct($parameters, SettingService $settingService)
    {
        $this->settings = $settingService->getValuesOf($this->settingNames);
        $environment = $this->settings["agit.payment.paypal.environment"];
        $this->active = $this->settings["agit.payment.paypal.active"];
        $this->parameters = $parameters["environment"][$environment];
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
