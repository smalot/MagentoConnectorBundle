<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Merger;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MagentoMapper;
use PhpSpec\ObjectBehavior;

class MagentoMappingMergerSpec extends ObjectBehavior
{
    function let(MagentoMapper $mapper1, MagentoMapper $mapper2)
    {
        $mapper1->getPriority()->willReturn(0);
        $mapper2->getPriority()->willReturn(10);
    }

    function it_sets_parameters_to_all_mappers($mapper1, $mapper2, MagentoSoapClientParameters $clientParameters)
    {
        $this->beConstructedWith(array($mapper1, $mapper2), 'generic', 'export', true);

        $mapper1->setParameters($clientParameters, 'default')->shouldBeCalled();
        $mapper2->setParameters($clientParameters, 'default')->shouldBeCalled();

        $this->setParameters($clientParameters, 'default');
    }

    function it_gives_a_configuration_field($mapper1, $mapper2, MagentoSoapClientParameters $clientParameters)
    {
        $this->beConstructedWith(array($mapper2, $mapper1), 'generic', 'export', true);

        $mapper1->setParameters($clientParameters, 'default')->shouldBeCalled();
        $mapper2->setParameters($clientParameters, 'default')->shouldBeCalled();
        $this->setParameters($clientParameters, 'default');

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
                        'data-sources' => '{"sources":{"id":"test","text":"Text4"}}',
                        'data-targets' => '{"targets":{"id":"test","text":"Text2"},"allowAddition":true}',
                        'data-name'    => 'generic'
                    ),
                    'label' => 'pim_magento_connector.export.genericMapping.label',
                    'help'  => 'pim_magento_connector.export.genericMapping.help'
                )
            )
        ));
    }
}
