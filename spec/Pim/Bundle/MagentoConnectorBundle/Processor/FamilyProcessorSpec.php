<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyProcessorSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Processor\FamilyProcessor');
    }

    function it_returns_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_processes_a_family(Family $family, AttributeGroup $group1, AttributeGroup $group2, $normalizer)
    {
        $context = [
            'defaultLocale'    => 'en_US',
            'defaultStoreView' => 'Default',
            'visibility'       => true,
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ],
        ];

        $item = [
            'family' => $family,
            'groups' => [
                $group1,
                $group2
            ]
        ];

        $normalizer->normalize($family, 'api_import', $context)
            ->shouldBeCalled()
            ->willReturn(['attribute_set_name' => 'My attr set']);
        $normalizer->normalize($group1, 'api_import', $context)->shouldBeCalled()->willReturn(['My attr group1' => 1]);
        $normalizer->normalize($group2, 'api_import', $context)->shouldBeCalled()->willReturn(['My attr group2' => 1]);

        $this->process($item)->shouldReturn([
            'attribute_set_name' => 'My attr set',
            'My attr group1' => 1,
            'My attr group2' => 1
        ]);
    }
}
