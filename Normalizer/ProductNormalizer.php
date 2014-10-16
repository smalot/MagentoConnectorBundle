<?php
namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\scalar;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalize a product in ApiImport format
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    // Constants for default attributes header
    const HEADER_ATTRIBUTE_SET   = '_attribute_set';
    const HEADER_CATEGORY        = '_category';
    const HEADER_CATEGORY_ROOT   = '_root_category';
    const HEADER_CREATED_AT      = 'created_at';
    const HEADER_PRODUCT_TYPE    = '_type';
    const HEADER_PRODUCT_WEBSITE = '_product_websites';
    const HEADER_SKU             = 'sku';
    const HEADER_STATUS          = 'status';
    const HEADER_STORE           = '_store';
    const HEADER_TAX_CLASS_ID    = 'tax_class_id';
    const HEADER_UPDATED_AT      = 'updated_at';
    const HEADER_VISIBILITY      = 'visibility';

    // Constants for default value attributes
    const PRODUCT_TYPE_SIMPLE    = 'simple';

    // Constants for formats
    const DATE_FORMAT            = 'Y-m-d H:i:s';
    const API_IMPORT_FORMAT      = 'api_import';

    /** @var string[] $supportedFormats */
    protected $supportedFormats  = [self::API_IMPORT_FORMAT];

    /** @var SerializerInterface */
    protected $serializer;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $productValues = array_merge_recursive(
            $this->getProductValues($object, $format, $context),
            $this->getCustomProductValues($object, $context)
        );

        foreach ($productValues as $storeView => &$values) {
            if (!isset($values[static::HEADER_STORE])) {
                $values[static::HEADER_STORE] = $storeView;
            }
        }
        $processedProduct = array_values($productValues);

        return $processedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Get custom product values
     * Return [ 'default store view' => [ 'custom value header' => 'value', ... ] ]
     *
     * @param ProductInterface $product
     * @param array            $context
     *
     * @return array
     */
    protected function getCustomProductValues(ProductInterface $product, array $context)
    {
        $defaultStoreView = $context['defaultStoreView'];

        $customValues[$defaultStoreView] = [
            static::HEADER_PRODUCT_TYPE    => static::PRODUCT_TYPE_SIMPLE,
            static::HEADER_PRODUCT_WEBSITE => $context['website'],
            static::HEADER_STATUS          => (integer) $product->isEnabled(),
            static::HEADER_VISIBILITY      => (integer) $context['visibility'],
            static::HEADER_ATTRIBUTE_SET   => $product->getFamily()->getCode(),
            static::HEADER_CREATED_AT      => $product->getCreated()->format(static::DATE_FORMAT),
            static::HEADER_UPDATED_AT      => $product->getUpdated()->format(static::DATE_FORMAT),
        ];

        return $customValues;
    }

    /**
     * Get products values
     * Return [ 'store view 1' = [ 'code' => 'value', ...], 'store view 2' => [], ...]
     *
     * @param ProductInterface $product
     * @param string           $format
     * @param array            $context
     *
     * @return array
     */
    protected function getProductValues(ProductInterface $product, $format, array $context)
    {
        $values = [];
        foreach ($product->getValues() as $productValue) {
            $values = array_merge_recursive($values, $this->serializer->normalize($productValue, $format, $context));
        }

        return $values;
    }
}
