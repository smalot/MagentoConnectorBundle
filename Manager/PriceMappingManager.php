<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

/**
 * Price mapping manager
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceMappingManager
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $currency;

    /**
     * Constructor
     * @param string $locale
     * @param string $currency
     */
    public function __construct($locale, $currency)
    {
        $this->locale   = $locale;
        $this->currency = $currency;
    }

    /**
     * Get price mapping for the given group and products
     * @param  Group $group
     * @param  array $products
     * @return array
     */
    public function getPriceMapping(Group $group, $products)
    {
        $attributes = $group->getAttributes();
        $lowerPrice = $this->getLowerPrice($products);

        $priceMapping = array();

        foreach ($attributes as $attribute) {
            $attributeMapping = $this->getAttributeMapping($attribute, $lowerPrice, $products);

            $priceMapping[$attribute->getCode()] = $attributeMapping;
        }

        return $priceMapping;
    }

    /**
     * Get the lower price of given products
     * @param  array $products
     * @return int
     */
    protected function getLowerPrice($products)
    {
        $lowerPrice = $this->getProductPrice($products[0]);

        foreach ($products as $product) {
            $productPrice = $this->getProductPrice($product);

            $lowerPrice = ($productPrice < $lowerPrice) ? $productPrice : $lowerPrice;
        }

        return $lowerPrice;
    }

    /**
     * Get the price of the given product
     * @param  Product $product
     * @return int
     */
    protected function getProductPrice(Product $product)
    {
        return $product->getValue('price', $this->locale)->getPrice($this->currency)->getData();
    }

    /**
     * Get price mapping for an attribute
     * @param  ProductAttribute $attribute
     * @param  int              $basePrice
     * @param  array            $products
     * @return array
     */
    protected function getAttributeMapping($attribute, $basePrice, $products)
    {
        $attributeMapping = array();

        foreach ($attribute->getOptions() as $option) {
            $productsWithOption = $this->getProductsWithOption($products, $option);

            if (count($productsWithOption) > 0) {
                $lowerPrice = $this->getLowerPrice($productsWithOption);
                $attributeMapping[$option->getCode()] = $lowerPrice - $basePrice;
            }
        }

        return $attributeMapping;
    }

    /**
     * Get all products with the given option value
     * @param  array           $products
     * @param  AttributeOption $option
     * @return array
     */
    protected function getProductsWithOption($products, $option)
    {
        $productsWithOption = array();
        $attributeCode      = $option->getAttribute()->getCode();

        foreach ($products as $product) {
            if ($product->getValue($attributeCode, $this->locale) !== null &&
                $product->getValue($attributeCode, $this->locale)->getData()->getCode() === $option->getCode()
            ) {
                $productsWithOption[] = $product;
            }
        }

        return $productsWithOption;
    }
}
