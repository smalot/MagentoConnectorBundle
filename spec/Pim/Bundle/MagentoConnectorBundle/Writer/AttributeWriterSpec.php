<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeGroupMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;

class AttributeWriterSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser            $webserviceGuesser,
        FamilyMappingManager         $familyMappingManager,
        AttributeMappingManager      $attributeMappingManager,
        AttributeGroupMappingManager $attributeGroupMappingManager,
        Webservice                   $webservice,
        StepExecution                $stepExecution
    ) {
        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);

        $this->beConstructedWith(
            $webserviceGuesser,
            $familyMappingManager,
            $attributeMappingManager,
            $attributeGroupMappingManager
        );
        $this->setStepExecution($stepExecution);
    }

    function it_sends_attribute_to_create_on_magento_webservice(
        AbstractAttribute $attribute,
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

    function it_sends_attribute_with_group_and_family_to_create_on_magento_webservice(
        AbstractAttribute $attribute,
        $webservice,
        AttributeMappingManager      $attributeMappingManager,
        AttributeGroupMappingManager $attributeGroupMappingManager,
        FamilyMappingManager         $familyMappingManager,
        AttributeGroup               $group,
        Family                       $family
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
        $this->setMagentoUrl('bar');
        $this->setWsdlUrl('foo');

        $attribute->getFamilies()->willReturn(array($family));
        $attribute->getGroup()->willReturn($group);
        $group->getCode()->willReturn('group_name');
        $familyMappingManager->getIdFromFamily(Argument::any(), 'barfoo')->willReturn(414);
        $webservice->addAttributeGroupToAttributeSet(414, 'group_name')->shouldBeCalled()->willReturn(797);
        $attributeGroupMappingManager->registerGroupMapping($group, $family, 797, 'barfoo')->shouldBeCalled();

        $webservice->createAttribute(Argument::any())->willReturn(12);
        $attributeGroupMappingManager->getIdFromGroup($group, $family, 'barfoo')->willReturn(797);
        $webservice->addAttributeToAttributeSet(12, 414, 797)->shouldBeCalled();
        $attributeMappingManager->registerAttributeMapping($attribute, 12, 'barfoo')->shouldBeCalled();

        $this->write($attributes);
    }
}
