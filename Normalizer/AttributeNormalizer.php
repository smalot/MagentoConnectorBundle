<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute normalizer
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalized    = null;
        $attributeType = LabelDictionary::getMagentoAttributeTypeFor($object->getAttributeType());

        if (null !== $attributeType) {
            $normalized[LabelDictionary::ATTRIBUTE_ID_HEADER]        = $object->getCode();
            $normalized[LabelDictionary::ATTR_DEFAULT_VAL_HEADER]    = $object->getDefaultValue();
            $normalized[LabelDictionary::ATTRIBUTE_TYPE_HEADER]      = $attributeType;
            $normalized[LabelDictionary::ATTRIBUTE_LABEL_HEADER]     =
                $object->getTranslation($context['defaultLocale'])->getLabel();
            $normalized[LabelDictionary::ATTRIBUTE_GLOBAL_HEADER]    = 0;
            $normalized[LabelDictionary::ATTRIBUTE_REQUIRED_HEADER]  = (int) $object->isRequired();
            $normalized[LabelDictionary::ATTRIBUTE_VISIBLE_HEADER]   = (int) $context['visibility'];
            $normalized[LabelDictionary::ATTRIBUTE_IS_UNIQUE_HEADER] = (int) $object->isUnique();
        }

        return $normalized;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractAttribute && 'api_import' === $format;
    }
}
