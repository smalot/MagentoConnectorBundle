<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\ConnectorMappingBundle\Manager\SimpleMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use Pim\Bundle\ConnectorMappingBundle\Entity\SimpleMapping;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ORMPimMapperSpec extends ObjectBehavior
{
    protected $clientParameters;

    function let(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        SimpleMappingManager $simpleMappingManager
    ) {
        $this->beConstructedWith($hasValidCredentialsValidator, $simpleMappingManager, 'generic');
        $this->clientParameters = new MagentoSoapClientParameters('soap_user', 'soap_password', 'soap_url', 'wsdl_url');
    }

    function it_shoulds_return_nothing_as_sources_if_it_is_not_well_configured()
    {
        $this->getAllSources()->shouldReturn(array());
    }

    function it_shoulds_return_nothing_as_targets_if_it_is_not_well_configured()
    {
        $this->getAllTargets()->shouldReturn(array());
    }

    function it_shoulds_return_nothing_as_mapping_if_it_is_not_well_configured()
    {
        $this->getMapping()->shouldBeAnInstanceOf('Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection');
    }

    function it_gets_mapping_from_database($simpleMappingManager, $hasValidCredentialsValidator, SimpleMapping $simpleMapping)
    {
        $this->setParameters($this->clientParameters);
        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(true);

        $simpleMapping->getSource()->willReturn('generic_source');
        $simpleMapping->getTarget()->willReturn('generic_target');
        $simpleMappingManager->getMapping($this->getIdentifier('generic'))->willReturn(array($simpleMapping));

        $mapping = $this->getMapping();

        $mapping->shouldBeAnInstanceOf('Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection');
        $mapping->toArray()->shouldReturn(array(
            'generic_source' => array(
                'source'    => 'generic_source',
                'target'    => 'generic_target',
                'deletable' => true
            )
        ));
    }

    function it_shoulds_store_mapping_in_database($simpleMappingManager, $hasValidCredentialsValidator)
    {
        $this->setParameters($this->clientParameters);
        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(true);

        $simpleMappingManager->setMapping(array('mapping'), $this->getIdentifier('generic'))->shouldBeCalled();

        $this->setMapping(array('mapping'));
    }

    function it_shoulds_store_nothing_if_parameters_are_not_setted($simpleMappingManager)
    {
        $simpleMappingManager->setMapping(Argument::cetera())->shouldNotBeCalled();

        $this->setMapping(array('mapping'));
    }

    function it_shoulds_return_all_items_from_database_as_sources($simpleMappingManager, $hasValidCredentialsValidator, SimpleMapping $simpleMapping)
    {
        $this->setParameters($this->clientParameters);
        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(true);

        $simpleMappingManager->getMapping($this->getIdentifier('generic'))->willReturn(array($simpleMapping));
        $simpleMapping->getSource()->willReturn('generic_source');
        $simpleMapping->getTarget()->willReturn('generic_target');

        $this->getAllSources()->shouldReturn(array(array('id' => 'generic_source', 'text' => 'generic_source')));
    }

    function it_shoulds_return_all_items_from_database_as_targets($simpleMappingManager, $hasValidCredentialsValidator, SimpleMapping $simpleMapping)
    {
        $this->setParameters($this->clientParameters);
        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(true);

        $simpleMappingManager->getMapping($this->getIdentifier('generic'))->willReturn(array($simpleMapping));
        $simpleMapping->getSource()->willReturn('generic_source');
        $simpleMapping->getTarget()->willReturn('generic_target');

        $this->getAllTargets()->shouldReturn(array(array('id' => 'generic_target', 'text' => 'generic_target')));
    }

    function it_shoulds_have_a_priority()
    {
        $this->getPriority()->shouldReturn(10);
    }
}
