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
        $attributeType = $this->mappingHelper->getMagentoAttributeType($object->getAttributeType());

        return [
            AttributeLabelDictionary::ID_HEADER        => $object->getCode(),
            AttributeLabelDictionary::DEFAULT_VALUE_HEADER    => $object->getDefaultValue(),
            AttributeLabelDictionary::TYPE_HEADER      => $attributeType,
            AttributeLabelDictionary::LABEL_HEADER     =>
                $object->getTranslation($context['defaultLocale'])->getLabel(),
            AttributeLabelDictionary::GLOBAL_HEADER    => 0,
            AttributeLabelDictionary::REQUIRED_HEADER  => (int) $object->isRequired(),
            AttributeLabelDictionary::VISIBLE_HEADER   => (int) $context['visibility'],
            AttributeLabelDictionary::IS_UNIQUE_HEADER => (int) $object->isUnique()
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
