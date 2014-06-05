<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Mapper;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoAttributeMapping;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Mapper\ORMExportedAttributeMapper;
use Pim\Bundle\MagentoConnectorBundle\Merger\MagentoMappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Prophecy\Argument;

class ORMExportedAttributeMapperSpec extends ObjectBehavior
{
    protected $clientParameters;

    function let(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        AttributeMappingManager $attributeMappingManager,
        MagentoSoapClientParameters $clientParameters,
        MagentoMappingMerger $magentoAttributeMappingMerger,
        MagentoAttributeMapping $attributeMapping,
        AbstractAttribute $attribute
    ) {
        $this->beConstructedWith($hasValidCredentialsValidator, $attributeMappingManager, $magentoAttributeMappingMerger, 'generic');
        $this->setParameters($clientParameters, '');

        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(true);
        $clientParameters->getSoapUrl()->willReturn('http://test.dev/api');
        $attributeMapping->getMagentoAttributeId()->willReturn(12);
        $attributeMapping->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('attribute_code');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\MagentoConnectorBundle\Mapper\ORMExportedAttributeMapper');
    }

    function it_should_extends_mapper()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\ConnectorMappingBundle\Mapper\Mapper');
    }

    function it_return_a_sha1_identifier(
        MagentoSoapClientParameters $clientParameters
    ){
        $clientParameters->getSoapUrl()->willReturn('url_soap');
        $this->getIdentifier()->shouldReturn('94bc61f223c4c61a55e35d9ab9ec0e671897dc5a');
    }

    function it_return_an_empty_string_if_is_not_valid(HasValidCredentialsValidator $hasValidCredentialsValidator){
        $hasValidCredentialsValidator->areValidSoapCredentials(Argument::any())->willReturn(false);
        $this->getIdentifier()->shouldReturn('');
    }

    function it_should_return_a_mapping_collection_on_get_mapping(AttributeMappingManager $attributeMappingManager, MagentoSoapClientParameters $clientParameters)
    {
        $attributeMappingManager->getAllMagentoAttribute('http://test.dev/api')->willReturn([]);
        $this->getMapping()->shouldReturnAnInstanceOf('Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection');
    }

    function it_should_get_mapping_from_exported_attributes_table($attributeMappingManager, $clientParameters, MappingCollection $mapping, $attributeMapping, $attribute, $magentoAttributeMappingMerger)
    {
        $attributeMappingManager->getAllMagentoAttribute('http://test.dev/api')->willReturn([$attributeMapping]);

        $magentoAttributeMappingMerger->getMapping()->willReturn($mapping);
        $mapping->getTarget('attribute_code')->willReturn('attribute_code');

        $this->getMapping()->shouldBeAnInstanceOf('Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection');
        $mappingCollection = $this->getMapping();

        $mappingCollection->getSource(12)->shouldReturn('attribute_code');
        $mappingCollection->getTarget('attribute_code')->shouldReturn(12);
    }

    function it_should_get_mapping_from_exported_attributes_table_with_a_mapped_attribute($attributeMappingManager, $clientParameters, MappingCollection $mapping, $attributeMapping, $attribute, $magentoAttributeMappingMerger)
    {
        $attributeMappingManager->getAllMagentoAttribute('http://test.dev/api')->willReturn([$attributeMapping]);

        $magentoAttributeMappingMerger->getMapping()->willReturn($mapping);
        $mapping->getTarget('attribute_code')->willReturn('attribute_code_mapped');

        $this->getMapping()->shouldBeAnInstanceOf('Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection');
        $mappingCollection = $this->getMapping();

        $mappingCollection->getSource(12)->shouldReturn('attribute_code_mapped');
        $mappingCollection->getTarget('attribute_code_mapped')->shouldReturn(12);
    }
}
