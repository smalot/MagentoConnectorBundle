<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute option value normalizer
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionValueNormalizer implements NormalizerInterface
{
    /** @staticvar int */
    const DEFAULT_STORE_VIEW_ID = 0;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $storeId = $this->getStoreID($object, $context);
        $value   = $this->getValue($object);

        return null !== $storeId ? [$storeId => $value] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeOptionValue && 'api_import' === $format;
    }

    /**
     * Returns store view id
     *
     * @param AttributeOptionValue $optionValue
     * @param array                $context
     *
     * @return null|string
     */
    protected function getStoreID(AttributeOptionValue $optionValue, array $context)
    {
        return $optionValue->getLocale() === $context['defaultLocale'] ? static::DEFAULT_STORE_VIEW_ID : null;
    }

    /**
     * Returns value of the attribute option
     *
     * @param AttributeOptionValue $optionValue
     *
     * @return string
     */
    protected function getValue(AttributeOptionValue $optionValue)
    {
        return $optionValue->getValue();
    }
}
