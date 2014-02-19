<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeManagerSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, ClassMetadataFactory $classMetadataFactory, ClassMetadata $classMetadata)
    {
        $this->beConstructedWith($objectManager, 'class_name');
        $objectManager->getMetadataFactory()->willReturn($classMetadataFactory);
        $classMetadataFactory->getMetadataFor('class_name')->willReturn($classMetadata);
    }
}
