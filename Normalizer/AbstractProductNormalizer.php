<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;

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

    /**
     * @var array
     */
    protected $supportedFormats = array('json', 'xml');
    /**
     * @var ChannelManager
     */
    protected $channelManager;

    protected $pimLocales;

    public function __construct(ChannelManager $channelManager)
    {
        $this->channelManager = $channelManager;
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
     * @param  string  $locale        The locale to apply
     * @param  string  $scope         The akeno scope
     * @param  boolean $onlyLocalized If true, only get translatable attributes
     *
     * @return array Computed data
     */
    protected function getValues(Product $product, $magentoAttributes, $locale, $scope, $onlyLocalized = false)
    {
        $values = array();

        $pimAttributes = $product->getAllAttributes();

        foreach ($magentoAttributes as $magentoAttribute) {
            if (($value = $this->getPimValue(
                $pimAttributes,
                $magentoAttribute,
                $product,
                $locale,
                $scope,
                $onlyLocalized
            )) !== null) {
                $values[$magentoAttribute['code']] = $value;
            }
        }

        return $values;
    }

    /**
     * Get the value the given attribute for the given product
     * @param  array  $pimAttributes Akeneo attribute list for the product
     * @param  array  $magentoAttribute Magento attribute list
     * @param  Product $product          The product
     * @param  string  $locale           The locale to apply
     * @param  string  $scope            The scope to apply
     * @param  boolean  $onlyLocalized   If true on the attribute is not translatable get a null for the value
     * @return mixed The formated value
     */
    protected function getPimValue(
        $pimAttributes,
        $magentoAttribute,
        Product $product,
        $locale,
        $scope,
        $onlyLocalized
    ) {
        $attributesOptions    = $this->getAttributesOptions();
        $magentoAttributeCode = $magentoAttribute['code'];;

        $value = null;

        if (isset($attributesOptions[$magentoAttributeCode]) &&
            (
                !$onlyLocalized ||
                $onlyLocalized && $attributesOptions[$magentoAttributeCode]['translatable']
            )
        ) {
            $attributeOptions = $attributesOptions[$magentoAttributeCode];

            if (isset($attributeOptions['method'])) {
                $value = $this->getValueFromMethod($product, $attributeOptions, $locale, $scope);
            } else {
                $value = $this->getValueFromPimAttribute(
                    $product,
                    $attributeOptions,
                    $pimAttributes,
                    $magentoAttributeCode,
                    $locale,
                    $scope
                );
            }

            $value = $this->castValue($value, $attributeOptions);
        }

        return $value;
    }

    /**
     * Call the method from $attributeOptions and return the computed value
     *
     * @param  Product $product          The concerned product
     * @param  array   $attributeOptions Attribute options
     * @param  string  $locale           Locale code
     * @param  string  $scope            Scope code
     *
     * @return mixed The computed value
     */
    protected function getValueFromMethod(Product $product, $attributeOptions, $locale, $scope)
    {
        $parameters = isset($attributeOptions['parameters']) ? $attributeOptions['parameters'] : array();
        $parameters = array_merge(array(
            'locale' => $locale,
            'scope'  => $scope
        ), $parameters);

        $method = $attributeOptions['method'];

        if (is_callable($method)) {
            $value = $method($product, $parameters);
        } else {
            $value = call_user_func_array(array($product, $attributeOptions['method']), $parameters);
        }

        return $value;
    }

    /**
     * Getting the value from the akeneo attribute
     *
     * @param  Product $product              The concerned product
     * @param  array   $attributeOptions     Attribute options
     * @param  array   $pimAttributes        Pim attributes
     * @param  string  $magentoAttributeCode The magento attribute code
     * @param  string  $locale               The locale to apply
     * @param  string  $scope                The scope to apply
     *
     * @return mixed
     */
    protected function getValueFromPimAttribute(
        Product $product,
        $attributeOptions,
        $pimAttributes,
        $magentoAttributeCode,
        $locale,
        $scope
    ) {
        //If there is a mapping between magento and the pim
        if (isset($attributeOptions['mapping'])) {
            $pimAttribute  = $pimAttributes[$attributeOptions['mapping']];
        } else {
            $pimAttribute  = $pimAttributes[$magentoAttributeCode];
        }

        $attributeCode   = $pimAttribute->getCode();
        $attributeLocale = ($pimAttribute->isTranslatable()) ? $locale : null;
        $attributeScope  = ($pimAttribute->isScopable())     ? $scope  : null;

        return $product->getValue($attributeCode, $attributeLocale, $attributeScope);
    }

    /**
     * Cast the given value
     *
     * @param  mixed $value            Our value
     * @param  array $attributeOptions The attribute options
     *
     * @return mixed
     */
    protected function castValue($value, $attributeOptions)
    {
        if ($value !== null && isset($attributeOptions['type'])) {
            $castMethod = $this->getCastOptions()[$attributeOptions['type']];

            $value = $castMethod($value);
        }

        return $value;
    }

    /**
     * Get cast options
     *
     * @return array
     */
    protected function getCastOptions()
    {
        return array(
            'int' => function($value) {
                return (int) $value;
            },
            'string' => function($value) {
                return (string) $value;
            },
            'bool' => function($value) {
                return (int) $value;
            },
            'float' => function($value) {
                return (float) $value;
            },
            'date' => function($value) {
                return (string) $value->format(\DateTime::ATOM);
            },
        );
    }

    /**
     * Getting the attributes options
     *
     * @return array
     */
    protected function getAttributesOptions()
    {
        return array(
            'name' => array(
                'translatable' => true,
                'type'         => 'string',
            ),
            'description' => array(
                'translatable' => true,
                'type'         => 'string',
                'mapping'      => 'long_description',
            ),
            'short_description' => array(
                'translatable' => true,
                'type'         => 'string',
            ),
            'status' => array(
                'translatable' => false,
                'type'         => 'bool',
                'method'       => function($product, $params) {
                    return $this->enabled;
                },
            ),
            'visibility' => array(
                'translatable' => false,
                'type'         => 'int',
                'method'       => function($product, $params) {
                    return $this->visibility;
                },
            ),
            'price' => array(
                'translatable' => true,
                'type'         => 'float',
                'method'       => function($product, $params) {
                    return $product->getValue('price', (string) $params['locale'], (string) $params['scope'])
                        ->getPrices()
                        ->first()
                        ->getData();
                },
            ),
            'tax_class_id' => array(
                'translatable' => false,
                'type'         => 'int',
                'method'       => function ($product, $params) { return 0; }
            ),
        );
    }
}