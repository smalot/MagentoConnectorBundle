<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\MagentoConnectorBundle\Helper\MagentoAttributesHelper;
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
        $locale        = $object->getLocale();
        $attribute     = $object->getAttribute();
        $attributeCode = $attribute->getCode();
        $data          = $object->getData();
        $value         = null;

        switch (gettype($data)) {
            case 'object':
                $value = $this->normalizer->normalize($data, $format, $context);
                break;

            case 'string':
                $value = $this->getStringValue($object, $attribute, $data);
                break;

            case 'boolean':
                $value = intval($data);
                break;
        }

        return null !== $value ? $this->localizeValue($locale, $attributeCode, $value, $context) : [];
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
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface && ProductNormalizer::API_IMPORT_FORMAT === $format;
    }

    /**
     * Localize a value
     *
     * @param string $locale
     * @param string $attributeCode
     * @param mixed  $value
     * @param array  $context
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
     * @param string $store
     * @param mixed  $value
     * @param string $attributeCode
     *
     * @return array
     */
    protected function normalizeLocalizedValue($store, $value, $attributeCode)
    {
        $normalized = [];
        if (is_array($value)) {
            foreach ($value as $option) {
                if (is_array($option)) {
                    $normalized[] = array_merge($option, [$this->attributesHelper->getStoreHeader() => $store]);
                } else {
                    $normalized[] = [
                        $this->attributesHelper->getStoreHeader() => $store,
                        $attributeCode                            => $option
                    ];
                }
            }
        } else {
            $normalized[$store][$attributeCode] = $value;
        }

        return $normalized;
    }

    /**
     * Return value of a string product value
     *
     * @param ProductValueInterface $object
     * @param AbstractAttribute     $attribute
     * @param string                $data
     *
     * @return mixed
     *
     * @throws BackendTypeNotFoundException
     */
    protected function getStringValue(ProductValueInterface $object, AbstractAttribute $attribute, $data)
    {
        switch ($attribute->getBackendType()) {
            case 'decimal':
                $value = floatval($data);
                break;

            case 'text':
            case 'varchar':
                $value = $data;
                break;

            default:
                throw new BackendTypeNotFoundException(
                    sprintf(
                        'Backend type "%s" of attribute "%s" from product "%s" is not supported yet in ' .
                        'ProductValueNormalizer and can not be normalized.',
                        $attribute->getBackendType(),
                        $attribute->getCode(),
                        (string) $object->getEntity()->getIdentifier()
                    )
                );
                break;
        }

        return $value;
    }
}
