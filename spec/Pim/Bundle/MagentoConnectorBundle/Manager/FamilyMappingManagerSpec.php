<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoFamilyMapping;
use Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FamilyMappingManagerSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, EntityRepository $entityRepository, MappingCollection $mappingCollection)
    {
        $this->beConstructedWith($objectManager, 'Pim\Bundle\MagentoConnectorBundle\Entity\MagentoFamilyMapping');
        $objectManager->getRepository('Pim\Bundle\MagentoConnectorBundle\Entity\MagentoFamilyMapping')
            ->willReturn($entityRepository);

        $mappingCollection->getTarget('default')->willReturn(12);
    }

    function it_gets_family_from_id($entityRepository, MagentoFamilyMapping $familyMapping, Family $family)
    {
        $entityRepository->findOneBy(array('magentoFamilyId' => 12, 'magentoUrl' => 'magento_url'))
            ->willReturn($familyMapping);

        $familyMapping->getFamily()->willReturn($family);

        $this->getFamilyFromId(12, 'magento_url')->shouldReturn($family);
    }

    function it_returns_null_if_family_mapping_is_not_found($entityRepository, MagentoFamilyMapping $familyMapping, Family $family)
    {
        $entityRepository->findOneBy(array('magentoFamilyId' => 12, 'magentoUrl' => 'magento_url'))
            ->willReturn(null);

        $this->getFamilyFromId(12, 'magento_url')->shouldReturn(null);
    }

    function it_gets_id_from_family_mapping_stored_in_database(Family $family, $entityRepository, MagentoFamilyMapping $familyMapping, $mappingCollection)
    {
        $entityRepository->findOneBy(
            array(
                'family'   => $family,
                'magentoUrl' => ''
            )
        )->willReturn($familyMapping);

        $familyMapping->getMagentoFamilyId()->willReturn(13);

        $mappingCollection->getTarget('colors')->willReturn('colors');

        $family->getCode()->willReturn('colors');

        $this->getIdFromFamily($family, '', $mappingCollection)->shouldReturn(13);
    }

    function it_returns_null_if_family_is_not_found($entityRepository, $mappingCollection, Family $family)
    {
        $entityRepository->findOneBy(
            array(
                'family'   => $family,
                'magentoUrl' => ''
            )
        )->willReturn(null);

        $mappingCollection->getTarget('colors')->willReturn('colors');

        $family->getCode()->willReturn('colors');

        $this->getIdFromFamily($family, '', $mappingCollection)->shouldReturn(null);
    }

    function it_tests_if_a_family_exist_from_family_id($entityRepository, MagentoFamilyMapping $familyMapping, Family $family)
    {
        $entityRepository->findOneBy(array('magentoFamilyId' => 12, 'magentoUrl' => 'magento_url'))
            ->willReturn($familyMapping);

        $familyMapping->getFamily()->willReturn($family);

        $this->magentoFamilyExists(12, 'magento_url')->shouldReturn(true);
    }

    function it_registers_a_family_mapping(
        $objectManager,
        $entityRepository,
        MagentoFamilyMapping $familyMapping,
        Family $pimFamily
    ) {
        $pimFamily->getId()->willReturn(12);

        $entityRepository->findOneBy(array('family' => 12))->shouldBeCalled()->willReturn($familyMapping);

        $familyMapping->setFamily($pimFamily)->shouldBeCalled();
        $familyMapping->setMagentoFamilyId(12)->shouldBeCalled();
        $familyMapping->setMagentoUrl('magento_url')->shouldBeCalled();

        $objectManager->persist($familyMapping)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->registerFamilyMapping($pimFamily, 12, 'magento_url');
    }
}
