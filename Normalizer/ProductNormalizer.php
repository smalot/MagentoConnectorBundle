<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\Media;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice;

/**
 * A normalizer to transform a product entity into an array
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer extends AbstractNormalizer implements ProductNormalizerInterface
{
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
     * @var ProductValueNormalizer
     */
    protected $productValueNormalizer;

    /**
     * Constructor
     * @param ChannelManager         $channelManager
     * @param MediaManager           $mediaManager
     * @param ProductValueNormalizer $productValueNormalizer
     * @param bool                   $enabled
     * @param bool                   $visibility
     * @param string                 $currency
     */
    public function __construct(
        ChannelManager $channelManager,
        MediaManager $mediaManager,
        ProductValueNormalizer $productValueNormalizer,
        $enabled,
        $visibility,
        $currency
    ) {
        parent::__construct($channelManager);

        $this->mediaManager    = $mediaManager;
        $this->productValueNormalizer = $productValueNormalizer;
        $this->enabled         = $enabled;
        $this->visibility      = $visibility;
        $this->currency        = $currency;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $processedItem = array();

        $processedItem[MagentoWebservice::SOAP_DEFAULT_STORE_VIEW] = $this->getDefaultProduct(
            $object,
            $context['magentoAttributes'],
            $context['magentoAttributesOptions'],
            $context['attributeSetId'],
            $context['defaultLocale'],
            $context['channel'],
            $context['website'],
            $context['create']
        );

        $processedItem[MagentoWebservice::IMAGES] = $this->getNormalizedImages($object);

        //For each storeview, we update the product only with localized attributes
        foreach ($this->getPimLocales($context['channel']) as $locale) {
            $storeViewCode = $this->getStoreViewCodeForLocale(
                $locale->getCode(),
                $context['magentoStoreViews'],
                $context['storeViewMapping']
            );

            //If a locale for this storeview exist in PIM, we create a translated product in this locale
            if ($storeViewCode) {
                $values = $this->getValues(
                    $object,
                    $context['magentoAttributes'],
                    $context['magentoAttributesOptions'],
                    $locale,
                    $context['channel'],
                    true
                );

                $processedItem[$storeViewCode] = array(
                    (string) $object->getIdentifier(),
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
     * Get all images of a product normalized
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    public function getNormalizedImages(ProductInterface $product)
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

    /**
     * Get the default product with all attributes (ie : event the non localizables ones)
     *
     * @param ProductInterface $product                  The given product
     * @param array            $magentoAttributes        Attribute list from Magento
     * @param array            $magentoAttributesOptions Attribute options list from Magento
     * @param integer          $attributeSetId           Attribute set id
     * @param string           $defaultLocale            Default locale
     * @param string           $channel                  Channel
     * @param string           $website                  Website name
     * @param bool             $create                   Is it a creation ?
     *
     * @return array The default product data
     */
    protected function getDefaultProduct(
        ProductInterface $product,
        $magentoAttributes,
        $magentoAttributesOptions,
        $attributeSetId,
        $defaultLocale,
        $channel,
        $website,
        $create
    ) {
        $sku           = (string) $product->getIdentifier();
        $defaultValues = $this->getValues(
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
     * @param ProductInterface $product                  The given product
     * @param array            $magentoAttributes        Attribute list from Magento
     * @param array            $magentoAttributesOptions Attribute options list from Magento
     * @param string           $localeCode               The locale to apply
     * @param string           $scopeCode                The akeno scope
     * @param boolean          $onlyLocalized            If true, only get translatable attributes
     *
     * @return array Computed data
     */
    public function getValues(
        ProductInterface $product,
        $magentoAttributes,
        $magentoAttributesOptions,
        $localeCode,
        $scopeCode,
        $onlyLocalized = false
    ) {
        $normalizedValues = array();

        $context = array(
            'identifier'               => $product->getIdentifier(),
            'scopeCode'                => $scopeCode,
            'localeCode'               => $localeCode,
            'onlyLocalized'            => $onlyLocalized,
            'magentoAttributes'        => $magentoAttributes,
            'magentoAttributesOptions' => $magentoAttributesOptions,
            'currencyCode'             => $this->currency
        );

        foreach ($product->getValues() as $value) {
            if (($normalizedValue = $this->productValueNormalizer->normalize($value, 'MagentoArray', $context)) !== null) {
                $normalizedValues = array_merge(
                    $normalizedValues,
                    $normalizedValue
                );
            }

        }

        $normalizedValues = array_merge(
            $normalizedValues,
            $this->getCustomValue()
        );

        ksort($normalizedValues);

        return $normalizedValues;
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
            self::ENABLED      => (string) ($this->enabled) ? 1 : 2,
            'created_at'       => (new \DateTime())->format(self::DATE_FORMAT),
            'updated_at'       => (new \DateTime())->format(self::DATE_FORMAT)
        );
    }
}
