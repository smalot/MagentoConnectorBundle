<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MagentoCategoryMapperSpec extends ObjectBehavior
{
    protected $clientParameters;

    function let(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        WebserviceGuesser $webserviceGuesser,
        Webservice $webservice,
        MagentoSoapClientParameters $clientParameters
    ) {
        $this->beConstructedWith($hasValidCredentialsValidator, $webserviceGuesser);

        $this->setParameters($clientParameters, '');
        $webserviceGuesser->getWebservice($clientParameters)->willReturn($webservice);
    }

    function it_returns_an_empty_mapping_from_magento($hasValidCredentialsValidator, $webservice)
    {
        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(true);

        $webservice->getCategoriesStatus()->willReturn(array('category_id_1' => array(), 'categorie_id_2' => array()));

        $mapping = $this->getMapping();
        $mapping->shouldBeAnInstanceOf('Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection');
        $mapping->toArray()->shouldReturn(array());
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

    function it_returns_all_magento_categories_as_targets($hasValidCredentialsValidator, $webservice)
    {
        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(true);

        $webservice->getCategoriesStatus()->willReturn(array('foo' => array('name' => 'Foo'), 'bar' => array('name' => 'Bar')));

        $this->getAllTargets()->shouldReturn(array(array('id' => 'foo', 'text' => 'Foo'), array('id' => 'bar', 'text' => 'Bar')));
    }

    function it_returns_a_proper_identifier($hasValidCredentialsValidator, $clientParameters)
    {
        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(true);
        $clientParameters->getSoapUrl()->willReturn('soap_urlwsdl_url');
        $identifier = sha1('category-soap_urlwsdl_url');

        $this->getIdentifier()->shouldReturn($identifier);
    }
}
