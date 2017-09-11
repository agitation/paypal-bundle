<?php
declare(strict_types=1);
/*
 * @package    agitation/paypal-bundle
 * @link       http://github.com/agitation/paypal-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\PaypalBundle\Setting;

use Agit\SettingBundle\Service\AbstractSetting;
use Agit\ValidationBundle\ValidationService;

abstract class AbstractPaypalSetting extends AbstractSetting
{
    protected $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }
}
