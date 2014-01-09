<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\Metric;
use Pim\Bundle\CatalogBundle\Model\Media;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\AttributeNotFoundException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\InvalidOptionException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\InvalidScopeMatchException;

/**
 * A normalizer to transform a product entity into an array
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer extends AbstractNormalizer implements ProductNormalizerInterface
{
    const GLOBAL_SCOPE = 'global';
    const VISIBILITY   = 'visibility';
    const ENABLED      = 'status';

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * @var boolean
     */
    protected $visibility;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var MediaManager
     */
    protected $mediaManager;

    /**
     * Constructor
     * @param ChannelManager $channelManager
     * @param MediaManager   $mediaManager
     * @param bool           $enabled
     * @param bool           $visibility
     * @param string         $currency
     */
    public function __construct(
        ChannelManager $channelManager,
        MediaManager   $mediaManager,
        $enabled,
        $visibility,
        $currency
    ) {
        parent::__construct($channelManager);

        $this->mediaManager = $mediaManager;
        $this->enabled      = $enabled;
        $this->visibility   = $visibility;
        $this->currency     = $currency;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $this->getNormalizedProduct(
            $object,
            $context['magentoStoreViews'],
            $context['attributeSetId'],
            $context['defaultLocale'],
            $context['channel'],
            $context['website'],
            $context['storeViewMapping'],
            $context['magentoAttributes'],
            $context['magentoAttributesOptions'],
            $context['create']
        );
    }

    /**
     * Serialize the given product
     * @param  Product $product
     * @param  array   $magentoStoreViews List of storeviews (in magento platform)
     * @param  int     $attributeSetId
     * @param  string  $defaultLocale     Locale for the default storeview
     * @param  string  $channel
     * @param  string  $website           The website where to send data
     * @param  array   $storeViewMapping
     * @param  array   $magentoAttributes
     * @param  bool    $create            Is it a new product or an existing product
     * @return array The normalized product
     */
    protected function getNormalizedProduct(
        Product $product,
        $magentoStoreViews,
        $attributeSetId,
        $defaultLocale,
        $channel,
        $website,
        $storeViewMapping,
        $magentoAttributes,
        $create
    ) {
        $processedItem = array();

        $processedItem[MagentoWebservice::SOAP_DEFAULT_STORE_VIEW] = $this->getDefaultProduct(
            $product,
            $magentoAttributes,
            $magentoAttributesOptions,
            $attributeSetId,
            $defaultLocale,
            $channel,
            $website,
            $create
        );

        $processedItem[MagentoWebservice::IMAGES] = $this->getNormalizedImages($product);

        //For each storeview, we update the product only with localized attributes
        foreach ($this->getPimLocales($channel) as $locale) {
            $storeViewCode = $this->getStoreViewCodeForLocale(
                $locale->getCode(),
                $magentoStoreViews,
                $storeViewMapping
            );

            //If a locale for this storeview exist in PIM, we create a translated product in this locale
            if ($storeViewCode) {
                $values = $this->getValues(
                    $product,
                    $magentoAttributes,
                    $magentoAttributesOptions,
                    $locale,
                    $channel,
                    true
                );

                $processedItem[$storeViewCode] = array(
                    (string) $product->getIdentifier(),
                    $values,
                    $storeViewCode
                );
            } else {
                if ($locale->getCode() !== $defaultLocale) {
                    $this->localeNotFound($locale, $storeViewMapping);
                }
            }
        }

        return $processedItem;
    }

    /**
     * Get the default product with all attributes (ie : event the non localizables ones)
     *
     * @param  Product $product                 The given product
     * @param  array   $magentoAttributes
     * @param  array   $magentoAttributesOptions
     * @param  integer $attributeSetId           Attribute set id
     * @param  string  $defaultLocale            Default locale
     * @param  string  $channel                  Channel
     * @param  string  $website                  Website name
     * @param  bool    $create                   Is it a creation ?
     * @return array The default product data
     */
    protected function getDefaultProduct(
        Product $product,
        $magentoAttributes,
        $magentoAttributesOptions,
        $attributeSetId,
        $defaultLocale,
        $channel,
        $website,
        $create
    ) {
        $sku                       = (string) $product->getIdentifier();
        $defaultValues             = $this->getValues(
            $product,
            $magentoAttributes,
            $magentoAttributesOptions,
            $defaultLocale,
            $channel,
            false
        );
        $defaultValues['websites'] = array($website);

        if ($create) {
            //For the default storeview we create an entire product
            $defaultProduct = array(
                self::MAGENTO_SIMPLE_PRODUCT_KEY,
                $attributeSetId,
                $sku,
                $defaultValues,
                MagentoWebservice::SOAP_DEFAULT_STORE_VIEW
            );
        } else {
            $defaultProduct = array(
                $sku,
                $defaultValues,
                MagentoWebservice::SOAP_DEFAULT_STORE_VIEW
            );
        }

        return $defaultProduct;
    }

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
    ) {
        $identifier = $product->getIdentifier();

        $filteredValues = $product->getValues()->filter(
            function ($value) use ($identifier, $scopeCode, $localeCode, $onlyLocalized) {
                return $this->isValueNormalizable($value, $identifier, $scopeCode, $localeCode, $onlyLocalized);
            }
        );

        $normalizedValues = array();

        foreach ($filteredValues as $value) {
            $normalizedValues = array_merge(
                $normalizedValues,
                $this->getNormalizedValue($value, $magentoAttributes, $magentoAttributesOptions)
            );
        }

        $normalizedValues = array_merge(
            $normalizedValues,
            $this->getCustomValue()
        );

        ksort($normalizedValues);

        return $normalizedValues;
    }

    /**
     * Is the given value normalizable
     * @param  ProductValue $value
     * @param  string       $identifier
     * @param  string       $scopeCode
     * @param  string       $localeCode
     * @param  bool         $onlyLocalized
     * @return boolean
     */
    protected function isValueNormalizable($value, $identifier, $scopeCode, $localeCode, $onlyLocalized)
    {
        return (
            ($value !== $identifier) &&
            ($value->getData() !== null) &&
            (
                ($scopeCode == null) ||
                (!$value->getAttribute()->isScopable()) ||
                ($value->getAttribute()->isScopable() && $value->getScope() === $scopeCode)
            ) &&
            (
                ($localeCode == null) ||
                (!$value->getAttribute()->isTranslatable()) ||
                ($value->getAttribute()->isTranslatable() && $value->getLocale() === $localeCode)
            ) &&
            (
                (!$onlyLocalized && !$value->getAttribute()->isTranslatable()) ||
                $value->getAttribute()->isTranslatable()
            ) &&
            !in_array($value->getAttribute()->getCode(), $this->getIgnoredAttributes()) &&
            !($value->getData() instanceof Media)
        );
    }

    /**
     * Get the normalized value
     *
     * @param ProductValue $value
     * @param array        $magentoAttributes
     * @param array        $magentoAttributesOptions
     *
     * @return array
     */
    protected function getNormalizedValue(ProductValue $value, $magentoAttributes, $magentoAttributesOptions)
    {
        $data      = $value->getData();
        $attribute = $value->getAttribute();

        if (!isset($magentoAttributes[$attribute->getCode()])) {
            throw new AttributeNotFoundException(sprintf(
                'The magento attribute %s doesn\'t exist or isn\'t in the requested attributeSet. You should create ' .
                'it first or adding it to the corresponding attributeSet',
                $attribute->getCode()
            ));
        }

        $normalizer     = $this->getNormalizer($data);
        $attributeScope = $magentoAttributes[$attribute->getCode()]['scope'];

        $normalizedValue = $this->normalizeData(
            $data,
            $normalizer,
            $attribute,
            $attributeScope,
            $magentoAttributesOptions
        );

        return array($attribute->getCode() => $normalizedValue);
    }

    /**
     * Normalize the given data
     * @param  mixed     $data
     * @param  callable  $normalizer
     * @param  Attribute $attribute
     * @param  string    $attributeScope
     * @param  array     $magentoAttributesOptions
     * @return array
     */
    protected function normalizeData(
        $data,
        $normalizer,
        Attribute $attribute,
        $attributeScope,
        $magentoAttributesOptions
    ) {
        if (
            in_array($attribute->getCode(), $this->getIgnoredScopeMatchingAttributes()) ||
            (
                $attributeScope !== self::GLOBAL_SCOPE &&
                $attribute->isTranslatable()
            ) ||
            (
                $attributeScope === self::GLOBAL_SCOPE &&
                !$attribute->isTranslatable()
            )
        ) {
            $normalizedValue = $normalizer($data, array(
                'attributeCode'            => $attribute->getCode(),
                'magentoAttributesOptions' => $magentoAttributesOptions
            ));
        } else {
            throw new InvalidScopeMatchException(sprintf(
                'The scope for the PIM attribute "%s" is not matching the scope of his corresponding Magento ' .
                'attribute. To export the "%s" attribute, you must set the same scope in both Magento and the PIM.' .
                "\nMagento scope : %s\n" .
                "PIM scope : %s" ,
                $attribute->getCode(),
                $attribute->getCode(),
                $attributeScope,
                (($attribute->isTranslatable()) ? 'translatable' : 'not translatable')
            ));
        }

        return $normalizedValue;
    }

    /**
     * Get all value normalizer (filter and normlizer)
     *
     * @return array
     */
    protected function getValueNormalizers()
    {
        return array(
            array(
                'filter'     => function($data) { return is_bool($data); },
                'normalizer' => function($data, $parameters) { return ($data) ? 1 : 0; }
            ),
            array(
                'filter'     => function($data) { return $data instanceof \DateTime; },
                'normalizer' => function($data, $parameters) { return $data->format(self::DATE_FORMAT); }
            ),
            array(
                'filter'     => function($data) {
                    return $data instanceof \Pim\Bundle\CatalogBundle\Entity\AttributeOption;
                },
                'normalizer' => function($data, $parameters) {
                    if (in_array($parameters['attributeCode'], $this->getIgnoredOptionMatchingAttributes())) {
                        return $data->getCode();
                    }

                    return $this->getOptionId(
                        $parameters['attributeCode'],
                        $data->getCode(),
                        $parameters['magentoAttributesOptions']
                    );
                }
            ),
            array(
                'filter'     => function($data) { return $data instanceof \Doctrine\Common\Collections\Collection; },
                'normalizer' => function($data, $parameters) {
                    return $this->normalizeCollectionData(
                        $data,
                        $parameters['attributeCode'],
                        $parameters['magentoAttributesOptions']
                    );
                }
            ),
            array(
                'filter'     => function($data) { return $data instanceof Metric; },
                'normalizer' => function($data, $parameters) {
                    return (string) $data->getData();
                }
            ),
            array(
                'filter'     => function($data) { return true; },
                'normalizer' => function($data, $parameters) { return $data; }
            )
        );
    }

    /**
     * Get all ignored attribute
     *
     * @return array
     */
    protected function getIgnoredAttributes()
    {
        return array();
    }

    /**
     * Get all ignored attribute in scope matching test
     *
     * @return array
     */
    protected function getIgnoredScopeMatchingAttributes()
    {
        return array(
            'visibility'
        );
    }

    /**
     * Get all ignored attribute in option matching test
     *
     * @return array
     */
    protected function getIgnoredOptionMatchingAttributes()
    {
        return array();
    }

    /**
     * Get custom values (not provided by the PIM product)
     *
     * @return mixed
     */
    protected function getCustomValue()
    {
        return array(
            self::VISIBILITY   => $this->visibility,
            self::ENABLED      => $this->enabled,
            'created_at'       => (new \DateTime())->format(self::DATE_FORMAT),
            'updated_at'       => (new \DateTime())->format(self::DATE_FORMAT)
        );
    }

    /**
     * Get normalizer closure matching the corresponding filter with $data
     *
     * @param  mixed $data
     * @return closure
     */
    protected function getNormalizer($data)
    {
        $valueNormalizers = $this->getValueNormalizers();

        $cpt = 0;
        $end = count($valueNormalizers);

        while ($cpt < $end && !$valueNormalizers[$cpt]['filter']($data)) {
            $cpt++;
        }

        return $valueNormalizers[$cpt]['normalizer'];
    }

    /**
     * Normalize the value collection data
     *
     * @param array  $data
     * @param string $attributeCode
     * @param array  $magentoAttributesOptions
     *
     * @return string
     */
    protected function normalizeCollectionData($data, $attributeCode, $magentoAttributesOptions)
    {
        $result = array();
        foreach ($data as $item) {
            if ($item instanceof \Pim\Bundle\CatalogBundle\Entity\AttributeOption) {
                $optionCode = $item->getCode();

                $result[] = $this->getOptionId($attributeCode, $optionCode, $magentoAttributesOptions);
            } elseif ($item instanceof \Pim\Bundle\CatalogBundle\Model\ProductPrice) {
                if (
                    $item->getData() !== null &&
                    $item->getCurrency() === $this->currency
                ) {
                    return $item->getData();
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
     * @param  string $attributeCode            The product attribute code
     * @param  string $optionCode               The option label
     * @param  array  $magentoAttributesOptions
     * @return integer
     */
    protected function getOptionId($attributeCode, $optionCode, $magentoAttributesOptions)
    {
        $attributeCode = strtolower($attributeCode);

        if (!isset($magentoAttributesOptions[$attributeCode][$optionCode])) {
            throw new InvalidOptionException(sprintf('The attribute "%s" doesn\'t have any option named "%s" on ' .
                'Magento side. You should add this option in your "%s" attribute on Magento or export the PIM ' .
                'options using this Magento connector.', $attributeCode, $optionCode, $attributeCode));
        }

        return $magentoAttributesOptions[$attributeCode][$optionCode];
    }

    /**
     * Get all images of a product normalized
     *
     * @param  Product $product
     * @return array
     */
    protected function getNormalizedImages(Product $product)
    {
        $imagesValue = $product->getValues()->filter(
            function ($value) {
                return $value->getData() instanceof Media;
            }
        );

        $images = array();

        foreach ($imagesValue as $imageValue) {
            $data = $imageValue->getData();

            if ($imageData = $this->mediaManager->getBase64($data)) {
                $images[] = array(
                    (string) $product->getIdentifier(),
                    array(
                        'file' => array(
                            'name'    => $data->getFilename(),
                            'content' => $imageData,
                            'mime'    => $data->getMimeType()
                        ),
                        'label'    => $data->getFilename(),
                        'position' => 0,
                        'types'    => array(MagentoWebservice::SMALL_IMAGE),
                        'exclude'  => 0
                    )
                );
            }
        }

        return $images;
    }
}
