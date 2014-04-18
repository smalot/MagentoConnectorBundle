<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesserFactory;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\StoreViewsWebservice;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MagentoStoreViewMapperSpec extends ObjectBehavior
{
    protected $clientParameters;

    function let(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        WebserviceGuesserFactory $webserviceGuesserFactory,
        StoreViewsWebservice $storeViewsWebservice
    ) {
        $this->beConstructedWith($hasValidCredentialsValidator, $webserviceGuesserFactory);

        $webserviceGuesserFactory->getWebservice('storeviews', Argument::cetera())->willReturn($storeViewsWebservice);
        $this->clientParameters = new MagentoSoapClientParameters('soap_user', 'soap_password', 'soap_url');
    }

    function it_shoulds_get_an_empty_mapping_from_magento($hasValidCredentialsValidator, $storeViewsWebservice)
    {
        $this->setParameters($this->clientParameters);
        $hasValidCredentialsValidator->areValidSoapParameters(Argument::any())->willReturn(true);

        $storeViewsWebservice->getStoreViewsList()->willReturn(array(array('code' => 'attribute_code')));

        $mapping = $this->getMapping();
        $mapping->shouldBeAnInstanceOf('Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection');
        $mapping->toArray()->shouldReturn(array());
    }

    function it_returns_an_empty_collection_if_parameters_are_not_setted()
    {
        $mapping = $this->getMapping();
        $mapping->shouldBeAnInstanceOf('Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection');
        $mapping->toArray()->shouldReturn(array());
    }

    function it_shoulds_do_nothing_to_save_mapping()
    {
        $this->setMapping(array())->shouldReturn(null);
    }

    function it_shoulds_get_all_magento_storeviews_as_targets($hasValidCredentialsValidator, $storeViewsWebservice)
    {
        $this->setParameters($this->clientParameters);
        $hasValidCredentialsValidator->areValidSoapParameters(Argument::any())->willReturn(true);

        $storeViewsWebservice->getStoreViewsList()->willReturn(array(array('code' => 'attribute_code')));

        $this->getAllTargets()->shouldReturn(array(array('id' => 'attribute_code', 'text' => 'attribute_code')));
    }

    function it_should_give_an_proper_identifier($hasValidCredentialsValidator)
    {
        $this->setParameters($this->clientParameters);
        $hasValidCredentialsValidator->areValidSoapParameters(Argument::any())->willReturn(true);

        $identifier = sha1('storeview-soap_url'.MagentoSoapClientParameters::SOAP_WSDL_URL);

        $this->getIdentifier()->shouldReturn($identifier);
    }
}
