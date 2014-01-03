<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

/**
 * A normalizer to transform a product entity into an array for Magento platform above 1.6
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer16 implements ProductNormalizer
{
    /**
     * Manage not found locales
     * @param  string $storeViewCode
     * @throws StoreviewNotMatchedException
     */
    protected function localeNotFound($storeViewCode, $magentoStoreViewMapping)
    {
        throw new StoreviewNotMatchedException(sprintf('No locale found for %s storeview code. Please map ' .
            'this storeview name to a PIM locale.', $storeViewCode));
    }
}
