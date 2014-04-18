<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesserFactory;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\CategoryWebservice;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MagentoCategoryMapperSpec extends ObjectBehavior
{
    protected $clientParameters;

    function let(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        WebserviceGuesserFactory $webserviceGuesserFactory,
        CategoryWebservice $categoryWebservice
    ) {
        $this->beConstructedWith($hasValidCredentialsValidator, $webserviceGuesserFactory);

        $webserviceGuesserFactory->getWebservice('category', Argument::cetera())->willReturn($categoryWebservice);
        $this->clientParameters = new MagentoSoapClientParameters('soap_user', 'soap_password', 'soap_url');
    }

    function it_shoulds_get_an_empty_mapping_from_magento($hasValidCredentialsValidator, $categoryWebservice)
    {
        $this->setParameters($this->clientParameters);
        $hasValidCredentialsValidator->areValidSoapParameters(Argument::any())->willReturn(true);

        $categoryWebservice->getCategoriesStatus()->willReturn(array('category_id_1' => array(), 'categorie_id_2' => array()));

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

    function it_shoulds_get_all_magento_categories_as_targets($hasValidCredentialsValidator, $categoryWebservice)
    {
        $this->setParameters($this->clientParameters);
        $hasValidCredentialsValidator->areValidSoapParameters(Argument::any())->willReturn(true);

        $categoryWebservice->getCategoriesStatus()->willReturn(array('foo' => array('name' => 'Foo'), 'bar' => array('name' => 'Bar')));

        $this->getAllTargets()->shouldReturn(array(array('id' => 'foo', 'text' => 'Foo'), array('id' => 'bar', 'text' => 'Bar')));
    }

    function it_should_give_an_proper_identifier($hasValidCredentialsValidator)
    {
        $this->setParameters($this->clientParameters);
        $hasValidCredentialsValidator->areValidSoapParameters(Argument::any())->willReturn(true);

        $identifier = sha1('category-soap_url'.MagentoSoapClientParameters::SOAP_WSDL_URL);

        $this->getIdentifier()->shouldReturn($identifier);
    }
}
