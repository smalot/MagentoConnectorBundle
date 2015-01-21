<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary\ProductLabelDictionary;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\MappingException;
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
    /** @staticvar string */
    const SIMPLE_PRODUCT_TYPE = 'simple';

    /** @staticvar string */
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $productValues = array_merge_recursive(
            $this->getProductValues($object, $format, $context),
            $this->getCustomProductValues($object, $context)
        );

        foreach ($productValues as $storeView => &$values) {
            if (!isset($values[ProductLabelDictionary::STORE_HEADER])) {
                $values[ProductLabelDictionary::STORE_HEADER] = $storeView;
            }
        }
        $processedProduct = array_values($productValues);

        $categories = $this->getProductCategories($object, $format, $context);
        foreach ($categories[ProductLabelDictionary::CATEGORY_HEADER] as $key => $category) {
            $processedProduct[] = [
                ProductLabelDictionary::CATEGORY_HEADER      => $category,
                ProductLabelDictionary::CATEGORY_ROOT_HEADER =>
                    $categories[ProductLabelDictionary::CATEGORY_ROOT_HEADER][$key]
            ];
        }

        $associationParts = $this->getNormalizedAssociation($object, $context);

        return array_merge($processedProduct, $associationParts);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && 'api_import' === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        if (!$serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $this->normalizer = $serializer;
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
            ProductLabelDictionary::PRODUCT_TYPE_HEADER    => static::SIMPLE_PRODUCT_TYPE,
            ProductLabelDictionary::PRODUCT_WEBSITE_HEADER => $context['website'],
            ProductLabelDictionary::STATUS_HEADER          => (int) $product->isEnabled(),
            ProductLabelDictionary::VISIBILITY_HEADER      => (int) $context['visibility'],
            ProductLabelDictionary::ATTRIBUTE_SET_HEADER   => $product->getFamily()->getCode(),
            ProductLabelDictionary::CREATED_AT_HEADER      => $product->getCreated()->format(static::DATE_FORMAT),
            ProductLabelDictionary::UPDATED_AT_HEADER      => $product->getUpdated()->format(static::DATE_FORMAT)
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
            $values = array_merge_recursive($values, $this->normalizer->normalize($productValue, $format, $context));
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
    protected function getProductCategories(ProductInterface $product, $format, array $context)
    {
        $productCategories = [];
        foreach ($product->getCategories() as $category) {
            $normalized = $this->normalizer->normalize($category, $format, $context);

            if (!isset($context['userCategoryMapping'][$normalized['root']])) {
                throw new MappingException(
                    sprintf('Category root "%s" not corresponding with user category mapping', $normalized['root'])
                );
            }
            $productCategories[ProductLabelDictionary::CATEGORY_ROOT_HEADER][] =
                $context['userCategoryMapping'][$normalized['root']];
            $productCategories[ProductLabelDictionary::CATEGORY_HEADER][] = $normalized['category'];
        }

        return $productCategories;
    }

    /**
     * Get normalized product association parts
     *
     * @param ProductInterface $product
     * @param array            $context
     *
     * @return array
     */
    protected function getNormalizedAssociation(ProductInterface $product, array $context)
    {
        $associationParts = $this->normalizer->normalize($product->getAssociations(), 'api_import', $context);
        if (!is_array($associationParts)) {
            $associationParts = [];
        }

        $flattenedParts = [];
        foreach ($associationParts as $parts) {
            foreach ($parts as $part) {
                $flattenedParts[] = $part;
            }
        }

        return $flattenedParts;
    }
}
