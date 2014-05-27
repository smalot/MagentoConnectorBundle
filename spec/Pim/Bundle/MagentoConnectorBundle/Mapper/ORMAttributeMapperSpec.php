<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\ConnectorMappingBundle\Manager\SimpleMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeManager;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use PhpSpec\ObjectBehavior;

class ORMAttributeMapperSpec extends ObjectBehavior
{
    protected $clientParameters;

    function let(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        SimpleMappingManager $simpleMappingManager,
        AttributeManager $attributeManager,
        MagentoSoapClientParametersRegistry $clientParameters
    ) {
        $this->beConstructedWith($hasValidCredentialsValidator, $simpleMappingManager, 'attribute', $attributeManager);
        $this->setParameters($clientParameters, '');
    }

    function it_shoulds_return_all_attributes_from_database_as_targets($attributeManager, $hasValidCredentialsValidator, $clientParameters, Attribute $attribute)
    {
        $hasValidCredentialsValidator->areValidSoapCredentials($clientParameters)->willReturn(true);

        $attributeManager->getAttributes()->willReturn(array($attribute));

        $attribute->getCode()->willReturn('foo');

        $this->getAllSources()->shouldReturn(array(array('id' => 'foo', 'text' => 'foo')));
    }
}
