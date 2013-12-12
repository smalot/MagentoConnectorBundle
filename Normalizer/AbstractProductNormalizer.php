<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\FlexibleEntityBundle\Entity\Metric;
use Pim\Bundle\CatalogBundle\Model\Media;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;

/**
 * A normalizer to transform a product entity into an array
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductNormalizer implements NormalizerInterface
{
    const MAGENTO_SIMPLE_PRODUCT_KEY = 'simple';
    const DEFAULT_LOCALE             = 'en_US';

    protected $enabled;
    protected $visibility;
    protected $magentoAttributesOptions;

    /**
     * @var array
     */
    protected $supportedFormats = array('json', 'xml');

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var MediaManager
     */
    protected $mediaManager;

    protected $pimLocales;

    public function __construct(ChannelManager $channelManager, MediaManager $mediaManager)
    {
        $this->channelManager = $channelManager;
        $this->mediaManager   = $mediaManager;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Serialize the given product
     *
     * @param  Product $product           The product
     * @param  array   $magentoStoreViews List of storeviews (in magento platform)
     * @return array The generated product
     */
    protected function getNormalizedProduct(
        Product $product,
        $magentoStoreViews,
        $magentoAttributes,
        $attributeSetId,
        $defaultLocale,
        $channel,
        $website,
        $create
    ) {
        $processedItem = array();

        $processedItem[MagentoSoapClient::SOAP_DEFAULT_STORE_VIEW] = $this->getDefaultProduct(
            $product,
            $magentoAttributes,
            $attributeSetId,
            $defaultLocale,
            $channel,
            $website,
            $create
        );

        //For each storeview, we update the product only with localized attributes
        foreach ($magentoStoreViews as $magentoStoreView) {
            $storeViewCode = $magentoStoreView['code'];
            $locale        = $this->getAkeneoLocaleForStoreView($storeViewCode, $channel);

            //If a locale for this storeview exist in akeneo, we create a translated product in this locale
            if ($locale) {
                $values = $this->getValues($product, $magentoAttributes, $locale, $channel, true);

                $processedItem[$storeViewCode] = array(
                    (string) $product->getIdentifier(),
                    $values,
                    $storeViewCode
                );
            }
        }

        return $processedItem;
    }

    /**
     * Get the default product with all attributes (ie : event the non localizables ones)
     *
     * @param  Product $product           The given product
     * @param  array   $magentoAttributes Magento attribute list
     * @param  integer $attributeSetId    Attribute set id
     * @param  string  $defaultLocale     Default locale
     * @param  string  $channel           Channel
     * @param  string  $website           Website name
     * @param  bool    $create            Is it a creation ?
     * @return array The default product data
     */
    protected function getDefaultProduct(
        Product $product,
        $magentoAttributes,
        $attributeSetId,
        $defaultLocale,
        $channel,
        $website,
        $create
    ) {
        $sku                       = (string) $product->getIdentifier();
        $defaultValues             = $this->getValues($product, $magentoAttributes, $defaultLocale, $channel, false);
        $defaultValues['websites'] = array($website);

        if ($create) {
            //For the default storeview we create an entire product
            $defaultProduct = array(
                self::MAGENTO_SIMPLE_PRODUCT_KEY,
                $attributeSetId,
                $sku,
                $defaultValues,
                MagentoSoapClient::SOAP_DEFAULT_STORE_VIEW
            );
        } else {
            $defaultProduct = array(
                $sku,
                $defaultValues,
                MagentoSoapClient::SOAP_DEFAULT_STORE_VIEW
            );
        }

        return $defaultProduct;
    }

    /**
     * Get the corresponding akeneo locale for a given storeview code
     *
     * @param  string $storeViewCode The store view code
     * @return Locale The corresponding locale
     */
    protected function getAkeneoLocaleForStoreView($storeViewCode, $channel)
    {
        $pimLocales = $this->getPimLocales($channel);
        foreach ($pimLocales as $locale) {
            if (strtolower($locale->getCode()) == $storeViewCode) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * Get all akeneo locales for the current channel
     * @return array The locales
     */
    protected function getPimLocales($channel)
    {
        if (!$this->pimLocales) {
            $this->pimLocales = $this->channelManager
                ->getChannels(array('code' => $channel))
                [0]
                ->getLocales();
        }

        return $this->pimLocales;
    }

    /**
     * Get values array for a given product
     *
     * @param  Product $product       The given product
     * @param  string  $localeCode    The locale to apply
     * @param  string  $scopeCode     The akeno scope
     * @param  boolean $onlyLocalized If true, only get translatable attributes
     *
     * @return array Computed data
     */
    protected function getValues(Product $product, $magentoAttributes, $localeCode, $scopeCode, $onlyLocalized = false)
    {
        // $values = array();

        $identifier = $product->getIdentifier();

        $filteredValues = $product->getValues()->filter(
            function ($value) use ($identifier, $scopeCode, $localeCode, $onlyLocalized) {
                return (
                    ($value !== $identifier) &&
                    (
                        ($scopeCode == null) ||
                        (!$value->getAttribute()->isScopable()) ||
                        ($value->getAttribute()->isScopable() && $value->getScope() == $scopeCode)
                    ) &&
                    (
                        ($localeCode == null) ||
                        (!$value->getAttribute()->isTranslatable()) ||
                        ($value->getAttribute()->isTranslatable() && $value->getLocale() == $localeCode)

                    ) &&
                    (
                        (!$onlyLocalized && !$value->getAttribute()->isTranslatable()) ||
                        $value->getAttribute()->isTranslatable()
                    )
                );
            }
        );

        $normalizedValues = array();
        foreach ($filteredValues as $value) {
            $normalizedValues = array_merge(
                $normalizedValues,
                $this->normalizeValue($value)
            );
        }
        ksort($normalizedValues);

        return $normalizedValues;
    }

    /**
     * Normalizes a value
     *
     * @param mixed $value
     *
     * @return array
     */
    protected function normalizeValue($value)
    {
        $data = $value->getData();

        $attributeCode = $value->getAttribute()->getCode();

        if (is_bool($data)) {
            $data = ($data) ? 1 : 0;
        } elseif ($data instanceof \DateTime) {
            $data = $data->format(\DateTime::ATOM);
        } elseif ($data instanceof \Pim\Bundle\CatalogBundle\Entity\AttributeOption) {
            print_r($data->getCode());
            $data = $this->getOptionId($attributeCode, $data->getCode());
        } elseif ($data instanceof \Doctrine\Common\Collections\Collection) {
            $data = $this->normalizeCollectionData($data, $attributeCode);
        } elseif ($data instanceof Media) {
            $data = $this->mediaManager->getExportPath($data);
        } elseif ($data instanceof Metric) {
            return array(
                $attributeCode                     => $data->getData(),
                sprintf('%s-unit', $attributeCode) => ($data->getData() !== null) ? $data->getUnit() : '',
            );
        }

        return array($attributeCode => $data);
    }

    /**
     * Normalize the value collection data
     *
     * @param array $data
     *
     * @return string
     */
    protected function normalizeCollectionData($data, $attributeCode)
    {
        $result = array();
        foreach ($data as $item) {
            if ($item instanceof \Pim\Bundle\CatalogBundle\Entity\AttributeOption) {
                $optionCode = $item->getCode();

                $result[] = $this->getOptionId($attributeCode, $optionCode);
            } elseif ($item instanceof \Pim\Bundle\CatalogBundle\Model\ProductPrice) {
                if ($item->getData() !== null) {
                    $result[] = $item->getData();
                }
            } else {
                $result[] = (string) $item;
            }
        }

        return $result;
    }
































    // /**
    //  * Get the value the given attribute for the given product
    //  * @param  array   $pimAttributes    Akeneo attribute list for the product
    //  * @param  array   $magentoAttribute Magento attribute list
    //  * @param  Product $product          The product
    //  * @param  string  $locale           The locale to apply
    //  * @param  string  $scope            The scope to apply
    //  * @param  boolean $onlyLocalized    If true on the attribute is not translatable get a null for the value
    //  * @return mixed The formated value
    //  */
    // protected function getPimValue(
    //     $pimAttributes,
    //     $magentoAttribute,
    //     Product $product,
    //     $locale,
    //     $scope,
    //     $onlyLocalized
    // ) {
    //     $attributesOptions    = $this->getAttributesOptions();
    //     $magentoAttributeCode = $magentoAttribute['code'];;

    //     $value = null;


    //     if (isset($attributesOptions[$magentoAttributeCode]) &&
    //         (
    //             !$onlyLocalized ||
    //             $onlyLocalized && $attributesOptions[$magentoAttributeCode]['translatable']
    //         )
    //     ) {
    //         $attributeOptions = $attributesOptions[$magentoAttributeCode];

    //         if (isset($attributeOptions['method'])) {
    //             $value = $this->getValueFromMethod($product, $attributeOptions, $locale, $scope);
    //         } else {
    //             $value = $this->getValueFromPimAttribute(
    //                 $product,
    //                 $attributeOptions,
    //                 $pimAttributes,
    //                 $magentoAttributeCode,
    //                 $locale,
    //                 $scope
    //             );
    //         }

    //         $value = $this->castValue($value, $attributeOptions);
    //     }

    //     return $value;
    // }

    // /**
    //  * Call the method from $attributeOptions and return the computed value
    //  *
    //  * @param  Product $product          The concerned product
    //  * @param  array   $attributeOptions Attribute options
    //  * @param  string  $locale           Locale code
    //  * @param  string  $scope            Scope code
    //  *
    //  * @return mixed The computed value
    //  */
    // protected function getValueFromMethod(Product $product, $attributeOptions, $locale, $scope)
    // {
    //     $parameters = isset($attributeOptions['parameters']) ? $attributeOptions['parameters'] : array();
    //     $parameters = array_merge(array(
    //         'locale' => $locale,
    //         'scope'  => $scope
    //     ), $parameters);

    //     $method = $attributeOptions['method'];

    //     if (is_callable($method)) {
    //         $value = $method($product, $parameters);
    //     } else {
    //         $value = call_user_func_array(array($product, $attributeOptions['method']), $parameters);
    //     }

    //     return $value;
    // }

    // /**
    //  * Getting the value from the akeneo attribute
    //  *
    //  * @param  Product $product              The concerned product
    //  * @param  array   $attributeOptions     Attribute options
    //  * @param  array   $pimAttributes        Pim attributes
    //  * @param  string  $magentoAttributeCode The magento attribute code
    //  * @param  string  $locale               The locale to apply
    //  * @param  string  $scope                The scope to apply
    //  *
    //  * @return mixed
    //  */
    // protected function getValueFromPimAttribute(
    //     Product $product,
    //     $attributeOptions,
    //     $pimAttributes,
    //     $magentoAttributeCode,
    //     $locale,
    //     $scope
    // ) {
    //     //If there is a mapping between magento and the pim
    //     if (isset($attributeOptions['mapping'])) {
    //         $pimAttribute  = $pimAttributes[$attributeOptions['mapping']];
    //     } else {
    //         $pimAttribute  = $pimAttributes[$magentoAttributeCode];
    //     }

    //     print_r($pimAttribute->getAttributeType());

    //     $attributeCode   = $pimAttribute->getCode();
    //     $attributeLocale = ($pimAttribute->isTranslatable()) ? $locale : null;
    //     $attributeScope  = ($pimAttribute->isScopable())     ? $scope  : null;

    //     return $product->getValue($attributeCode, $attributeLocale, $attributeScope);
    // }

    // /**
    //  * Cast the given value
    //  *
    //  * @param  mixed $value            Our value
    //  * @param  array $attributeOptions The attribute options
    //  *
    //  * @return mixed
    //  */
    // protected function castValue($value, $attributeOptions)
    // {
    //     if ($value !== null && isset($attributeOptions['type'])) {
    //         $castMethod = $this->getCastOptions()[$attributeOptions['type']];

    //         $value = $castMethod($value);
    //     }

    //     return $value;
    // }

    // /**
    //  * Get cast options
    //  *
    //  * @return array
    //  */
    // protected function getCastOptions()
    // {
    //     return array(
    //         'int' => function($value) {
    //             return (int) $value;
    //         },
    //         'string' => function($value) {
    //             return (string) $value;
    //         },
    //         'bool' => function($value) {
    //             return (int) $value;
    //         },
    //         'float' => function($value) {
    //             return (float) $value;
    //         },
    //         'date' => function($value) {
    //             return (string) $value->format(\DateTime::ATOM);
    //         },
    //         'array' => function($value) {
    //             return $value;
    //         },
    //         'id' => function($value) {
    //             return (int) $value;
    //         }
    //     );
    // }

    // /**
    //  * Getting the attributes options
    //  *
    //  * @return array
    //  */
    // protected function getAttributesOptions()
    // {
    //     return array(
    //         'name' => array(
    //             'translatable' => true,
    //             'type'         => 'string',
    //         ),
    //         'description' => array(
    //             'translatable' => true,
    //             'type'         => 'string',
    //             'mapping'      => 'long_description',
    //         ),
    //         'short_description' => array(
    //             'translatable' => true,
    //             'type'         => 'string',
    //         ),
    //         'status' => array(
    //             'translatable' => false,
    //             'type'         => 'bool',
    //             'method'       => function($product, $params) {
    //                 return $this->enabled;
    //             },
    //         ),
    //         'visibility' => array(
    //             'translatable' => false,
    //             'type'         => 'id',
    //             'method'       => function($product, $params) {
    //                 return $this->visibility;
    //             },
    //         ),
    //         'multiple_color' => array(
    //             'translatable' => false,
    //             'type'         => 'array',
    //             'method'       => function($product, $params) {
    //                 $colors = $product->getValue('color', self::DEFAULT_LOCALE)->getOptions();

    //                 return $this->getMatchedOptions($colors, 'multiple_color');
    //             }
    //         ),
    //         'size' => array(
    //             'translatable' => false,
    //             'type'         => 'id',
    //             'method'       => function($product, $params) {
    //                 $size = strtolower($product->getSize()->getOption()->getCode());

    //                 return $this->getOptionId('size', $size);
    //             }
    //         ),
    //         'price' => array(
    //             'translatable' => true,
    //             'type'         => 'float',
    //             'method'       => function($product, $params) {
    //                 return $product->getValue('price', (string) $params['locale'], (string) $params['scope'])
    //                     ->getPrices()
    //                     ->first()
    //                     ->getData();
    //             },
    //         ),
    //         'tax_class_id' => array(
    //             'translatable' => false,
    //             'type'         => 'id',
    //             'method'       => function ($product, $params) { return 0; }
    //         ),
    //     );
    // }

    /**
     * Get the id of the given magento option code
     *
     * @param  string $attributeCode The product attribute code
     * @param  string $optionCode    The option label
     * @return integer
     */
    protected function getOptionId($attributeCode, $optionCode)
    {
        $attributeCode = strtolower($attributeCode);
        $optionCode    = strtolower($optionCode);

        if (!isset($this->magentoAttributesOptions[$attributeCode][$optionCode])) {
            throw new InvalidOptionException('The attribute "' . $attributeCode . '" doesn\'t have any option named "' .
                $optionCode . '" on Magento side. You should add this option in your "' . $attributeCode .
                '" attribute on Magento or export the PIM options using this Magento connector.');
        }

        return $this->magentoAttributesOptions[$attributeCode][$optionCode];
    }

    // /**
    //  * Get an array of magento options ids for the given attribute
    //  *
    //  * @param  array  $options       List of PIM options for the given attribute
    //  * @param  string $attributeCode The magento attribute code
    //  * @return array
    //  */
    // protected function getMatchedOptions($options, $attributeCode)
    // {
    //     $matchedOptions = array();

    //     foreach ($options as $option) {
    //         $optionCode = strtolower($option->getCode());

    //         $matchedOptions[] = $this->getOptionId($attributeCode, $optionCode);
    //     }

    //     return $matchedOptions;
    // }
}