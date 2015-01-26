<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Family;

class AddMandatoryAttributeToSetsProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Processor\AddMandatoryAttributeToSetsProcessor');
    }

    function it_returns_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_processes_attribute_to_associate_it_to_sets_and_groups(Family $family)
    {
        $family->setLocale('en_US')->shouldBeCalled();
        $family->getLabel()->willReturn('Family Label');

        $this->process(['family' => $family])->shouldReturn([
            [
                'attribute_set_id'   => 'Family Label',
                'attribute_id'       => 'sku',
                'attribute_group_id' => 'General',
                'sort_order'         => 0
            ],
            [
                'attribute_set_id'   => 'Family Label',
                'attribute_id'       => 'visibility',
                'attribute_group_id' => 'General',
                'sort_order'         => 1
            ],
            [
                'attribute_set_id'   => 'Family Label',
                'attribute_id'       => 'status',
                'attribute_group_id' => 'General',
                'sort_order'         => 2
            ]
        ]);
    }
}
