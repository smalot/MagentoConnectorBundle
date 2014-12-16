<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\MagentoConnectorBundle\Helper\AttributeMappingHelper;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(AttributeMappingHelper $mappingHelper)
    {
        $this->beConstructedWith($mappingHelper);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\AttributeNormalizer');
    }

    function it_returns_true_if_the_normalizer_can_support_given_data(AbstractAttribute $attribute)
    {
        $this->supportsNormalization($attribute, 'api_import')->shouldReturn(true);
    }

    function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        AbstractAttribute $attribute
    ) {
        $this->supportsNormalization($attribute, 'foo_bar')->shouldReturn(false);
    }

    function it_normalizes_a_supported_attribute(
        AbstractAttribute $attribute,
        AttributeTranslation $translation,
        $mappingHelper
    ) {
        $context = [
            'defaultLocale' => 'en_US',
            'visibility' => true
        ];

        $translation->getLabel()->willReturn('My attribute');

        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->getDefaultValue()->willReturn('value');
        $attribute->getTranslation('en_US')->willReturn($translation);
        $attribute->isRequired()->willReturn(true);
        $attribute->isUnique()->willReturn(false);

        $mappingHelper->getMagentoAttributeType('pim_catalog_text')->shouldBeCalled()->willReturn('text');

        $this->normalize($attribute, 'api_import', $context)->shouldReturn([
            'attribute_id'     => 'attribute_code',
            'default'          => 'value',
            'type'             => 'text',
            'label'            => 'My attribute',
            'global'           => 0,
            'required'         => 1,
            'visible_on_front' => 1,
            'unique'           => 0
        ]);
    }
}
