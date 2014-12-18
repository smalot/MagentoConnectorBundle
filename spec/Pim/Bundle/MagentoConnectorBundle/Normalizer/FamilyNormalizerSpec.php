<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Family;

class FamilyNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\FamilyNormalizer');
    }

    function it_returns_true_if_the_normalizer_can_support_given_data(Family $family)
    {
        $this->supportsNormalization($family, 'api_import')->shouldReturn(true);
    }

    function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        Family $family
    ) {
        $this->supportsNormalization($family, 'foo_bar')->shouldReturn(false);
    }

    function it_normalizes_a_family(Family $family)
    {
        $context = [
            'defaultLocale'    => 'en_US',
            'defaultStoreView' => 'Default',
            'visibility'       => true,
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ],
        ];

        $family->setLocale('en_US')->shouldBeCalled();
        $family->getLabel()->willReturn('My attr set');

        $this->normalize($family, 'api_import', $context)->shouldReturn(['attribute_set_name' => 'My attr set']);
    }
}
