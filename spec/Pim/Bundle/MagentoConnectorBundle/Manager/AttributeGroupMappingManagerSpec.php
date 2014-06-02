<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoGroupMapping;
use Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection;
use Pim\Bundle\CatalogBundle\Entity\Family;
use PhpSpec\ObjectBehavior;

class AttributeGroupMappingManagerSpec extends ObjectBehavior
{
    public function let(
        ObjectManager $objectManager,
        EntityRepository $entityRepository,
        Family $family,
        AttributeGroup $group
    ) {
        $this->beConstructedWith($objectManager, 'Pim\Bundle\MagentoConnectorBundle\Entity\MagentoAttributeMapping');
        $objectManager->getRepository('Pim\Bundle\MagentoConnectorBundle\Entity\MagentoAttributeMapping')
            ->willReturn($entityRepository);

        $group->getCode()->willReturn(12);
        $family->getCode()->willReturn(5);
    }

    public function it_return_id_from_group(
        $entityRepository,
        MagentoGroupMapping $magentoGroupMapping,
        $family,
        $group
    ) {
        $entityRepository->findOneBy(array('pimGroupCode' => 12, 'pimFamilyCode' => 5, 'magentoUrl' => 'magento_url'))
            ->willReturn($magentoGroupMapping);

        $magentoGroupMapping->getMagentoGroupId()->willReturn(4);

        $this->getIdFromGroup($group, $family, 'magento_url')->shouldReturn(4);
    }

    public function it_gets_null_if_attribute_group_mapping_is_not_found(
        $entityRepository,
        MagentoGroupMapping $magentoGroupMapping,
        $family,
        $group
    ) {
        $entityRepository->findOneBy(array('pimGroupCode' => 12, 'pimFamilyCode' => 5, 'magentoUrl' => 'magento_url'))
            ->willReturn(null);

        $magentoGroupMapping->getMagentoGroupId()->shouldNotBeCalled();

        $this->getIdFromGroup($group, $family, 'magento_url')->shouldReturn(null);
    }

    public function it_return_all_mappings(
        $entityRepository,
        MagentoGroupMapping $magentoGroupMapping
    ) {
        $entityRepository->findAll()->willReturn(array($magentoGroupMapping));
        $this->getAllMappings()->shouldReturn(array($magentoGroupMapping));
    }

    public function it_register_mapping(
        $entityRepository,
        MagentoGroupMapping $magentoGroupMapping,
        $group,
        $family,
        $objectManager
    ) {
        $entityRepository->findOneBy(array('pimGroupCode' => 12, 'pimFamilyCode' => 5))->willReturn($magentoGroupMapping);
        $magentoGroupMapping->setPimGroupCode(12)->shouldBeCalled();
        $magentoGroupMapping->setPimFamilyCode(5)->shouldBeCalled();
        $magentoGroupMapping->setMagentoGroupId(3)->shouldBeCalled();
        $magentoGroupMapping->setMagentoUrl('url')->shouldBeCalled();

        $objectManager->persist($magentoGroupMapping)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->registerGroupMapping($group, $family, 3, 'url');
    }

    public function it_return_null_if_no_mappings_found(
        $entityRepository
    ) {
        $entityRepository->findAll()->willReturn(array());
        $this->getAllMappings()->shouldReturn(null);
    }

}
