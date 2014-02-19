<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Manager\CurrencyManager as BaseCurrencyManager;

/**
 * Custom currency manager
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyManager extends BaseCurrencyManager
{
    /**
     * Get currency choices
     * Allow to list currencys in an array like array[<code>] = <code>
     *
     * @return string[]
     */
    public function getCurrencyChoices()
    {
        $currencyCodes = $this->getActiveCodeChoices();

        $choices = array();
        foreach ($currencyCodes as $code) {
            $choices[$code] = $code;
        }

        return $choices;
    }
}
