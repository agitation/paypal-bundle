<?php

/*
 * @package    agitation/paypal-bundle
 * @link       http://github.com/agitation/paypal-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\PaypalBundle\Setting;

use Agit\IntlBundle\Tool\Translate;

class ApiSignatureSetting extends AbstractPaypalSetting
{
    public function getId()
    {
        return "agit.payment.paypal.api_signature";
    }

    public function getName()
    {
        return Translate::t("API signature");
    }

    public function getDefaultValue()
    {
        return null;
    }

    public function validate($value)
    {
        $this->validationService->validate("string", $value, 30, 100);
    }
}
