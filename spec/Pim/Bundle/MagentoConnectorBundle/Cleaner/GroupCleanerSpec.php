<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoGroup;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeGroupMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoGroupManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GroupCleanerSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser            $webserviceGuesser,
        MagentoGroupManager          $magentoGroupManager,
        AttributeGroupMappingManager $attributeGroupMappingManager,
        Webservice                   $webservice,
        StepExecution                $stepExecution
    ) {
        $this->beConstructedWith($webserviceGuesser, $magentoGroupManager, $attributeGroupMappingManager);
        $this->setStepExecution($stepExecution);

        $webserviceGuesser->getWebservice(Argument::cetera())->willReturn($webservice);
    }

    function it_asks_soap_client_to_delete_groups_that_are_not_in_pim_anymore(
        $webservice,
        AttributeGroupMappingManager $attributeGroupMappingManager,
        MagentoGroupManager $magentoGroupManager,
        AttributeGroup      $attributeGroup,
        MagentoGroup        $group
    ) {
        $group->getMagentoGroupId()->willReturn(5);
        $magentoGroupManager->getAllMagentoGroups()->willReturn(array($group));
        $attributeGroupMappingManager->getGroupFromId(5, Argument::any())->shouldBeCalled()->WillReturn($attributeGroup);
        $attributeGroup->getCode()->shouldBeCalled();
        $attributeGroupMappingManager->magentoGroupExists(5, Argument::any())->shouldBeCalled()->willReturn(false);

        $webservice->removeAttributeGroupFromAttributeSet(5)->shouldBeCalled();
        $magentoGroupManager->removeMagentoGroup(5, Argument::any())->shouldBeCalled();

        $this->execute();
    }

    function it_asks_soap_client_to_delete_groups_that_should_be_ignored(
        $webservice,
        AttributeGroupMappingManager $attributeGroupMappingManager,
        MagentoGroupManager          $magentoGroupManager,
        AttributeGroup               $attributeGroup,
        MagentoGroup                 $group
    ) {
        $group->getMagentoGroupId()->willReturn(4);
        $magentoGroupManager->getAllMagentoGroups()->willReturn(array($group));
        $attributeGroupMappingManager->getGroupFromId(4, Argument::any())->shouldBeCalled()->WillReturn($attributeGroup);
        $attributeGroupMappingManager->magentoGroupExists(4, Argument::any())->shouldBeCalled()->willReturn(false);
        $attributeGroup->getCode()->shouldBeCalled()->willReturn('Default');

        $webservice->removeAttributeGroupFromAttributeSet(4)->shouldNotBeCalled();
        $magentoGroupManager->removeMagentoGroup(4, Argument::any())->shouldNotBeCalled();

        $this->execute();
    }
}
