<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MagentoAttributeMapperSpec extends ObjectBehavior
{
    protected $clientParameters;

    function let(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        WebserviceGuesser $webserviceGuesser,
        Webservice $webservice
    ) {
        $this->beConstructedWith($hasValidCredentialsValidator, $webserviceGuesser);

        $webserviceGuesser->getWebservice(Argument::cetera())->willReturn($webservice);
        $this->clientParameters = new MagentoSoapClientParameters('soap_user', 'soap_password', 'soap_url', 'wsdl_url');
    }

    function it_shoulds_get_an_empty_mapping_from_magento($hasValidCredentialsValidator, $webservice)
    {
        $this->setParameters($this->clientParameters, Argument::Any());
        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(true);

        $webservice->getAllAttributes()->willReturn(array('attribute_foo' => array(), 'attribute_bar' => array()));

        $mapping = $this->getMapping();
        $mapping->shouldBeAnInstanceOf('Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection');
        $mapping->toArray()->shouldReturn(array());
    }

    function it_shoulds_get_mapping_from_magento_with_madatory_attributes($hasValidCredentialsValidator, $webservice)
    {
        $this->setParameters($this->clientParameters, Argument::Any());
        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(true);

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

    function it_shoulds_do_nothing_to_save_mapping()
    {
        $this->setMapping(array())->shouldReturn(null);
    }

    function it_shoulds_get_all_magento_attributes_as_sources($hasValidCredentialsValidator, $webservice)
    {
        $this->setParameters($this->clientParameters, Argument::Any());
        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(true);

        $webservice->getAllAttributes()->willReturn(array('foo' => array(), 'bar' => array()));

        $this->getAllSources()->shouldReturn(array(array('id' => 'foo', 'text' => 'foo'), array('id' => 'bar', 'text' => 'bar')));
    }

    function it_returns_an_empty_array_as_targets_if_parameters_are_not_setted($hasValidCredentialsValidator)
    {
        $this->getAllTargets()->shouldReturn(array());
    }

    function it_shoulds_return_an_empty_array_as_sources()
    {
        $this->getAllSources()->shouldReturn(array());
    }

    function it_shoulds_have_a_priority()
    {
        $this->getPriority()->shouldReturn(0);
    }

    function it_should_give_an_proper_identifier($hasValidCredentialsValidator)
    {
        $this->setParameters($this->clientParameters, Argument::Any());
        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(true);

        $identifier = sha1('attribute-soap_urlwsdl_url');

        $this->getIdentifier()->shouldReturn($identifier);
    }

    function it_should_give_an_empty_identifier_if_the_mapper_is_not_configured()
    {
        $this->getIdentifier()->shouldReturn('');
    }
}
