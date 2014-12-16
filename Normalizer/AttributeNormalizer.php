<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\MagentoConnectorBundle\Helper\AttributeMappingHelper;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Attribute normalizer
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var AttributeMappingHelper */
    protected $mappingHelper;

    /** @var NormalizerInterface */
    protected $normalizer;

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
        $defaultValue     = $object->getDefaultValue();
        $pimAttributeType = $object->getAttributeType();
        $pimBackendType   = $object->getBackendType();
        $attributeType    = $this->mappingHelper->getMagentoAttributeTypeFor($pimAttributeType);
        $backendType      = $this->mappingHelper->getMagentoBackendTypeFor($pimBackendType);

        $normalized = [
            LabelDictionary::ATTRIBUTE_ID_HEADER           => $object->getCode(),
            LabelDictionary::ATTR_DEFAULT_VAL_HEADER       => $defaultValue,
            LabelDictionary::ATTRIBUTE_INPUT_HEADER        => $attributeType,
            LabelDictionary::ATTRIBUTE_BACKEND_TYPE_HEADER => $backendType,
            LabelDictionary::ATTRIBUTE_LABEL_HEADER        =>
                $object->getTranslation($context['defaultLocale'])->getLabel(),
            LabelDictionary::ATTRIBUTE_GLOBAL_HEADER       => 0,
            LabelDictionary::ATTRIBUTE_REQUIRED_HEADER     => (int) $object->isRequired(),
            LabelDictionary::ATTRIBUTE_VISIBLE_HEADER      => (int) $context['visibility'],
            LabelDictionary::ATTRIBUTE_IS_UNIQUE_HEADER    => (int) $object->isUnique()
        ];

        if ('pim_catalog_simpleselect' === $pimAttributeType ||
            'pim_catalog_multiselect' === $pimAttributeType
        ) {
            foreach ($object->getOptions() as $option) {
                $normalized['option']['value'][$option->getCode()] = $this->getValidOptionValues($option, $context);
                $normalized['option']['order'][$option->getCode()] = $option->getSortOrder();
            }
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
     * Returns valid option values (in terms of store view mapping and default locale)
     *
     * @param AttributeOption $option
     * @param array           $context
     *
     * @return array
     */
    protected function getValidOptionValues(AttributeOption $option, array $context)
    {
        $values = [];
        foreach ($option->getOptionValues() as $optionValue) {
            $values = array_merge(
                $values,
                $this->normalizer->normalize($optionValue, 'api_import', $context)
            );
        }

        return $values;
    }
}
