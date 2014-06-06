<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Mapper;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\ConnectorMappingBundle\Manager\SimpleMappingManager;
use Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoAttributeMapping;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Mapper\ORMExportedAttributeMapper;
use Pim\Bundle\MagentoConnectorBundle\Merger\MagentoMappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Prophecy\Argument;

class ORMPimMapperSpec extends ObjectBehavior
{
    protected $clientParameters;

    function let(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        SimpleMappingManager $simpleMappingManager,
        MagentoSoapClientParameters $clientParameters
    ) {
        $this->beConstructedWith($hasValidCredentialsValidator, $simpleMappingManager, 'generic');
        $this->setParameters($clientParameters, '');

        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(true);
        $clientParameters->getSoapUrl()->willReturn('http://test.dev/api');
    }

    function it_return_a_sha1_identifier(
        MagentoSoapClientParameters $clientParameters
    ){
        $clientParameters->getSoapUrl()->willReturn('url_soap');
        $this->getIdentifier()->shouldReturn('9ee9adb749263a9a79ab7d8f20cc7a1ab0312bc4');
    }

    function it_return_an_empty_string_if_is_not_valid(HasValidCredentialsValidator $hasValidCredentialsValidator){
        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(false);
        $this->getIdentifier()->shouldReturn('');
    }
}
