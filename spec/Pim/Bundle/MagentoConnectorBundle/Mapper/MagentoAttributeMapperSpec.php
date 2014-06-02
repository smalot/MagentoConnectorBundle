<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use PhpSpec\ObjectBehavior;

class MagentoAttributeMapperSpec extends ObjectBehavior
{
    protected $clientParameters;

    function let(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        WebserviceGuesser $webserviceGuesser,
        Webservice $webservice,
        MagentoSoapClientParametersRegistry $clientParametersRegistry,
        MagentoSoapClientParameters $clientParameters
    ) {
        $this->beConstructedWith($hasValidCredentialsValidator, $webserviceGuesser);

        $clientParametersRegistry->getInstance(null, null, null, '/api/soap/?wsdl', 'default', null, null)->willReturn($clientParameters);

        $this->setParameters($clientParameters, '');
        $webserviceGuesser->getWebservice($clientParameters)->willReturn($webservice);
    }

    function it_returns_an_empty_mapping_from_magento($hasValidCredentialsValidator, $webservice, $clientParameters)
    {
        $hasValidCredentialsValidator->areValidSoapCredentials($clientParameters)->willReturn(true);

        $webservice->getAllAttributes()->willReturn(array('attribute_foo' => array(), 'attribute_bar' => array()));

        $mapping = $this->getMapping();
        $mapping->shouldBeAnInstanceOf('Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection');
        $mapping->toArray()->shouldReturn(array());
    }

    function it_returns_mapping_from_magento_with_madatory_attributes($hasValidCredentialsValidator, $webservice, $clientParameters)
    {
        $hasValidCredentialsValidator->areValidSoapCredentials($clientParameters)->willReturn(true);

        $webservice->getAllAttributes()->willReturn(array('name' => array(), 'attribute_bar' => array()));

        $mapping = $this->getMapping();
        $mapping->shouldBeAnInstanceOf('Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection');
        $mapping->toArray()->shouldReturn(array('name' => array('source' => 'name', 'target' => 'name', 'deletable' => false)));
    }

    function it_returns_an_empty_collection_if_parameters_are_not_setted()
    {
        $mapping = $this->getMapping();
        $mapping->shouldBeAnInstanceOf('Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection');
        $mapping->toArray()->shouldReturn(array());
    }

    function it_returns__nothing_to_save_mapping()
    {
        $this->setMapping(array())->shouldReturn(null);
    }

    function it_returns_all_magento_attributes_as_sources($hasValidCredentialsValidator, $webservice, $clientParameters)
    {
        $hasValidCredentialsValidator->areValidSoapCredentials($clientParameters)->willReturn(true);

        $webservice->getAllAttributes()->willReturn(array('foo' => array(), 'bar' => array()));

        $this->getAllTargets()->shouldReturn(array(array('id' => 'foo', 'text' => 'foo'), array('id' => 'bar', 'text' => 'bar')));
    }

    function it_returns_an_empty_array_as_targets_if_parameters_are_not_setted($hasValidCredentialsValidator)
    {
        $this->getAllTargets()->shouldReturn(array());
    }

    function it_returns_an_empty_array_as_sources()
    {
        $this->getAllSources()->shouldReturn(array());
    }

    function it_shoulds_have_a_priority()
    {
        $this->getPriority()->shouldReturn(0);
    }

    function it_returns_an_proper_identifier($hasValidCredentialsValidator, $clientParameters)
    {
        $hasValidCredentialsValidator->areValidSoapCredentials($clientParameters)->willReturn(true);

        $clientParameters->getSoapUrl()->willReturn('soap_urlwsdl_url');
        $identifier = sha1('attribute-soap_urlwsdl_url');

        $this->getIdentifier()->shouldReturn($identifier);
    }

    function it_returns_an_empty_identifier_if_the_mapper_is_not_configured()
    {
        $this->getIdentifier()->shouldReturn('');
    }

    function it_should_be_called_once($hasValidCredentialsValidator, $webservice, $clientParameters)
    {
        $hasValidCredentialsValidator->areValidSoapCredentials($clientParameters)->willReturn(true);
        $webservice->getAllAttributes()->shouldBeCalledTimes(1)->willReturn(array('foo' => array(), 'bar' => array()));
        $this->getAllTargets();
    }
}
