<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Entity\Group;
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
        $group          = $object['group'];
        $products       = $object['products'];

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

        //For each storeview, we update the group only with localized attributes
        foreach ($this->getPimLocales($context['channel']) as $locale) {
            $storeViewCode = $this->getStoreViewCodeForLocale(
                $locale->getCode(),
                $context['magentoStoreViews'],
                $context['storeViewMapping']
            );

            //If a locale for this storeview exist in PIM, we create a translated group in this locale
            if ($storeViewCode) {
                $values = $this->getConfigurableValues(
                    $group,
                    $products,
                    $context['magentoAttributes'],
                    $context['magentoAttributesOptions'],
                    $locale->getCode(),
                    $context['currency'],
                    $context['website'],
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

    protected function getDefaultConfigurable(
        $group,
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
        $defaultConfigurableValues = $this->getConfigurableValues(
            $group,
            $products,
            $magentoAttributes,
            $magentoAttributesOptions,
            $locale,
            $currency,
            $channel,
            false
        );

        $defaultConfigurableValues['websites'] = array($website);

        if ($create) {
            $defaultConfigurable = $this->getNewConfigurable($defaultConfigurableValues, $sku, $attributeSetId);
        } else {
            $defaultConfigurable = $this->getUpdatedConfigurable($defaultConfigurableValues, $sku);
        }

        return $defaultConfigurable;
    }

    protected function getNewConfigurable($configurableValues, $sku, $attributeSetId)
    {
        return array(
            AbstractNormalizer::MAGENTO_CONFIGURABLE_PRODUCT_KEY,
            $attributeSetId,
            $sku,
            $configurableValues
        );
    }

    protected function getUpdatedConfigurable($configurableValues, $sku)
    {
        return array(
            $sku,
            $configurableValues
        );
    }

    protected function getConfigurableValues(
        $group,
        $products,
        $magentoAttributes,
        $magentoAttributesOptions,
        $locale,
        $currency,
        $channel,
        $onlyLocalisable
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
            $onlyLocalisable
        );

        return array_merge(
            $defaultProductValues,
            array(
                self::PRICE_CHANGES   => $priceChanges,
                self::ASSOCIATED_SKUS => $associatedSkus
            )
        );
    }

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

    protected function getLowerPrice($products, $locale, $currency)
    {
        $lowerPrice = $this->getProductPrice($products[0], $locale, $currency);

        foreach ($products as $product) {
            $productPrice = $this->getProductPrice($product, $locale, $currency);

            $lowerPrice = ($productPrice < $lowerPrice) ? $productPrice : $lowerPrice;
        }

        return $lowerPrice;
    }

    protected function getProductPrice($product, $locale, $currency)
    {
        return $product->getValue('price', $locale)->getPrice($currency)->getData();
    }

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
