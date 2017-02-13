<?php

/*
 * @package    agitation/paypal-bundle
 * @link       http://github.com/agitation/paypal-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\PaypalBundle\Setting;

use Agit\IntlBundle\Tool\Translate;

class IsActiveSetting extends AbstractPaypalSetting
{
    public function getId()
    {
        return "agit.payment.paypal.active";
    }

    public function getName()
    {
        return Translate::t("Active");
    }

    public function getDefaultValue()
    {
        return false;
    }

    public function validate($value)
    {
        $this->validationService->validate("boolean", $value);
    }
}
