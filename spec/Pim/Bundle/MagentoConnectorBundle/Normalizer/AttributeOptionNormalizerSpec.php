<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

class AttributeOptionNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\AttributeOptionNormalizer');
    }

    public function it_returns_true_if_the_normalizer_can_support_given_data(AttributeOption $attributeOption)
    {
        $this->supportsNormalization($attributeOption, 'api_import')->shouldReturn(true);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        AttributeOption $attributeOption
    ) {
        $this->supportsNormalization($attributeOption, 'foo_bar')->shouldReturn(false);
    }

    public function it_normalizes_an_attribute_option_in_api_import_format(AttributeOption $attributeOption)
    {
        $attributeOption->getCode()->willReturn('foo');
        $this->normalize($attributeOption, 'api_import')->shouldReturn('foo');
    }
}
