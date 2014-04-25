<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoFamilyMapping;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
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

    function it_gets_family_from_id(EntityRepository $entityRepository, MagentoFamilyMapping $familyMapping, Family $family)
    {
        $entityRepository->findOneBy(array('magentoFamilyId' => 12, 'magentoUrl' => 'magento_url'))
            ->willReturn($familyMapping);

        $familyMapping->getFamily()->willReturn($family);

        $this->getFamilyFromId(12, 'magento_url')->shouldReturn($family);
    }

    function it_shoulds_gets_null_if_family_mapping_is_not_found(EntityRepository $entityRepository, MagentoFamilyMapping $familyMapping, Family $family)
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

    function it_returns_null_if_family_is_not_found(Family $family, EntityRepository $entityRepository, $mappingCollection)
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

    function it_tests_if_a_family_exist_from_family_id(EntityRepository $entityRepository, MagentoFamilyMapping $familyMapping, Family $family)
    {
        $entityRepository->findOneBy(array('magentoFamilyId' => 12, 'magentoUrl' => 'magento_url'))
            ->willReturn($familyMapping);

        $familyMapping->getFamily()->willReturn($family);

        $this->magentoFamilyExists(12, 'magento_url')->shouldReturn(true);
    }
}
