<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
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
     * @param Group $group
     * @param array $products
     *
     * @return array
     */
    public function getPriceMapping(Group $group, array $products)
    {
        $attributes = $group->getAttributes();
        $lowestPrice = $this->getLowestPrice($products);

        $sortedAttributes = $this->getSortedAttributes($attributes, $products, $lowestPrice);

        $priceMapping = array();

        foreach ($sortedAttributes as $attribute) {
            $attributeMapping = $this->getAttributeMapping($attribute, $lowestPrice, $products, $priceMapping);

            $priceMapping[$attribute->getCode()] = $attributeMapping;
        }

        return $priceMapping;
    }

    /**
     * Get the lowest price of given products
     * @param array $products
     * @param array $priceMapping
     *
     * @return int
     */
    public function getLowestPrice(array $products, array $priceMapping = array())
    {
        $lowestPrice = $this->getProductPrice($products[0], $priceMapping);

        foreach ($products as $product) {
            $productPrice = $this->getProductPrice($product, $priceMapping);

            $lowestPrice = ($productPrice < $lowestPrice) ? $productPrice : $lowestPrice;
        }

        return $lowestPrice;
    }

    /**
     * Get sorted attributes for mapping
     * @param ArrayCollection $attributes
     * @param array           $products
     * @param float           $basePrice
     *
     * @return array
     */
    protected function getSortedAttributes($attributes, array $products, $basePrice)
    {
        $attributeDelta = array();
        $attributeMap   = array();

        foreach ($attributes as $attribute) {
            $absoluteAttributeMapping = $this->getAttributeMapping($attribute, $basePrice, $products, array());

            $attributeDelta[$attribute->getCode()] = max($absoluteAttributeMapping);
            $attributeMap[$attribute->getCode()]   = $attribute;
        }

        asort($attributeDelta);

        array_walk($attributeDelta, function (&$value, $key) use ($attributeMap) {
            $value = $attributeMap[$key];
        });

        return $attributeDelta;
    }

    /**
     * Get the price of the given product
     * @param ProductInterface $product
     * @param array            $priceMapping
     *
     * @return int
     */
    protected function getProductPrice(ProductInterface $product, array $priceMapping = array())
    {
        $toSubstract = 0;

        foreach ($priceMapping as $attributeCode => $attributeMapping) {
            foreach ($attributeMapping as $optionCode => $optionPrice) {
                if ($product->getValue($attributeCode, $this->locale) !== null &&
                    $product->getValue($attributeCode, $this->locale)->getData()->getCode() === $optionCode
                ) {
                    $toSubstract += $optionPrice;
                }
            }
        }

        return $product->getValue('price', $this->locale)->getPrice($this->currency)->getData() - $toSubstract;
    }

    /**
     * Get price mapping for an attribute
     * @param AttributeInterface $attribute
     * @param int                $basePrice
     * @param array              $products
     * @param array              $priceMapping
     *
     * @return array
     */
    protected function getAttributeMapping(
        AttributeInterface $attribute,
        $basePrice,
        array $products,
        array $priceMapping
    ) {
        $attributeMapping = array();

        foreach ($attribute->getOptions() as $option) {
            $productsWithOption = $this->getProductsWithOption($products, $option);

            if (count($productsWithOption) > 0) {
                $priceDiff = $this->getLowestPrice($productsWithOption, $priceMapping) - $basePrice;

                $attributeMapping[$option->getCode()] = $priceDiff;
            }
        }

        return $attributeMapping;
    }

    /**
     * Get all products with the given option value
     * @param array           $products
     * @param AttributeOption $option
     *
     * @return array
     */
    protected function getProductsWithOption(array $products, $option)
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

    /**
     * Validate generated price mapping
     * @param array $products
     * @param array $priceMapping
     * @param float $basePrice
     *
     * @return boolean
     */
    public function validatePriceMapping(array $products, array $priceMapping, $basePrice)
    {
        foreach ($products as $product) {
            $productPrice            = $this->getProductPrice($product);
            $productPriceFromMapping = $this->getProductPriceFromMapping($product, $priceMapping, $basePrice);

            if ($productPrice != $productPriceFromMapping) {
                throw new ComputedPriceNotMatchedException(
                    sprintf(
                        "Computed price mapping : %s. \n" .
                        "Base price : %s %s. \n" .
                        "Item causing the problem : %s. \n" .
                        "Actual product price : %s %s. \n" .
                        "Computed product price from mapping : %s %s.",
                        json_encode($priceMapping),
                        $basePrice,
                        $this->currency,
                        $product->getIdentifier(),
                        $productPrice,
                        $this->currency,
                        $productPriceFromMapping,
                        $this->currency
                    )
                );
            }
        }
    }

    /**
     * Get product price from generated mapping
     * @param ProductInterface $product
     * @param array            $priceMapping
     * @param float            $basePrice
     *
     * @return float
     */
    protected function getProductPriceFromMapping(ProductInterface $product, array $priceMapping, $basePrice)
    {
        $priceFromMapping = $basePrice;

        foreach ($priceMapping as $attributeCode => $attributeMapping) {
            $priceFromMapping += $this->getAttributePriceFromMapping($product, $attributeCode, $attributeMapping);
        }

        return $priceFromMapping;
    }

    /**
     * Get the attribute price from generated mapping
     * @param ProductInterface $product
     * @param string           $attributeCode
     * @param array            $attributeMapping
     *
     * @return float
     */
    protected function getAttributePriceFromMapping(ProductInterface $product, $attributeCode, array $attributeMapping)
    {
        if ($product->getValue($attributeCode, $this->locale) !== null) {
            foreach ($attributeMapping as $optionCode => $optionPrice) {
                if ($product->getValue($attributeCode, $this->locale)->getData()->getCode() === $optionCode) {
                    return $optionPrice;
                }
            }
        }

        return 0;
    }
}
