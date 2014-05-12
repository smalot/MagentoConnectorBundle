<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\Media;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\AssociationTypeManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\CategoryNotFoundException;
use Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection;

/**
 * A normalizer to transform a product entity into an array
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer extends AbstractNormalizer implements ProductNormalizerInterface
{
    const VISIBILITY = 'visibility';
    const ENABLED    = 'status';

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
    protected $currencyCode;

    /**
     * @var MediaManager
     */
    protected $mediaManager;

    /**
     * @var AssociationTypeManager
     */
    protected $associationTypeManager;

    /**
     * @var ProductValueNormalizer
     */
    protected $productValueNormalizer;

    /**
     * Constructor
     * @param ChannelManager         $channelManager
     * @param MediaManager           $mediaManager
     * @param ProductValueNormalizer $productValueNormalizer
     * @param CategoryMappingManager $categoryMappingManager
     * @param AssociationTypeManager $associationTypeManager
     * @param bool                   $enabled
     * @param bool                   $visibility
     * @param string                 $currencyCode
     * @param string                 $magentoUrl
     */
    public function __construct(
        ChannelManager $channelManager,
        MediaManager $mediaManager,
        ProductValueNormalizer $productValueNormalizer,
        CategoryMappingManager $categoryMappingManager,
        AssociationTypeManager $associationTypeManager,
        $enabled,
        $visibility,
        $currencyCode,
        $magentoUrl
    ) {
        parent::__construct($channelManager);

        $this->mediaManager           = $mediaManager;
        $this->productValueNormalizer = $productValueNormalizer;
        $this->categoryMappingManager = $categoryMappingManager;
        $this->associationTypeManager = $associationTypeManager;
        $this->enabled                = $enabled;
        $this->visibility             = $visibility;
        $this->currencyCode           = $currencyCode;
        $this->magentoUrl             = $magentoUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $processedItem = array();

        $processedItem[$context['defaultStoreView']] = $this->getDefaultProduct(
            $object,
            $context['magentoAttributes'],
            $context['magentoAttributesOptions'],
            $context['attributeSetId'],
            $context['defaultLocale'],
            $context['channel'],
            $context['website'],
            $context['categoryMapping'],
            $context['attributeMapping'],
            $context['pimGrouped'],
            $context['create'],
            $context['defaultStoreView']
        );

        $images = $this->getNormalizedImages($object);

        if (count($images) > 0) {
            $processedItem[Webservice::IMAGES] = $images;
        }

        //For each storeview, we update the product only with localized attributes
        foreach ($this->getPimLocales($context['channel']) as $locale) {
            $storeView = $this->getStoreViewForLocale(
                $locale->getCode(),
                $context['magentoStoreViews'],
                $context['storeViewMapping']
            );

            //If a locale for this storeview exist in PIM, we create a translated product in this locale
            if ($storeView && $storeView['code'] !== $context['defaultStoreView']) {
                $values = $this->getValues(
                    $object,
                    $context['magentoAttributes'],
                    $context['magentoAttributesOptions'],
                    $locale,
                    $context['channel'],
                    $context['categoryMapping'],
                    $context['attributeMapping'],
                    true
                );

                $processedItem[$storeView['code']] = array(
                    (string) $object->getIdentifier(),
                    $values,
                    $storeView['code'],
                    'sku'
                );
            } else {
                if ($locale->getCode() !== $context['defaultLocale']) {
                    $this->localeNotFound($locale);
                }
            }
        }

        return $processedItem;
    }

    /**
     * Get all images of a product normalized
     *
     * @param ProductInterface $product
     * @param string           $sku
     *
     * @return array
     */
    public function getNormalizedImages(ProductInterface $product, $sku = '')
    {
        $imageValues = $product->getValues()->filter(
            function ($value) {
                return $value->getData() instanceof Media;
            }
        );

        if ($sku === '') {
            $sku = $product->getIdentifier();
        }

        $images = array();

        foreach ($imageValues as $imageValue) {
            $data = $imageValue->getData();

            if ($imageData = $this->mediaManager->getBase64($data)) {
                $images[] = array(
                    (string) $sku,
                    array(
                        'file' => array(
                            'name'    => $data->getFilename(),
                            'content' => $imageData,
                            'mime'    => $data->getMimeType()
                        ),
                        'label'    => $data->getFilename(),
                        'position' => 0,
                        'types'    => array(Webservice::SMALL_IMAGE, Webservice::BASE_IMAGE, Webservice::THUMBNAIL),
                        'exclude'  => 0
                    ),
                    0,
                    'sku'
                );
            }
        }

        return $images;
    }

    /**
     * Get the default product with all attributes (ie : event the non localizables ones)
     *
     * @param ProductInterface  $product                  The given product
     * @param array             $magentoAttributes        Attribute list from Magento
     * @param array             $magentoAttributesOptions Attribute options list from Magento
     * @param integer           $attributeSetId           Attribute set id
     * @param string            $defaultLocale            Default locale
     * @param string            $channel                  Channel
     * @param string            $website                  Website name
     * @param MappingCollection $categoryMapping          Root category mapping
     * @param MappingCollection $attributeMapping         Attribute mapping
     * @param string            $pimGrouped               Pim grouped association code
     * @param bool              $create                   Is it a creation ?
     * @param array             $context                  Context
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
        MappingCollection $categoryMapping,
        MappingCollection $attributeMapping,
        $pimGrouped,
        $create,
        $defaultStoreValue
    ) {
        $sku           = (string) $product->getIdentifier();
        $defaultValues = $this->getValues(
            $product,
            $magentoAttributes,
            $magentoAttributesOptions,
            $defaultLocale,
            $channel,
            $categoryMapping,
            $attributeMapping,
            false
        );

        $defaultValues['websites'] = array($website);

        if ($create) {
            if ($this->hasGroupedProduct($product, $pimGrouped)) {
                $productType = self::MAGENTO_GROUPED_PRODUCT_KEY;
            } else {
                $productType = self::MAGENTO_SIMPLE_PRODUCT_KEY;
            }

            //For the default storeview we create an entire product
            $defaultProduct = array(
                $productType,
                $attributeSetId,
                $sku,
                $defaultValues,
                $defaultStoreValue
            );
        } else {
            $defaultProduct = array(
                $sku,
                $defaultValues,
                $defaultStoreValue,
                'sku'
            );
        }

        return $defaultProduct;
    }

    /**
     * Test if a product has grouped products
     * @param ProductInterface $product
     * @param string           $pimGrouped
     *
     * @return boolean
     */
    protected function hasGroupedProduct(ProductInterface $product, $pimGrouped)
    {
        if ($associationType = $this->associationTypeManager->getAssociationTypeByCode($pimGrouped)) {
            
            return (bool) $product->getAssociationForType($associationType);
        } else {
            return false;
        }
    }

    /**
     * Get values array for a given product
     *
     * @param ProductInterface  $product                  The given product
     * @param array             $magentoAttributes        Attribute list from Magento
     * @param array             $magentoAttributesOptions Attribute options list from Magento
     * @param string            $localeCode               The locale to apply
     * @param string            $scopeCode                The akeno scope
     * @param MappingCollection $categoryMapping          Root category mapping
     * @param MappingCollection $attributeMapping         Attribute mapping
     * @param boolean           $onlyLocalized            If true, only get translatable attributes
     *
     * @return array Computed data
     */
    public function getValues(
        ProductInterface $product,
        $magentoAttributes,
        $magentoAttributesOptions,
        $localeCode,
        $scopeCode,
        MappingCollection $categoryMapping,
        MappingCollection $attributeMapping,
        $onlyLocalized
    ) {
        $normalizedValues = array();

        $context = array(
            'identifier'               => $product->getIdentifier(),
            'scopeCode'                => $scopeCode,
            'localeCode'               => $localeCode,
            'onlyLocalized'            => $onlyLocalized,
            'magentoAttributes'        => $magentoAttributes,
            'magentoAttributesOptions' => $magentoAttributesOptions,
            'attributeMapping'         => $attributeMapping,
            'currencyCode'             => $this->currencyCode
        );

        foreach ($product->getValues() as $value) {
            $normalizedValue = $this->productValueNormalizer->normalize($value, 'MagentoArray', $context);
            if ($normalizedValue !== null) {
                $normalizedValues = array_merge(
                    $normalizedValues,
                    $normalizedValue
                );
            }
        }

        $normalizedValues = array_merge(
            $normalizedValues,
            $this->getCustomValue(
                $product,
                $attributeMapping,
                array('categoryMapping' => $categoryMapping)
            )
        );

        ksort($normalizedValues);

        return $normalizedValues;
    }

    /**
     * Get categories for the given product
     * @param ProductInterface  $product
     * @param MappingCollection $categoryMapping
     *
     * @return array
     */
    protected function getProductCategories(ProductInterface $product, MappingCollection $categoryMapping)
    {
        $productCategories = array();

        foreach ($product->getCategories() as $category) {
            $magentoCategoryId = $this->categoryMappingManager->getIdFromCategory(
                $category,
                $this->magentoUrl,
                $categoryMapping
            );

            if (!$magentoCategoryId) {
                throw new CategoryNotFoundException(
                    sprintf(
                        'The category %s was not found. Please export categories first or add it to the root ' .
                        'category mapping',
                        $category->getLabel()
                    )
                );
            }

            $productCategories[] = $magentoCategoryId;
        }

        return $productCategories;
    }

    /**
     * Get custom values (not provided by the PIM product)
     * @param ProductInterface  $product
     * @param MappingCollection $attributeMapping
     * @param array             $parameters
     *
     * @return mixed
     */
    protected function getCustomValue(
        ProductInterface $product,
        MappingCollection $attributeMapping,
        array $parameters = array()
    ) {
        return array(
            strtolower($attributeMapping->getTarget(self::VISIBILITY)) => $this->visibility,
            strtolower($attributeMapping->getTarget(self::ENABLED))    => (string) ($this->enabled) ? 1 : 2,
            strtolower($attributeMapping->getTarget('created_at'))     => $product->getCreated()
                ->format(AbstractNormalizer::DATE_FORMAT),
            strtolower($attributeMapping->getTarget('updated_at'))     => $product->getUpdated()
                ->format(AbstractNormalizer::DATE_FORMAT),
            strtolower($attributeMapping->getTarget('categories'))     => $this->getProductCategories(
                $product,
                $parameters['categoryMapping']
            )
        );
    }
}
