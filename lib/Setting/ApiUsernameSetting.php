<?php
declare(strict_types=1);
/*
 * @package    agitation/paypal-bundle
 * @link       http://github.com/agitation/paypal-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\PaypalBundle\Setting;

use Agit\IntlBundle\Tool\Translate;

class ApiUsernameSetting extends AbstractPaypalSetting
{
    public function getId()
    {
        return 'tixys.payment.paypal.api_username';
    }

    public function getName()
    {
        return Translate::t('API username');
    }

    public function getDefaultValue()
    {
        return null;
    }

    public function validate($value)
    {
        $this->validationService->validate('string', $value, 10, 80);
    }
}
