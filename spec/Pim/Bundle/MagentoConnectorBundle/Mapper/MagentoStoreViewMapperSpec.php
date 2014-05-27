<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MagentoStoreViewMapperSpec extends ObjectBehavior
{
    protected $clientParameters;

    function let(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        WebserviceGuesser $webserviceGuesser,
        Webservice $webservice,
        MagentoSoapClientParametersRegistry $clientParameters
    ) {
        $this->beConstructedWith($hasValidCredentialsValidator, $webserviceGuesser);

        $webserviceGuesser->getWebservice($clientParameters)->willReturn($webservice);
        $this->setParameters($clientParameters, '');
    }

    function it_shoulds_get_an_empty_mapping_from_magento($hasValidCredentialsValidator, $webservice, $clientParameters)
    {
        $hasValidCredentialsValidator->areValidSoapCredentials($clientParameters)->willReturn(true);

        $webservice->getStoreViewsList()->willReturn(array(array('code' => 'attribute_code')));

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

    function it_shoulds_do_nothing_to_save_mapping()
    {
        $this->setMapping(array())->shouldReturn(null);
    }

    function it_shoulds_get_all_magento_storeviews_as_targets($hasValidCredentialsValidator, $webservice, $clientParameters)
    {
        $hasValidCredentialsValidator->areValidSoapCredentials($clientParameters)->willReturn(true);

        $webservice->getStoreViewsList()->willReturn(array(array('code' => 'attribute_code')));

        $this->getAllTargets()->shouldReturn(array(array('id' => 'attribute_code', 'text' => 'attribute_code')));
    }

    function it_should_give_an_proper_identifier($hasValidCredentialsValidator, $clientParameters)
    {
        $hasValidCredentialsValidator->areValidSoapCredentials($clientParameters)->willReturn(true);
        $clientParameters->getSoapUrl()->willReturn('soap_urlwsdl_url');
        $identifier = sha1('storeview-soap_urlwsdl_url');
        $this->getIdentifier()->shouldReturn($identifier);
    }
}
