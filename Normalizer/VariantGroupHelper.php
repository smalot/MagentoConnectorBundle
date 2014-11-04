<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Pim\Bundle\MagentoConnectorBundle\Helper\PriceHelper;

/**
 * Variant group normalizer helper
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupHelper
{
    /** @staticvar string */
    const PRODUCT_TYPE_CONFIGURABLE     = 'configurable';

    /** @staticvar string */
    const HEADER_SUPER_PRODUCT_SKU      = '_super_products_sku';

    /** @staticvar string */
    const HEADER_SUPER_ATTRIBUTE_CODE   = '_super_attribute_code';

    /** @staticvar string */
    const HEADER_SUPER_ATTRIBUTE_OPTION = '_super_attribute_option';

    /** @staticvar string */
    const HEADER_SUPER_ATTRIBUTE_PRICE  = '_super_attribute_price_corr';

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var PriceHelper */
    protected $priceHelper;

    /**
     * Constructor
     *
     * @param PriceHelper $priceHelper
     */
    public function __construct(PriceHelper $priceHelper)
    {
        $this->priceHelper = $priceHelper;
    }

    /**
     * Normalizes an object into a set of arrays/scalars
     *
     * @param object $object  object to normalize
     * @param string $format  format the normalization result will be encoded as
     * @param array  $context Context options for the normalizer
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalized    = [];
        $channel       = $context['channel'];
        $variationAxes = $this->getVariantAxesCodes($object);
        $validProducts = $this->getValidProducts($object, $channel);

        if (!empty($validProducts)) {
            $priceChanges = $this->priceHelper->computePriceChanges(
                $object,
                $validProducts,
                $context['defaultLocale'],
                $context['defaultCurrency'],
                $channel->getCode()
            );

            $configurable = array_shift($validProducts);
            $normalized = $this->buildConfigurable($configurable, $format, $variationAxes, $context);

            foreach ($validProducts as $product) {
                $normalized = array_merge(
                    $normalized,
                    $this->buildAssociatedProduct(
                        $product,
                        $format,
                        $variationAxes,
                        $context
                    )
                );
            }
        }

        return $normalized;
    }

    /**
     * Sets the owning Serializer object
     *
     * @param SerializerInterface $serializer
     *
     * @throws \LogicException
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        if (!$serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $this->normalizer = $serializer;
    }

    /**
     * Return products from the variant group which are completes and in the good channel
     *
     * @param Group   $variantGroup
     * @param Channel $channel
     *
     * @return ProductInterface[]
     */
    protected function getValidProducts(Group $variantGroup, Channel $channel)
    {
        $validProducts  = [];
        $rootCategoryId = $channel->getCategory()->getId();

        foreach ($variantGroup->getProducts() as $product) {
            $isComplete = true;
            $completenesses = $product->getCompletenesses()->getIterator();
            while ((list($key, $completeness) = each($completenesses)) && $isComplete) {
                if ($completeness->getChannel()->getId() === $channel->getId() &&
                    $completeness->getRatio() < 100
                ) {
                    $isComplete = false;
                }
            }

            $productCategories = $product->getCategories()->getIterator();
            if ($isComplete && false !== $productCategories) {
                $isInChannel = false;
                while ((list($key, $category) = each($productCategories)) && !$isInChannel) {
                    if ($category->getRoot() === $rootCategoryId) {
                        $isInChannel = true;
                        $validProducts[] = $product;
                    }
                }
            }
        }

        return $validProducts;
    }

    /**
     * Get variant axes codes
     *
     * @param Group $group
     *
     * @return Collection
     */
    protected function getVariantAxesCodes(Group $group)
    {
        return $group->getAttributes()->map(
            function ($attribute) {
                return $attribute->getCode();
            }
        );
    }

    /**
     * Build a configurable product from a product
     *
     * @param ProductInterface $product
     * @param string           $format
     * @param array            $priceChanges
     * @param Collection       $variationAxes
     * @param array            $context
     *
     * @return array
     */
    protected function buildConfigurable(
        ProductInterface $product,
        $format,
        Collection $variationAxes,
        array $context = []
    ) {
        $simpleProduct = $this->normalizer->normalize($product, $format, $context);

        $configurable = array_merge(
            $this->getConfigurableValues(
                $simpleProduct,
                $variationAxes
            ),
            $this->buildAssociatedProduct(
                $product,
                $format,
                $variationAxes,
                $context
            )
        );

        return $configurable;
    }

    /**
     * Transform a simple product in configurable
     *
     * @param array      $simpleProduct
     * @param Collection $variationAxes
     *
     * @throws TypeNotFoundException
     *
     * @return array
     */
    protected function getConfigurableValues(array $simpleProduct, Collection $variationAxes)
    {
        $simpleProductRows = $simpleProduct;
        $isTypeUpdated = false;

        foreach ($simpleProductRows as &$row) {
            if (isset($row[ProductNormalizer::HEADER_PRODUCT_TYPE])) {
                $row[ProductNormalizer::HEADER_PRODUCT_TYPE] = static::PRODUCT_TYPE_CONFIGURABLE;
                $isTypeUpdated = true;
            }
            foreach ($variationAxes as $axis) {
                if (isset($row[$axis])) {
                    unset($row[$axis]);
                }
            }
        }

        if (!$isTypeUpdated) {
            throw new TypeNotFoundException(
                sprintf(
                    'Simple product to transform : %s' . PHP_EOL .
                    'Can\'t transform simple product to configurable. ' .
                    'The field "_type" is not found in the simple product ' .
                    'and can not be switch to "configurable" from "simple".',
                    json_encode($simpleProduct)
                )
            );
        }

        return $simpleProductRows;
    }

    /**
     * Build associated product
     *
     * @param ProductInterface $product
     * @param string           $format
     * @param Collection       $variationAxes
     * @param array            $context
     *
     * @return array
     */
    protected function buildAssociatedProduct(
        ProductInterface $product,
        $format,
        Collection $variationAxes,
        array $context = []
    ) {
        $associated = [];
        foreach ($variationAxes as $axisCode) {
            foreach ($product->getAttributes() as $attribute) {
                if ($attribute->getCode() === $axisCode) {
                    $option = $product->getValue($axisCode)->getOption();
                    $associated[] = [
                        static::HEADER_SUPER_PRODUCT_SKU      => (string) $product->getIdentifier(),
                        static::HEADER_SUPER_ATTRIBUTE_CODE   => $axisCode,
                        static::HEADER_SUPER_ATTRIBUTE_OPTION => $this->normalizer->normalize(
                            $option,
                            $format,
                            $context
                        ),
                         static::HEADER_SUPER_ATTRIBUTE_PRICE  => 0
                    ];
                }
            }
        }

        return $associated;
    }
}
