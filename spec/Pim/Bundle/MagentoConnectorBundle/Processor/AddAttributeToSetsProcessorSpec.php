<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AddAttributeToSetsProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Processor\AddAttributeToSetsProcessor');
    }

    function it_returns_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_processes_attribute_to_associate_it_to_sets_and_groups(
        AbstractAttribute $attribute,
        Family $family,
        Family $family2,
        AttributeGroup $group
    ) {
        $attribute->getFamilies()->willReturn([$family, $family2]);
        $attribute->getGroup()->willReturn($group);
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->getSortOrder()->willReturn('1');

        $group->setLocale('en_US')->shouldBeCalled();
        $group->getLabel()->willReturn('Group Label');

        $family->setLocale('en_US')->shouldBeCalled();
        $family->getLabel()->willReturn('Family Label');

        $family2->setLocale('en_US')->shouldBeCalled();
        $family2->getLabel()->willReturn('Family 2 Label');

        $this->process($attribute)->shouldReturn([
            0 => [
                'attribute_set_id'   => 'Family Label',
                'attribute_id'       => 'attribute_code',
                'attribute_group_id' => 'Group Label',
                'sort_order'         => '1'
            ],
            1 => [
                'attribute_set_id'   => 'Family 2 Label',
                'attribute_id'       => 'attribute_code',
                'attribute_group_id' => 'Group Label',
                'sort_order'         => '1'
            ]
        ]);
    }
}
