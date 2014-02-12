<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
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
     * @param Group $group
     * @param array $products
     *
     * @return array
     */
    public function getPriceMapping(Group $group, array $products, $lowest = true)
    {
        $attributes = $group->getAttributes();
        $basePrice  = $this->getLimitPrice($products, array(), $lowest);

        $sortedAttributes = $this->getSortedAttributes($attributes, $products, $basePrice, $lowest);

        $priceChanges = array();

        foreach ($sortedAttributes as $attribute) {
            $attributeMapping = $this->getAttributeMapping($attribute, $basePrice, $products, $priceChanges, $lowest);

            $priceChanges[$attribute->getCode()] = $attributeMapping;
        }

        if ($lowest) {
            try {
                $this->validatePriceMapping($products, $priceChanges, $basePrice);
            } catch (ComputedPriceNotMatchedException $e) {
                return $this->getPriceMapping($group, $products, false);
            }
        }

        return array('price_changes' => $priceChanges, 'base_price' => $basePrice);
    }

    /**
     * Get the limit price of given products
     * @param array $products
     * @param array $priceChanges
     *
     * @return int
     */
    public function getLimitPrice(array $products, array $priceChanges = array(), $lowest = true)
    {
        $limitPrice = $this->getProductPrice($products[0], $priceChanges, $lowest);

        foreach ($products as $product) {
            $productPrice = $this->getProductPrice($product, $priceChanges, $lowest);

            if ($lowest) {
                $limitPrice = ($productPrice < $limitPrice) ? $productPrice : $limitPrice;
            } else {
                $limitPrice = ($productPrice > $limitPrice) ? $productPrice : $limitPrice;
            }
        }

        return $limitPrice;
    }

    /**
     * Get sorted attributes for mapping
     * @param ArrayCollection $attributes
     * @param array           $products
     * @param float           $basePrice
     *
     * @return array
     */
    protected function getSortedAttributes($attributes, array $products, $basePrice, $lowest)
    {
        $attributeDelta = array();
        $attributeMap   = array();

        foreach ($attributes as $attribute) {
            $absoluteAttributeMapping = $this->getAttributeMapping($attribute, $basePrice, $products, array(), $lowest);

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
     * @param array            $priceChanges
     *
     * @return int
     */
    protected function getProductPrice(ProductInterface $product, array $priceChanges = array(), $lowest = true)
    {
        $toSubstract = 0;

        foreach ($priceChanges as $attributeCode => $attributeMapping) {
            foreach ($attributeMapping as $optionCode => $optionPrice) {
                if ($product->getValue($attributeCode, $this->locale) !== null &&
                    $product->getValue($attributeCode, $this->locale)->getData()->getCode() === $optionCode
                ) {
                    $toSubstract += $optionPrice;
                }
            }
        }

        $toSubstract = ($lowest * -1) * $toSubstract;

        return $product->getValue('price', $this->locale)->getPrice($this->currency)->getData() + $toSubstract;
    }

    /**
     * Get price mapping for an attribute
     * @param Attribute $attribute
     * @param int       $basePrice
     * @param array     $products
     * @param array     $priceChanges
     *
     * @return array
     */
    protected function getAttributeMapping(
        Attribute $attribute,
        $basePrice,
        array $products,
        array $priceChanges,
        $lowest
    ) {
        $attributeMapping = array();

        foreach ($attribute->getOptions() as $option) {
            $productsWithOption = $this->getProductsWithOption($products, $option);

            if (count($productsWithOption) > 0) {
                $priceDiff = $this->getLimitPrice($productsWithOption, $priceChanges, $lowest) - $basePrice;

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
    protected function getProductsWithOption(array $products, AttributeOption $option)
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
     * @param array $priceChanges
     * @param float $basePrice
     *
     * @return boolean
     */
    public function validatePriceMapping(array $products, array $priceChanges, $basePrice)
    {
        foreach ($products as $product) {
            $productPrice            = $this->getProductPrice($product);
            $productPriceFromMapping = $this->getProductPriceFromMapping($product, $priceChanges, $basePrice);

            if ($productPrice != $productPriceFromMapping) {
                throw new ComputedPriceNotMatchedException(
                    sprintf(
                        "Computed price mapping : %s. \n" .
                        "Base price : %s %s. \n" .
                        "Item causing the problem : %s. \n" .
                        "Actual product price : %s %s. \n" .
                        "Computed product price from mapping : %s %s.",
                        json_encode($priceChanges),
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
     * @param array            $priceChanges
     * @param float            $basePrice
     *
     * @return float
     */
    protected function getProductPriceFromMapping(ProductInterface $product, array $priceChanges, $basePrice)
    {
        $priceFromMapping = $basePrice;

        foreach ($priceChanges as $attributeCode => $attributeMapping) {
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
