<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GroupManagerSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        ClassMetadataFactory $classMetadataFactory,
        ClassMetadata $classMetadata,
        RegistryInterface $doctrine
    ){
        $this->beConstructedWith($doctrine, 'product_class_name', 'attribute_class_name', 'class_name');

        $doctrine->getEntityManager()->willReturn($objectManager);
        $objectManager->getMetadataFactory()->willReturn($classMetadataFactory)->shouldBeCalled();
        $classMetadataFactory->getMetadataFor('class_name')->willReturn($classMetadata)->shouldBeCalled();
    }
}
