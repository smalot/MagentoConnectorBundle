<?php
namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\MagentoConnectorBundle\Helper\MagentoAttributesHelper;
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
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /** @staticvar string */
    const API_IMPORT_FORMAT = 'api_import';

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var MagentoAttributesHelper */
    protected $attributesHelper;

    /**
     * Constructor
     *
     * @param MagentoAttributesHelper $attributesHelper
     */
    public function __construct(MagentoAttributesHelper $attributesHelper)
    {
        $this->attributesHelper = $attributesHelper;
    }

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
            if (!isset($values[$this->attributesHelper->getHeaderStore()])) {
                $values[$this->attributesHelper->getHeaderStore()] = $storeView;
            }
        }
        $processedProduct = array_values($productValues);

        $categories = $this->getProductCategories($object, $format, $context);
        foreach ($categories[$this->attributesHelper->getHeaderCategory()] as $key => $category) {
            $processedProduct[] = [
                $this->attributesHelper->getHeaderCategory()     => $category,
                $this->attributesHelper->getHeaderCategoryRoot() =>
                    $categories[$this->attributesHelper->getHeaderCategoryRoot()][$key]
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
            $this->attributesHelper->getHeaderProductType()    => $this->attributesHelper->getProductTypeSimple(),
            $this->attributesHelper->getHeaderProductWebsite() => $context['website'],
            $this->attributesHelper->getHeaderStatus()         => (int) $product->isEnabled(),
            $this->attributesHelper->getHeaderVisibility()     => (int) $context['visibility'],
            $this->attributesHelper->getHeaderAttributeSet()   => $product->getFamily()->getCode(),
            $this->attributesHelper->getHeaderCreatedAt()      => $product->getCreated()->format(static::DATE_FORMAT),
            $this->attributesHelper->getHeaderUpdatedAt()      => $product->getUpdated()->format(static::DATE_FORMAT),
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
            $productCategories[$this->attributesHelper->getHeaderCategoryRoot()][] =
                $context['userCategoryMapping'][$normalized['root']];
            $productCategories[$this->attributesHelper->getHeaderCategory()][] = $normalized['category'];
        }

        return $productCategories;
    }
}
