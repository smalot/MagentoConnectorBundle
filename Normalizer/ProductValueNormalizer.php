<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Model\AbstractProductMedia;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Product value normalizer
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface, SerializerAwareInterface
{
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
        } elseif (AbstractAttributeType::BACKEND_TYPE_DECIMAL === $attribute->getBackendType()) {
            $value = $this->normalizeDecimal($data, $format, $context);
        } elseif (null !== $data) {
            if (is_bool($data)) {
                $value = intval($data);
            } else {
                $value = $this->serializer->normalize($data, $format, $context);
            }
        }

        if ($data instanceof AbstractProductMedia) {
            $normalized[] = $value;
        } else {
            $normalized = $this->localizeValue($locale, $attributeCode, $value, $context);
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
        return $data instanceof ProductValueInterface && ProductNormalizer::API_IMPORT_FORMAT === $format;
    }

    /**
     * Localize a value
     *
     * @param       $locale
     * @param       $attributeCode
     * @param       $value
     * @param array $context
     *
     * @return array
     */
    protected function localizeValue($locale, $attributeCode, $value, array $context)
    {
        if (null === $locale || $context['defaultLocale'] === $locale) {
            if (is_array($value)) {
                $localized = $this->normalizeLocalizedValue('', $value, $attributeCode);
            } else {
                $localized = $this->normalizeLocalizedValue($context['defaultStoreView'], $value, $attributeCode);
            }
        } else {
            $localized = $this->normalizeLocalizedValue($context['storeViewMapping'][$locale], $value, $attributeCode);
        }

        return $localized;
    }

    /**
     * Normalize a localized value
     *
     * @param $store
     * @param $value
     * @param $attributeCode
     *
     * @return array
     */
    protected function normalizeLocalizedValue($store, $value, $attributeCode)
    {
        $normalized = [];
        if (is_array($value)) {
            foreach ($value as $option) {
                $normalized[] = [
                    ProductNormalizer::HEADER_STORE => $store,
                    $attributeCode                  => $option
                ];
            }
        } else {
            $normalized[$store][$attributeCode] = $value;
        }

        return $normalized;
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
