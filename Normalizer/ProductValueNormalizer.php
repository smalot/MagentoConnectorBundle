<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\scalar;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;

/**
 * Product value normalizer
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var string[] $supportedFormats */
    protected $supportedFormats = [ProductNormalizer::API_IMPORT_FORMAT];

    /** @var SerializerInterface */
    protected $serializer;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $locale        = $object->getLocale();
        $attribute     = $object->getAttribute();
        $attributeCode = $attribute->getCode();
        $data          = $object->getData();
        $value         = null;

        if (AbstractAttributeType::BACKEND_TYPE_PRICE === $attribute->getBackendType()) {
            $productPrice = $object->getPrice($context['defaultCurrency']);

            if (null !== $productPrice) {
                $value = $this->serializer->normalize($productPrice, $format, $context);
            }
        } elseif (AbstractAttributeType::BACKEND_TYPE_DECIMAL === $object->getAttribute()->getBackendType()) {
            $value = $this->normalizeDecimal($data, $format, $context);
        } elseif (null !== $data) {
            if (is_bool($data)) {
                $value = intval($data);
            } else {
                $value = $this->serializer->normalize($data, $format, $context);
            }
        }

        $normalized = [];
        if (null === $locale || $context['defaultLocale'] === $locale) {
            if (is_array($value)) {
                foreach ($value as $option) {
                    $normalized[] = [
                        ProductNormalizer::HEADER_STORE => '',
                        $attributeCode                  => $option
                    ];
                }
            } else {
                $normalized[$context['defaultStoreView']][$attributeCode] = $value;
            }
        } else {
            if (is_array($value)) {
                foreach ($value as $option) {
                    $normalized[] = [
                        ProductNormalizer::HEADER_STORE => $context['storeViewMapping'][$locale],
                        $attributeCode                  => $option
                    ];
                }
            } else {
                $normalized[$context['storeViewMapping'][$locale]][$attributeCode] = $value;
            }
        }

        return null !== $value ? $normalized : [];
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize a collection attribute value
     *
     * @param Collection $collection
     * @param string     $format
     * @param array      $context
     *
     * @return array|null
     */
    protected function normalizeCollection(Collection $collection, $format, $context)
    {
        $normalized = [];
        foreach ($collection as $item) {
            $normalized[] = $this->serializer->normalize($item, $format, $context);
        }

        return (count($normalized) > 0) ? $normalized : null;
    }

    /**
     * Normalize a decimal attribute value
     *
     * @param mixed  $data
     * @param string $format
     * @param array  $context
     *
     * @return mixed|null
     */
    protected function normalizeDecimal($data, $format, $context)
    {
        if (false === is_numeric($data)) {
            $normalized = $this->serializer->normalize($data, $format, $context);
        } else {
            $normalized = floatval($data);
        }

        return $normalized;
    }
}
