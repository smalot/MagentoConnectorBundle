<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\Product;

/**
 * Defines the interface of a product normalizers.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductNormalizerInterface
{
    /**
     * Get values array for a given product
     *
     * @param  Product $product                  The given product
     * @param  array   $magentoAttributes
     * @param  array   $magentoAttributesOptions
     * @param  string  $localeCode               The locale to apply
     * @param  string  $scopeCode                The akeno scope
     * @param  boolean $onlyLocalized            If true, only get translatable attributes
     * @return array Computed data
     */
    public function getValues(
        Product $product,
        $magentoAttributes,
        $magentoAttributesOptions,
        $localeCode,
        $scopeCode,
        $onlyLocalized = false
    );

    /**
     * Get all images of a product normalized
     *
     * @param  Product $product
     * @return array
     */
    public function getNormalizedImages(Product $product);
}
