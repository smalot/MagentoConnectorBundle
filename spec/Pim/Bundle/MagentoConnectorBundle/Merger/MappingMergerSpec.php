<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Merger;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use Pim\Bundle\MagentoConnectorBundle\Mapper\Mapper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MappingMergerSpec extends ObjectBehavior
{
    function let(Mapper $mapper1, Mapper $mapper2)
    {
        $mapper1->getPriority()->willReturn(0);
        $mapper2->getPriority()->willReturn(10);
    }

    function it_sets_parameters_to_all_mappers($mapper1, $mapper2, MagentoSoapClientParameters $clientParameters)
    {
        $this->beConstructedWith(array($mapper1, $mapper2), 'generic');

        $mapper1->setParameters($clientParameters)->shouldBeCalled();
        $mapper2->setParameters($clientParameters)->shouldBeCalled();

        $this->setParameters($clientParameters);
    }

    function it_gives_ordered_mapping_from_mappers($mapper1, $mapper2, MagentoSoapClientParameters $clientParameters)
    {
        $this->beConstructedWith(array($mapper2, $mapper1), 'generic');

        $mapper1->setParameters($clientParameters)->shouldBeCalled();
        $mapper2->setParameters($clientParameters)->shouldBeCalled();
        $this->setParameters($clientParameters);

        $mapper1->getMapping()->willReturn(new MappingCollection(array(
            'source' => array(
                'source' => 'source',
                'target' => 'target1',
                'deletable' => true
            )
        )));

        $mapper2->getMapping()->willReturn(new MappingCollection(array(
            'source' => array(
                'source' => 'source',
                'target' => 'target2',
                'deletable' => true
            )
        )));

        $mappingCollection = $this->getMapping();
        $mappingCollection->toArray()->shouldBe(array(
            'source' => array(
                'source' => 'source',
                'target' => 'target2',
                'deletable' => true
            )
        ));
    }

    function it_gives_an_empty_mapping_collection_if_any_mapper_are_setted(MagentoSoapClientParameters $clientParameters)
    {
        $this->beConstructedWith(array(), 'generic');
        $this->setParameters($clientParameters);

        $mappingCollection = $this->getMapping();
        $mappingCollection->toArray()->shouldBe(array());
    }

    function it_gives_an_empty_mapping_collection_parameters_are_not_setted($mapper1, $mapper2)
    {
        $this->beConstructedWith(array($mapper1, $mapper2), 'generic');

        $mappingCollection = $this->getMapping();
        $mappingCollection->toArray()->shouldBe(array());
    }

    function it_sets_all_mappers_with_given_mapping($mapper1, $mapper2, MagentoSoapClientParameters $clientParameters)
    {
        $this->beConstructedWith(array($mapper2, $mapper1), 'generic');

        $mapper1->setParameters($clientParameters)->shouldBeCalled();
        $mapper2->setParameters($clientParameters)->shouldBeCalled();
        $this->setParameters($clientParameters);

        $mapper1->setMapping(array('foo'))->shouldBeCalled();
        $mapper2->setMapping(array('foo'))->shouldBeCalled();

        $this->setMapping(array('foo'));
    }

    function it_sets_any_mappers_if_mappers_are_not_setted($mapper1, $mapper2)
    {
        $this->beConstructedWith(array($mapper2, $mapper1), 'generic');

        $mapper1->setMapping(array('foo'))->shouldNotBeCalled();
        $mapper2->setMapping(array('foo'))->shouldNotBeCalled();

        $this->setMapping(array('foo'));
    }

    function it_gives_a_configuration_field($mapper1, $mapper2, MagentoSoapClientParameters $clientParameters)
    {
        $this->beConstructedWith(array($mapper2, $mapper1), 'generic');

        $mapper1->setParameters($clientParameters)->shouldBeCalled();
        $mapper2->setParameters($clientParameters)->shouldBeCalled();
        $this->setParameters($clientParameters);

        $mapper1->getAllSources()->willReturn(array('id' => 'test', 'text' => 'Text3'));
        $mapper2->getAllSources()->willReturn(array('id' => 'test', 'text' => 'Text4'));

        $mapper1->getAllTargets()->willReturn(array('id' => 'test', 'text' => 'Text1'));
        $mapper2->getAllTargets()->willReturn(array('id' => 'test', 'text' => 'Text2'));

        $this->getConfigurationField()->shouldReturn(array(
            'genericMapping' => array(
                'type'    => 'textarea',
                'options' => array(
                    'required' => false,
                    'attr'     => array(
                        'class' => 'mapping-field',
                        'data-sources' => '{"id":"test","text":"Text4"}',
                        'data-targets' => '{"id":"test","text":"Text2"}',
                        'data-name'    => 'generic'
                    ),
                    'help' => 'pim_magento_connector.export.genericMapping.label'
                )
            )
        ));
    }
}
