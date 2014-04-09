<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Manager\SimpleMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeManager;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ORMAttributeMapperSpec extends ObjectBehavior
{
    protected $clientParameters;

    function let(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        SimpleMappingManager $simpleMappingManager,
        AttributeManager $attributeManager
    ) {
        $this->beConstructedWith($hasValidCredentialsValidator, $simpleMappingManager, 'attribute', $attributeManager);
        $this->clientParameters = new MagentoSoapClientParameters('soap_user', 'soap_password', 'soap_url');
    }

    function it_shoulds_return_all_attributes_from_database_as_targets($attributeManager, $hasValidCredentialsValidator, Attribute $attribute)
    {
        $this->setParameters($this->clientParameters);
        $hasValidCredentialsValidator->areValidSoapParameters(Argument::any())->willReturn(true);

        $attributeManager->getAttributes()->willReturn(array($attribute));

        $attribute->getCode()->willReturn('foo');

        $this->getAllTargets()->shouldReturn(array(array('id' => 'foo', 'text' => 'foo')));
    }
}
