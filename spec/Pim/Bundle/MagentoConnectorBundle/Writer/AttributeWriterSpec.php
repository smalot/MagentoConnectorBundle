<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\GroupMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeWriterSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser $webserviceGuesser,
        FamilyMappingManager $familyMappingManager,
        AttributeMappingManager $attributeMappingManager,
        GroupMappingManager $groupMappingManager,
        Webservice $webservice,
        StepExecution $stepExecution
    ) {
        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);

        $this->beConstructedWith(
            $webserviceGuesser,
            $familyMappingManager,
            $attributeMappingManager,
            $groupMappingManager
        );
        $this->setStepExecution($stepExecution);
    }

    function it_sends_attribute_to_create_on_magento_webservice(
        Attribute $attribute,
        $webservice,
        AttributeMappingManager $attributeMappingManager
    ) {
        $attributes = array(
            array(
                $attribute,
                array(
                    'create' => array(
                        'attributeName' => 'attribute_code'
                    ),
                )
            )
        );
        $attribute->getFamilies()->willReturn(array());
        $attribute->getGroup()->willReturn(null);
        $webservice->createAttribute(Argument::any())->willReturn(12);
        $attributeMappingManager->registerAttributeMapping($attribute, 12, 'barfoo')->shouldBeCalled();
        $this->setMagentoUrl('bar');
        $this->setWsdlUrl('foo');

        $this->write($attributes);
    }
}
