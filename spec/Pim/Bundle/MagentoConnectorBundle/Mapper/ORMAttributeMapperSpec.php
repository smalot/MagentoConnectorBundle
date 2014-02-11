<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Manager\SimpleMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeManager;
use Pim\Bundle\MagentoConnectorBundle\Entity\SimpleMapping;
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

    function it_gets_mapping_from_database($simpleMappingManager, $hasValidCredentialsValidator, SimpleMapping $simpleMapping)
    {
        $this->setParameters($this->clientParameters);
        $hasValidCredentialsValidator->areValidSoapParameters(Argument::any())->willReturn(true);

        $simpleMapping->getSource()->willReturn('attribute_source');
        $simpleMapping->getTarget()->willReturn('attribute_target');
        $simpleMappingManager->getMapping($this->getIdentifier('attribute'))->willReturn(array($simpleMapping));

        $mapping = $this->getMapping();

        $mapping->shouldBeAnInstanceOf('Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection');
        $mapping->toArray()->shouldReturn(array(
            'attribute_source' => array(
                'source'    => 'attribute_source',
                'target'    => 'attribute_target',
                'deletable' => true
            )
        ));
    }

    function it_returns_an_empty_array_if_parameters_are_not_setted($simpleMappingManager, $hasValidCredentialsValidator, SimpleMapping $simpleMapping)
    {
        $simpleMapping->getSource()->willReturn('attribute_source');
        $simpleMapping->getTarget()->willReturn('attribute_target');

        $mapping = $this->getMapping();

        $mapping->shouldBeAnInstanceOf('Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection');
        $mapping->toArray()->shouldReturn(array());
    }

    function it_shoulds_store_mapping_in_database($simpleMappingManager, $hasValidCredentialsValidator)
    {
        $this->setParameters($this->clientParameters);
        $hasValidCredentialsValidator->areValidSoapParameters(Argument::any())->willReturn(true);

        $simpleMappingManager->setMapping(array('mapping'), $this->getIdentifier('attribute'))->shouldBeCalled();

        $this->setMapping(array('mapping'));
    }

    function it_shoulds_store_nothing_if_parameters_are_not_setted($simpleMappingManager)
    {
        $simpleMappingManager->setMapping(Argument::cetera())->shouldNotBeCalled();

        $this->setMapping(array('mapping'));
    }

    function it_shoulds_return_any_targets()
    {
        $this->getAllTargets()->shouldReturn(array());
    }

    function it_shoulds_return_all_attributes_from_database_as_sources($attributeManager, $hasValidCredentialsValidator, Attribute $attribute)
    {
        $this->setParameters($this->clientParameters);
        $hasValidCredentialsValidator->areValidSoapParameters(Argument::any())->willReturn(true);

        $attributeManager->getAttributes()->willReturn(array($attribute));

        $attribute->getCode()->willReturn('foo');

        $this->getAllSources()->shouldReturn(array(array('id' => 'foo', 'text' => 'foo')));
    }

    function it_shoulds_have_a_priority()
    {
        $this->getPriority()->shouldReturn(10);
    }
}
