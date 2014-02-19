<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Manager\LocaleManager as BaseLocaleManager;

/**
 * Custom locale manager
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleManager extends BaseLocaleManager
{
    /**
     * Get locale choices
     * Allow to list locales in an array like array[<code>] = <code>
     *
     * @return string[]
     */
    public function getLocaleChoices()
    {
        $codes = $this->getActiveCodes();

        $choices = array();
        foreach ($codes as $code) {
            $choices[$code] = $code;
        }

        return $choices;
    }
}
