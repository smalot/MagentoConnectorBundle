<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoAttributeMapping;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Prophecy\Argument;

class AttributeMappingManagerSpec extends ObjectBehavior
{
    public function let(
        ObjectManager $objectManager,
        EntityRepository $entityRepository,
        Attribute $attribute
    ) {
        $this->beConstructedWith($objectManager, 'Pim\Bundle\MagentoConnectorBundle\Entity\MagentoAttributeMapping');
        $objectManager->getRepository('Pim\Bundle\MagentoConnectorBundle\Entity\MagentoAttributeMapping')
            ->willReturn($entityRepository);

        $attribute->getCode()->willReturn(12);
    }

    public function it_return_attribute_from_id(
        EntityRepository $entityRepository,
        MagentoAttributeMapping $magentoAttributeMapping,
        $attribute
    ) {
        $entityRepository->findOneBy(['magentoAttributeId' => 12,'magentoUrl' => 'url'])
            ->willReturn($magentoAttributeMapping);

        $magentoAttributeMapping->getAttribute()->willReturn($attribute);

        $this->getAttributeFromId(12, 'url')->shouldReturn($attribute);
    }

    public function it_return_null_if_no_attribute_are_found_from_id(
        EntityRepository $entityRepository
    ) {
        $entityRepository->findOneBy(['magentoAttributeId' => 12,'magentoUrl' => 'url'])
            ->willReturn(null);

        $this->getAttributeFromId(12, 'url')->shouldReturn(null);
    }

    public function it_return_id_from_attribute(
        EntityRepository $entityRepository,
        MagentoAttributeMapping $magentoAttributeMapping,
        $attribute
    ) {
        $entityRepository->findOneBy(['attribute' => $attribute,'magentoUrl' => 'url'])
            ->willReturn($magentoAttributeMapping);

        $magentoAttributeMapping->getMagentoAttributeId()->willReturn(3);

        $this->getIdFromAttribute($attribute, 'url')->shouldReturn(3);
    }

    public function it_return_null_if_no_id_are_found_from_attribute(
        EntityRepository $entityRepository,
        $attribute
    ) {
        $entityRepository->findOneBy(['attribute' => $attribute,'magentoUrl' => 'url'])
            ->willReturn(null);

        $this->getIdFromAttribute($attribute, 'url')->shouldReturn(null);
    }

    public function it_return_all_magento_attribute(
        EntityRepository $entityRepository,
        MagentoAttributeMapping $magentoAttributeMapping
    ) {
        $entityRepository->findAll(['magentoUrl' => 'url'])
            ->shouldBeCalled()
            ->willReturn([$magentoAttributeMapping]);

        $this->getAllMagentoAttribute('url')->shouldReturn([$magentoAttributeMapping]);
    }

    function it_register_mapping(
        EntityRepository $entityRepository,
        MagentoAttributeMapping $magentoAttributeMapping,
        $attribute,
        $objectManager
) {
        $entityRepository->findOneBy(['attribute' => $attribute])->willReturn($magentoAttributeMapping);
        $magentoAttributeMapping->setAttribute($attribute);
        $magentoAttributeMapping->setMagentoAttributeId(12);
        $magentoAttributeMapping->setMagentoUrl('url');

        $objectManager->persist($magentoAttributeMapping)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->registerAttributeMapping($attribute, 12, 'url');
    }

    public function it_return_true_when_magento_attribute_exists(
        EntityRepository $entityRepository,
        MagentoAttributeMapping $magentoAttributeMapping,
        $attribute
    ) {
        $entityRepository->findOneBy(['magentoAttributeId' => 12,'magentoUrl' => 'url'])
            ->willReturn($magentoAttributeMapping);

        $magentoAttributeMapping->getAttribute()->willReturn($attribute);

        $this->magentoAttributeExists(12, 'url')->shouldReturn(true);
    }

    function it_return_false_when_magento_attribute_does_not_exist(
        EntityRepository $entityRepository,
        MagentoAttributeMapping $magentoAttributeMapping,
        $attribute
    ){
        $entityRepository->findOneBy(['magentoAttributeId' => 12,'magentoUrl' => 'url'])
            ->willReturn(null);

        $this->magentoAttributeExists(12, 'url')->shouldReturn(false);
    }
}
