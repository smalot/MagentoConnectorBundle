<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

/**
 * A normalizer to transform a product entity into an array for Magento platform above 1.6
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer16 extends ProductNormalizer
{
    /**
     * Get the corresponding storeview code for a givent locale
     * @param  string $locale
     * @param  array  $magentoStoreViews
     * @param  array  $storeViewMapping
     * @return string
     */
    protected function getStoreViewCodeForLocale($locale, $magentoStoreViews, $storeViewMapping)
    {
        return $this->getMappedStoreView($locale, $storeViewMapping);
    }

    /**
     * Manage not found locales
     * @param  string $storeViewCode
     * @throws LocaleNotMatchedException
     */
    protected function localeNotFound($storeViewCode, $magentoStoreViewMapping)
    {
        throw new LocaleNotMatchedException(sprintf('No storeview found for the locale "%s". Please map the ' .
            'locale "%s" to a Magento storeview', $storeViewCode, $storeViewCode));
    }
}
