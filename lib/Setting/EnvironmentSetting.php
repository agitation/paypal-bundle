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

class EnvironmentSetting extends AbstractPaypalSetting
{
    public function getId()
    {
        return 'agit.payment.paypal.environment';
    }

    public function getName()
    {
        return Translate::t('Environment');
    }

    public function getDefaultValue()
    {
        return 'test';
    }

    public function validate($value)
    {
        $this->validationService->validate('selection', $value, ['test', 'live']);
    }
}
