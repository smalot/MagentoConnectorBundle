<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\MagentoConnectorBundle\Helper\AttributeMappingHelper;
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
    /** @var AttributeMappingHelper */
    protected $mappingHelper;

    /**
     * @param AttributeMappingHelper $mappingHelper
     */
    public function __construct(AttributeMappingHelper $mappingHelper)
    {
        $this->mappingHelper = $mappingHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $attributeType = $this->mappingHelper->getMagentoAttributeTypeFor($object->getAttributeType());

        return [
            LabelDictionary::ATTRIBUTE_ID_HEADER        => $object->getCode(),
            LabelDictionary::ATTR_DEFAULT_VAL_HEADER    => $object->getDefaultValue(),
            LabelDictionary::ATTRIBUTE_TYPE_HEADER      => $attributeType,
            LabelDictionary::ATTRIBUTE_LABEL_HEADER     =>
                $object->getTranslation($context['defaultLocale'])->getLabel(),
            LabelDictionary::ATTRIBUTE_GLOBAL_HEADER    => 0,
            LabelDictionary::ATTRIBUTE_REQUIRED_HEADER  => (int) $object->isRequired(),
            LabelDictionary::ATTRIBUTE_VISIBLE_HEADER   => (int) $context['visibility'],
            LabelDictionary::ATTRIBUTE_IS_UNIQUE_HEADER => (int) $object->isUnique()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractAttribute && 'api_import' === $format;
    }
}
