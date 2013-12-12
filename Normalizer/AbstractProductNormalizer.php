<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\FlexibleEntityBundle\Entity\Metric;
use Pim\Bundle\CatalogBundle\Model\Media;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\ProductValue;

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

    const STORE_SCOPE = 'store';

    protected $enabled;
    protected $visibility;
    protected $magentoAttributesOptions;
    protected $magentoAttributes;

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
        $attributeSetId,
        $defaultLocale,
        $channel,
        $website,
        $create
    ) {
        $processedItem = array();

        $processedItem[MagentoSoapClient::SOAP_DEFAULT_STORE_VIEW] = $this->getDefaultProduct(
            $product,
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
                $values = $this->getValues($product, $locale, $channel, true);

                $processedItem[$storeViewCode] = array(
                    (string) $product->getIdentifier(),
                    $values,
                    $storeViewCode
                );
            }
        }

        print_r($processedItem);

        return $processedItem;
    }

    /**
     * Get the default product with all attributes (ie : event the non localizables ones)
     *
     * @param  Product $product           The given product
     * @param  integer $attributeSetId    Attribute set id
     * @param  string  $defaultLocale     Default locale
     * @param  string  $channel           Channel
     * @param  string  $website           Website name
     * @param  bool    $create            Is it a creation ?
     * @return array The default product data
     */
    protected function getDefaultProduct(
        Product $product,
        $attributeSetId,
        $defaultLocale,
        $channel,
        $website,
        $create
    ) {
        $sku                       = (string) $product->getIdentifier();
        $defaultValues             = $this->getValues($product, $defaultLocale, $channel, false);
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
    protected function getValues(Product $product, $localeCode, $scopeCode, $onlyLocalized = false)
    {
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
    protected function normalizeValue(ProductValue $value)
    {
        $data            = $value->getData();
        $attributeCode   = $value->getAttribute()->getCode();
        $valueNormalizer = $this->getValueNormalizers();

        $cpt = 0;
        $end = count($valueNormalizer);

        while ($cpt < $end && !$valueNormalizer[$cpt]['filter']($data)) {
            $cpt++;
        }

        $attributeScope = $this->magentoAttributes[$attributeCode]['scope'];

        if ($attributeScope == self::STORE_SCOPE &&
            $value->getAttribute()->isTranslatable() ||
            in_array($attributeCode, $this->getIgnoredScopeMatchingAttributes())
        ) {
            $normalizedValue = $valueNormalizer[$cpt]['normalizer']($data, array('attributeCode' => $attributeCode));
        } else {
            $message = 'The PIM attribute "%s" is %s, however his corresponding Magento attribute has the %s ' .
                'scope. To export the "%s" attribute, you must set his scope to %s in Magento.';

            if ($value->getAttribute()->isTranslatable()) {
                throw new InvalidScopeMatchException(sprintf(
                    $message,
                    $attributeCode,
                    'translatable',
                    'global',
                    $attributeCode,
                    'webview'
                ));
            } else {
                throw new InvalidScopeMatchException(sprintf(
                    $message,
                    $attributeCode,
                    'not translatable',
                    'webview',
                    $attributeCode,
                    'global'
                ));
            }
        }

        return array($attributeCode => $normalizedValue);
    }

    protected function getValueNormalizers()
    {
        return array(
            array(
                'filter'     => function($data) { return is_bool($data); },
                'normalizer' => function($data, $parameters) { return ($data) ? 1 : 0; }
            ),
            array(
                'filter'     => function($data) { return $data instanceof \DateTime; },
                'normalizer' => function($data, $parameters) { return $data->format(\DateTime::ATOM); }
            ),
            array(
                'filter'     => function($data) {
                    return $data instanceof \Pim\Bundle\CatalogBundle\Entity\AttributeOption;
                },
                'normalizer' => function($data, $parameters) {
                    return $this->getOptionId($parameters['attributeCode'], $data->getCode());
                }
            ),
            array(
                'filter'     => function($data) { return $data instanceof \Doctrine\Common\Collections\Collection; },
                'normalizer' => function($data, $parameters) {
                    return $this->normalizeCollectionData($data, $parameters['attributeCode']);
                }
            ),
            array(
                'filter'     => function($data) { return $data instanceof Media; },
                'normalizer' => function($data, $parameters) { return $this->mediaManager->getExportPath($data); }
            ),
            array(
                'filter'     => function($data) { return $data instanceof Metric; },
                'normalizer' => function($data, $parameters) { return $data->getData(); }
            ),
            array(
                'filter'     => function($data) { return true; },
                'normalizer' => function($data, $parameters) { return $data; }
            )
        );
    }

    protected function getIgnoredScopeMatchingAttributes()
    {
        return array();
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
}