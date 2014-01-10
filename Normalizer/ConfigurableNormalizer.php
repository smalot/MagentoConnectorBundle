<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

/**
 * A normalizer to transform a group entity into an array
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurableNormalizer extends AbstractNormalizer
{
    const PRICE_CHANGES   = 'price_changes';
    const ASSOCIATED_SKUS = 'associated_skus';

    /**
     * @var ProductNormalizer
     */
    protected $productNormalizer;

    /**
     * Constructor
     * @param ChannelManager    $channelManager
     * @param ProductNormalizer $productNormalizer
     */
    public function __construct(
        ChannelManager $channelManager,
        ProductNormalizer $productNormalizer
    ) {
        parent::__construct($channelManager);

        $this->productNormalizer = $productNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $group    = $object['group'];
        $products = $object['products'];

        $sku = sprintf(MagentoWebservice::CONFIGURABLE_IDENTIFIER_PATTERN, $group->getCode());

        $processedItem[MagentoWebservice::SOAP_DEFAULT_STORE_VIEW] = $this->getDefaultConfigurable(
            $group,
            $sku,
            $context['attributeSetId'],
            $products,
            $context['magentoAttributes'],
            $context['magentoAttributesOptions'],
            $context['defaultLocale'],
            $context['currency'],
            $context['website'],
            $context['channel'],
            $context['create']
        );

        $processedItem[MagentoWebservice::IMAGES] = $this->productNormalizer->getNormalizedImages($products[0]);

        //For each storeview, we update the group only with localized attributes
        foreach ($this->getPimLocales($context['channel']) as $locale) {
            $storeViewCode = $this->getStoreViewCodeForLocale(
                $locale->getCode(),
                $context['magentoStoreViews'],
                $context['storeViewMapping']
            );

            //If a locale for this storeview exist in PIM, we create a translated group in this locale
            if ($storeViewCode) {
                $values = $this->productNormalizer->getValues(
                    $products[0],
                    $context['magentoAttributes'],
                    $context['magentoAttributesOptions'],
                    $locale->getCode(),
                    $context['channel'],
                    true
                );

                $processedItem[$storeViewCode] = array(
                    $sku,
                    $values,
                    $storeViewCode
                );
            } else {
                if ($locale->getCode() !== $context['defaultLocale']) {
                    $this->localeNotFound($locale, $context['storeViewMapping']);
                }
            }
        }

        return $processedItem;
    }

    /**
     * Get default configurable
     * @param  Group  $group
     * @param  string $sku
     * @param  int    $attributeSetId
     * @param  array  $products
     * @param  array  $magentoAttributes
     * @param  array  $magentoAttributesOptions
     * @param  string $locale
     * @param  string $currency
     * @param  string $website
     * @param  string $channel
     * @param  bool   $create
     * @return array
     */
    protected function getDefaultConfigurable(
        Group $group,
        $sku,
        $attributeSetId,
        $products,
        $magentoAttributes,
        $magentoAttributesOptions,
        $locale,
        $currency,
        $website,
        $channel,
        $create
    ) {
        $priceChanges   = $this->getPriceMapping($group, $products, $locale, $currency);
        $associatedSkus = $this->getProductsSkus($products);

        $defaultProduct = $products[0];

        $defaultProductValues = $this->productNormalizer->getValues(
            $defaultProduct,
            $magentoAttributes,
            $magentoAttributesOptions,
            $locale,
            $channel,
            false
        );

        $defaultConfigurableValues = array_merge(
            $defaultProductValues,
            array(
                self::PRICE_CHANGES   => $priceChanges,
                self::ASSOCIATED_SKUS => $associatedSkus
            )
        );

        $defaultConfigurableValues['websites'] = array($website);

        if ($create) {
            $defaultConfigurable = $this->getNewConfigurable($defaultConfigurableValues, $sku, $attributeSetId);
        } else {
            $defaultConfigurable = $this->getUpdatedConfigurable($defaultConfigurableValues, $sku);
        }

        return $defaultConfigurable;
    }

    /**
     * Get the configurable for a new call
     * @param  array $configurableValues
     * @param  string $sku
     * @param  int $attributeSetId
     * @return array
     */
    protected function getNewConfigurable($configurableValues, $sku, $attributeSetId)
    {
        return array(
            AbstractNormalizer::MAGENTO_CONFIGURABLE_PRODUCT_KEY,
            $attributeSetId,
            $sku,
            $configurableValues
        );
    }

    /**
     * Get the configurable for an update call
     * @param  array $configurableValues
     * @param  string $sku
     * @return array
     */
    protected function getUpdatedConfigurable($configurableValues, $sku)
    {
        return array(
            $sku,
            $configurableValues
        );
    }

    /**
     * Get price mapping for the given group and products
     * @param  Group  $group
     * @param  array  $products
     * @param  string $locale
     * @param  string $currency
     * @return array
     */
    protected function getPriceMapping(Group $group, $products, $locale, $currency)
    {
        $attributes = $group->getAttributes();

        $lowerPrice = $this->getLowerPrice($products, $locale, $currency);

        $priceMapping = array();

        foreach ($attributes as $attribute) {
            $attributeMapping = $this->getAttributeMapping($attribute, $lowerPrice, $products, $locale, $currency);

            $priceMapping[$attribute->getCode()] = $attributeMapping;
        }

        return $priceMapping;
    }

    /**
     * Get the lower price of given products
     * @param  array  $products
     * @param  string $locale
     * @param  string $currency
     * @return
     */
    protected function getLowerPrice($products, $locale, $currency)
    {
        $lowerPrice = $this->getProductPrice($products[0], $locale, $currency);

        foreach ($products as $product) {
            $productPrice = $this->getProductPrice($product, $locale, $currency);

            $lowerPrice = ($productPrice < $lowerPrice) ? $productPrice : $lowerPrice;
        }

        return $lowerPrice;
    }

    /**
     * Get the price of the given product
     * @param  Product $product
     * @param  string  $locale
     * @param  string  $currency
     * @return int
     */
    protected function getProductPrice(Product $product, $locale, $currency)
    {
        return $product->getValue('price', $locale)->getPrice($currency)->getData();
    }

    /**
     * Get price mapping for an attribute
     * @param  ProductAttribute $attribute
     * @param  int              $basePrice
     * @param  array            $products
     * @param  string           $locale
     * @param  string           $currency
     * @return array
     */
    protected function getAttributeMapping($attribute, $basePrice, $products, $locale, $currency)
    {
        $attributeMapping = array();

        foreach ($attribute->getOptions() as $option) {
            $productsWithOption = $this->getProductsWithOption($products, $option, $locale);

            if (count($productsWithOption) > 0) {
                $lowerPrice = $this->getLowerPrice($productsWithOption, $locale, $currency);
                $attributeMapping[$option->getCode()] = $lowerPrice- $basePrice;
            }
        }

        return $attributeMapping;
    }

    /**
     * Get all products with the given option value
     * @param  array           $products
     * @param  AttributeOption $option
     * @param  string          $locale
     * @return array
     */
    protected function getProductsWithOption($products, $option, $locale)
    {
        $productsWithOption = array();
        $attributeCode      = $option->getAttribute()->getCode();

        foreach ($products as $product) {
            if ($product->getValue($attributeCode, $locale) !== null &&
                $product->getValue($attributeCode, $locale)->getData()->getCode() === $option->getCode()
            ) {
                $productsWithOption[] = $product;
            }
        }

        return $productsWithOption;
    }

    /**
     * Get all products skus
     * @param  array $products
     * @return array
     */
    protected function getProductsSkus($products)
    {
        array_walk(
            $products,
            function (&$value, $key) {
                $value = (string) $value->getIdentifier();
            }
        );

        return $products;
    }
}
