<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoGroupManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeGroupMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
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
        MagentoGroupManager          $magentoGroupManager,
        Webservice                   $webservice,
        StepExecution                $stepExecution
    ) {
        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);

        $this->beConstructedWith(
            $webserviceGuesser,
            $familyMappingManager,
            $attributeMappingManager,
            $attributeGroupMappingManager,
            $magentoGroupManager
        );
        $this->setStepExecution($stepExecution);
    }

    function it_sends_attribute_to_create_on_magento_webservice(
        $webservice,
        AbstractAttribute $attribute,
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
        $this->setMagentoUrl('bar');
        $this->setWsdlUrl('foo');

        $attribute->getFamilies()->willReturn(array());
        $attribute->getGroup()->willReturn(null);
        $webservice->createAttribute(Argument::any())->willReturn(12);
        $attributeMappingManager->registerAttributeMapping($attribute, 12, 'barfoo')->shouldBeCalled();

        $this->write($attributes);
    }

    function it_sends_attribute_with_group_and_family_to_create_on_magento_webservice(
        $webservice,
        AbstractAttribute            $attribute,
        AttributeMappingManager      $attributeMappingManager,
        AttributeGroupMappingManager $attributeGroupMappingManager,
        FamilyMappingManager         $familyMappingManager,
        MagentoGroupManager          $magentoGroupManager,
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
        $attributeGroupMappingManager->registerGroupMapping($group, 797, 'barfoo')->shouldBeCalled();
        $magentoGroupManager->registerMagentoGroup(797, 'barfoo')->shouldBeCalled();

        $webservice->createAttribute(Argument::any())->willReturn(12);
        $attributeGroupMappingManager->getIdFromGroup(Argument::any(), 'barfoo')->willReturn(797);
        $webservice->addAttributeToAttributeSet(12, 414, 797)->shouldBeCalled();
        $attributeMappingManager->registerAttributeMapping($attribute, 12, 'barfoo')->shouldBeCalled();

        $this->write($attributes);
    }

    function it_increments_summary_info_if_group_already_exists(
        $webservice,
        AttributeMappingManager      $attributeMappingManager,
        AbstractAttribute            $attribute,
        AttributeGroup               $group,
        FamilyMappingManager         $familyMappingManager,
        AttributeGroupMappingManager $attributeGroupMappingManager,
        MagentoGroupManager          $magentoGroupManager,
        StepExecution                $stepExecution,
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
        $webservice->addAttributeGroupToAttributeSet('414', 'group_name')->willThrow(new SoapCallException('Group already exists.'));
        $attributeGroupMappingManager->registerGroupMapping(Argument::cetera())->shouldNotBeCalled();
        $magentoGroupManager->registerMagentoGroup(Argument::cetera())->shouldNotBeCalled();
        $stepExecution->incrementSummaryInfo('Group was already in attribute set on magento')->shouldBeCalled();

        $webservice->createAttribute(Argument::any())->willReturn(12);
        $attributeGroupMappingManager->getIdFromGroup(Argument::any(), 'barfoo')->willReturn(797);
        $webservice->addAttributeToAttributeSet(12, 414, 797)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('Attributes created')->shouldBeCalled();
        $attributeMappingManager->registerAttributeMapping($attribute, 12, 'barfoo')->shouldBeCalled();

        $this->write($attributes);
    }

//    function it_increments_summary_info_if_attribute_already_exists(
//        $webservice,
//        AbstractAttribute            $attribute,
//        StepExecution                $stepExecution,
//        AttributeMappingManager      $attributeMappingManager,
//        AttributeGroupMappingManager $attributeGroupMappingManager
//    ) {
//        $attributes = array(
//            array(
//                $attribute,
//                array(
//                    'create' => array(
//                        'attributeName' => 'attribute_code'
//                    ),
//                )
//            )
//        );
//        $this->setMagentoUrl('bar');
//        $this->setWsdlUrl('foo');
//
//        $attribute->getFamilies()->willReturn(array());
//        $attribute->getGroup()->willReturn(null);
//        $webservice->createAttribute(Argument::any())->willReturn(12);
//        $attributeGroupMappingManager->getIdFromGroup(Argument::any(), 'barfoo')->willReturn(797);
//        $webservice->addAttributeToAttributeSet(12, 414, 797)->willThrow(new SoapCallException('Attribute already exists'));
//        $stepExecution->incrementSummaryInfo('Attribute already in magento')->shouldBeCalled();
//        $attributeMappingManager->registerAttributeMapping($attribute, 12, 'barfoo')->shouldBeCalled();
//
//        $this->write($attributes);
//    }
}
