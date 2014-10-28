<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Variant group normalizer
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupNormalizer implements NormalizerInterface, SerializerAwareInterface
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

    /** @var bool */
    protected $isConfigurableBuilt = false;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var string[] */
    protected $variationAxes;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalized = [];
        $channel = $context['channel'];
        $this->variationAxes = $this->getVariationAxesCode($object);
        $rootCategoryId = $channel->getCategory()->getId();

        foreach ($object->getProducts() as $product) {
            $isComplete = true;
            foreach ($product->getCompletenesses() as $completeness) {
                if ($completeness->getChannel()->getId() === $channel->getId() && $completeness->getRatio() < 100) {
                    $isComplete = false;
                    break; // Can't use a while() with a Persistent collection
                }
            }

            if (true === $isComplete && $product->getCategories()->first()->getRoot() === $rootCategoryId) {
                if (!$this->isConfigurableBuilt) {
                    $normalized = array_merge($normalized, $this->buildConfigurable($product, $format, $context));
                } else {
                    $normalized = array_merge($normalized, $this->buildAssociatedProduct($product, $format, $context));
                }
            }
        }

        return $normalized;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Group &&
            $data->getType()->isVariant() &&
            ProductNormalizer::API_IMPORT_FORMAT === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        if ($serializer instanceof NormalizerInterface) {
            $this->normalizer = $serializer;
        } else {
            throw new \LogicException('Serializer must be a normalizer');
        }
    }

    /**
     * Get variation axes code
     *
     * @return string[]
     */
    protected function getVariationAxesCode(Group $group)
    {
        return array_map(
            function ($attribute) {
                return $attribute->getCode();
            },
            $group->getAttributes()->toArray()
        );
    }

    /**
     * Build a configurable product from a product
     *
     * @param ProductInterface $product
     * @param string           $format
     * @param array            $context
     *
     * @return array
     */
    protected function buildConfigurable(ProductInterface $product, $format, array $context = [])
    {
        $simpleProduct = $this->normalizer->normalize($product, $format, $context);

        $configurable = array_merge(
            $this->transformSimpleProductInConfigurable($simpleProduct),
            $this->buildAssociatedProduct($product, $format, $context)
        );

        $this->isConfigurableBuilt = true;

        return $configurable;
    }

    /**
     * Transform a simple product in configurable
     *
     * @param array $simpleProductRows
     *
     * @throws TypeNotFoundException
     *
     * @return array
     */
    protected function transformSimpleProductInConfigurable(array $simpleProductRows)
    {
        $isTypeUpdated = false;
        while ((list($key, $row) = each($simpleProductRows)) && !$isTypeUpdated) {
            if (isset($row[ProductNormalizer::HEADER_PRODUCT_TYPE])) {
                $simpleProductRows[$key][ProductNormalizer::HEADER_PRODUCT_TYPE] = static::PRODUCT_TYPE_CONFIGURABLE;
                $isTypeUpdated = true;
            }
        }

        if (!$isTypeUpdated) {
            throw new TypeNotFoundException(
                sprintf('Cant transform simple product to configurable because the simple product type is not found.')
            );
        }

        return $simpleProductRows;
    }

    /**
     * Build associated product
     *
     * @param ProductInterface $product
     * @param string           $format
     * @param array            $context
     *
     * @throws WrongAttributeTypeException
     *
     * @return array
     */
    protected function buildAssociatedProduct(ProductInterface $product, $format, array $context = [])
    {
        $associated = [];
        foreach ($this->variationAxes as $axisCode) {
            foreach ($product->getAttributes() as $attribute) {
                if ($attribute->getCode() === $axisCode) {
                    if ('pim_catalog_simpleselect' === $attribute->getAttributeType()) {
                        $associated[] = [
                            static::HEADER_SUPER_PRODUCT_SKU      => (string) $product->getIdentifier(),
                            static::HEADER_SUPER_ATTRIBUTE_CODE   => $axisCode,
                            static::HEADER_SUPER_ATTRIBUTE_OPTION => $this->normalizer->normalize(
                                $product->getValue($axisCode)->getOption(),
                                $format,
                                $context
                            ),
                            // static::HEADER_SUPER_ATTRIBUTE_PRICE  => ;
                        ];
                    } else {
                        throw new WrongAttributeTypeException(
                            sprintf(
                                'Variation axis "%s" variant group can\'t be normalized.' .
                                ' Only a simple select can be a variation axis in Magento. This attribute is a "%s"',
                                $axisCode,
                                $attribute->getAttributeType()
                            )
                        );
                    }
                }
            }
        }

        return $associated;
    }
}
