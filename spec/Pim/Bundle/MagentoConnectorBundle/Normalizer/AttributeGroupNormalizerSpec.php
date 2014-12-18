<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

class AttributeGroupNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\AttributeGroupNormalizer');
    }

    function it_returns_true_if_the_normalizer_can_support_given_data(AttributeGroup $attributeGroup)
    {
        $this->supportsNormalization($attributeGroup, 'api_import')->shouldReturn(true);
    }

    function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        AttributeGroup $attributeGroup
    ) {
        $this->supportsNormalization($attributeGroup, 'foo_bar')->shouldReturn(false);
    }

    function it_normalizes_an_attribute_group(AttributeGroup $attributeGroup)
    {
        $context = [
            'defaultLocale'    => 'en_US',
            'defaultStoreView' => 'Default',
            'visibility'       => true,
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ],
        ];

        $attributeGroup->setLocale('en_US')->shouldBeCalled();
        $attributeGroup->getLabel()->willReturn('My attr group');
        $attributeGroup->getSortOrder()->willReturn(1);

        $this->normalize($attributeGroup, 'api_import', $context)->shouldReturn(['My attr group' => 1]);
    }
}
