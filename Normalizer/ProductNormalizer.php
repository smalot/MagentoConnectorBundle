<?php
namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
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

        $categories = $this->getProductCategories($object, $format, $context);
        foreach ($categories[static::HEADER_CATEGORY] as $key => $category) {
            $processedProduct[] = [
                static::HEADER_CATEGORY      => $category,
                static::HEADER_CATEGORY_ROOT => $categories[static::HEADER_CATEGORY_ROOT][$key]
            ];
        }

        return $processedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && static::API_IMPORT_FORMAT === $format;
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
            static::HEADER_STATUS          => (int) $product->isEnabled(),
            static::HEADER_VISIBILITY      => (int) $context['visibility'],
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

    /**
     * Get normalized categories for the given product
     * Return
     * [
     *   '_root_category' => ['rootOfCategory_1_path', 'rootOfCategory_2_path', ...],
     *   '_category' => [ 'category_1_path', 'category_2_path', ...]
     * ]
     *
     * @param ProductInterface $product
     * @param string           $format
     * @param array            $context
     *
     * @throws MappingException
     *
     * @return array
     */
    protected function getProductCategories(ProductInterface $product, $format, $context)
    {
        $productCategories = [];
        foreach ($product->getCategories() as $category) {
            $normalized = $this->serializer->normalize($category, $format, $context);

            if (!isset($context['userCategoryMapping'][$normalized['root']])) {
                throw new MappingException(
                    sprintf('Category root "%s" not corresponding with user category mapping', $normalized['root'])
                );
            }
            $productCategories[static::HEADER_CATEGORY_ROOT][] = $context['userCategoryMapping'][$normalized['root']];
            $productCategories[static::HEADER_CATEGORY][] = $normalized['category'];
        }

        return $productCategories;
    }
}
