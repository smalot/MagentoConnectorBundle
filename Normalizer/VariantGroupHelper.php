<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\MagentoConnectorBundle\Helper\ValidProductHelper;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
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
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var PriceHelper */
    protected $priceHelper;

    /** @var ValidProductHelper */
    protected $validProductHelper;

    /**
     * Constructor
     *
     * @param PriceHelper        $priceHelper
     * @param ValidProductHelper $validProductHelper
     */
    public function __construct(PriceHelper $priceHelper, ValidProductHelper $validProductHelper)
    {
        $this->priceHelper        = $priceHelper;
        $this->validProductHelper = $validProductHelper;
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
        $validProducts = $this->validProductHelper->getValidProducts($channel, $object->getProducts());

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
     * @param NormalizerInterface $normalizer
     */
    public function setSerializer(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * Get variant axes codes
     *
     * @param Group $group
     *
     * @return array
     */
    protected function getVariantAxesCodes(Group $group)
    {
        $axes = $group->getAttributes()->toArray();

        return array_map(
            function ($attribute) {
                return $attribute->getCode();
            },
            $axes
        );
    }

    /**
     * Build a configurable product from a product
     *
     * @param ProductInterface $product
     * @param string           $format
     * @param array            $variationAxes
     * @param array            $context
     *
     * @return array
     */
    protected function buildConfigurable(
        ProductInterface $product,
        $format,
        array $variationAxes,
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
     * @param array $simpleProduct
     * @param array $variationAxes
     *
     * @throws TypeNotFoundException
     *
     * @return array
     */
    protected function getConfigurableValues(array $simpleProduct, array $variationAxes)
    {
        $simpleProductRows = $simpleProduct;
        $isTypeUpdated = false;

        foreach ($simpleProductRows as &$row) {
            if (isset($row[LabelDictionary::PRODUCT_TYPE_HEADER])) {
                $row[LabelDictionary::PRODUCT_TYPE_HEADER] = LabelDictionary::CONFIGURABLE_PRODUCT_TYPE;
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
     * @param array            $variationAxes
     * @param array            $context
     *
     * @return array
     */
    protected function buildAssociatedProduct(
        ProductInterface $product,
        $format,
        array $variationAxes,
        array $context = []
    ) {
        $associated = [];
        foreach ($variationAxes as $axisCode) {
            foreach ($product->getAttributes() as $attribute) {
                if ($attribute->getCode() === $axisCode) {
                    $option = $product->getValue($axisCode)->getOption();
                    $associated[] = [
                        LabelDictionary::SUPER_PRODUCT_SKU_HEADER      => (string) $product->getIdentifier(),
                        LabelDictionary::SUPER_ATTRIBUTE_CODE_HEADER   => $axisCode,
                        LabelDictionary::SUPER_ATTRIBUTE_OPTION_HEADER => $this->normalizer->normalize(
                            $option,
                            $format,
                            $context
                        ),
                        LabelDictionary::SUPER_ATTRIBUTE_PRICE_HEADER  => 0
                    ];
                }
            }
        }

        return $associated;
    }
}
