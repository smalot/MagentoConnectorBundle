<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Manager\SimpleMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Entity\SimpleMapping;
use PhpSpec\ObjectBehavior;

class ORMMapperSpec extends ObjectBehavior
{
    public function let(
        SimpleMappingManager $simpleMappingManager
    ) {
        $this->beConstructedWith($simpleMappingManager, 'generic');
        $simpleMappingManager->getMapping($this->getIdentifier('generic'))->willReturn(array());
    }

    public function it_shoulds_return_nothing_as_sources_if_it_is_not_well_configured()
    {
        $this->getAllSources()->shouldReturn(array());
    }

    public function it_shoulds_return_nothing_as_targets_if_it_is_not_well_configured()
    {
        $this->getAllTargets()->shouldReturn(array());
    }

    public function it_shoulds_return_nothing_as_mapping_if_it_is_not_well_configured($simpleMappingManager)
    {
        $this->getMapping()->shouldBeAnInstanceOf('Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection');
    }

    public function it_gets_mapping_from_database($simpleMappingManager, SimpleMapping $simpleMapping)
    {
        $simpleMapping->getSource()->willReturn('generic_source');
        $simpleMapping->getTarget()->willReturn('generic_target');
        $simpleMappingManager->getMapping($this->getIdentifier('generic'))->willReturn(array($simpleMapping));

        $mapping = $this->getMapping();

        $mapping->shouldBeAnInstanceOf('Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection');
        $mapping->toArray()->shouldReturn(array(
            'generic_source' => array(
                'source'    => 'generic_source',
                'target'    => 'generic_target',
                'deletable' => true,
            ),
        ));
    }

    public function it_shoulds_store_mapping_in_database($simpleMappingManager)
    {
        $simpleMappingManager->setMapping(array('mapping'), $this->getIdentifier('generic'))->shouldBeCalled();

        $this->setMapping(array('mapping'));
    }

    public function it_shoulds_return_all_items_from_database_as_sources($simpleMappingManager, SimpleMapping $simpleMapping)
    {
        $simpleMappingManager->getMapping($this->getIdentifier('generic'))->willReturn(array($simpleMapping));
        $simpleMapping->getSource()->willReturn('generic_source');
        $simpleMapping->getTarget()->willReturn('generic_target');

        $this->getAllSources()->shouldReturn(array(array('id' => 'generic_source', 'text' => 'generic_source')));
    }

    public function it_shoulds_return_all_items_from_database_as_targets($simpleMappingManager, SimpleMapping $simpleMapping)
    {
        $simpleMappingManager->getMapping($this->getIdentifier('generic'))->willReturn(array($simpleMapping));
        $simpleMapping->getSource()->willReturn('generic_source');
        $simpleMapping->getTarget()->willReturn('generic_target');

        $this->getAllTargets()->shouldReturn(array(array('id' => 'generic_target', 'text' => 'generic_target')));
    }

    public function it_shoulds_have_a_priority()
    {
        $this->getPriority()->shouldReturn(10);
    }
}
