<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Mapper;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\ConnectorMappingBundle\Manager\SimpleMappingManager;
use Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Prophecy\Argument;

class MagentoMapperSpec extends ObjectBehavior
{
    protected $clientParameters;

    function let(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        MagentoSoapClientParameters $clientParameters
    ) {
        $this->beConstructedWith($hasValidCredentialsValidator);
        $this->setParameters($clientParameters, '');

        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(true);
        $clientParameters->getSoapUrl()->willReturn('http://test.dev/api');
    }

    function it_return_an_empty_string_if_is_not_valid(HasValidCredentialsValidator $hasValidCredentialsValidator){
        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(false);
        $this->getIdentifier()->shouldReturn('');
    }
}
