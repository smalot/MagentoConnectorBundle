<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;

class AttributeOptionValueNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\AttributeOptionValueNormalizer');
    }

    function it_returns_true_if_the_normalizer_can_support_given_data(AttributeOptionValue $optionValue)
    {
        $this->supportsNormalization($optionValue, 'api_import')->shouldReturn(true);
    }

    function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        AttributeOptionValue $optionValue
    ) {
        $this->supportsNormalization($optionValue, 'foo_bar')->shouldReturn(false);
    }

    function it_normalizes_an_option_value(AttributeOptionValue $optionValue)
    {
        $context = [
            'defaultLocale'    => 'en_US',
            'defaultStoreView' => 'Default',
            'visibility'       => true,
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ],
        ];

        $optionValue->getLocale()->willReturn('en_US');
        $optionValue->getValue()->willReturn('Option value');

        $this->normalize($optionValue, 'api_import', $context)->shouldReturn([0 => 'Option value']);
    }

    function it_returns_empty_array_if_option_value_locale_is_not_the_default_one(AttributeOptionValue $optionValue)
    {
        $context = [
            'defaultLocale'    => 'en_US',
            'defaultStoreView' => 'Default',
            'visibility'       => true,
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ],
        ];

        $optionValue->getLocale()->willReturn('fr_FR');
        $optionValue->getValue()->willReturn('Valeur de l\'option');

        $this->normalize($optionValue, 'api_import', $context)->shouldReturn([]);
    }
}
