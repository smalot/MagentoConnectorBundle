<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoGroupManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\GroupMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;

class AttributeWriterSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser       $webserviceGuesser,
        FamilyMappingManager    $familyMappingManager,
        AttributeMappingManager $attributeMappingManager,
        GroupMappingManager     $groupMappingManager,
        MagentoGroupManager     $magentoGroupManager,
        Webservice              $webservice,
        StepExecution           $stepExecution
    ) {
        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);

        $this->beConstructedWith(
            $webserviceGuesser,
            $familyMappingManager,
            $attributeMappingManager,
            $groupMappingManager,
            $magentoGroupManager
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

    function it_sends_attribute_with_group_and_family_to_create_on_magento_webservice(
        Attribute $attribute,
        $webservice,
        AttributeMappingManager $attributeMappingManager,
        GroupMappingManager     $groupMappingManager,
        FamilyMappingManager    $familyMappingManager,
        MagentoGroupManager     $magentoGroupManager,
        AttributeGroup          $group,
        Family                  $family
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
        $groupMappingManager->registerGroupMapping($group, 797, 'barfoo')->shouldBeCalled();
        $magentoGroupManager->registerMagentoGroup(797, 'barfoo')->shouldBeCalled();

        $webservice->createAttribute(Argument::any())->willReturn(12);
        $groupMappingManager->getIdFromGroup(Argument::any(), 'barfoo')->willReturn(797);
        $webservice->addAttributeToAttributeSet(12, 414, 797)->shouldBeCalled();
        $attributeMappingManager->registerAttributeMapping($attribute, 12, 'barfoo')->shouldBeCalled();

        $this->write($attributes);
    }
}
