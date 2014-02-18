<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AssociationTypeManagerSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, EntityRepository $entityRepository, ArrayCollection $arrayCollection)
    {
        $this->beConstructedWith($objectManager, 'class_name');
        $objectManager->getRepository('class_name')->willReturn($entityRepository);
    }

    function it_gets_association_type_from_repository($entityRepository, $arrayCollection)
    {
        $entityRepository->findBy(array())->willReturn($arrayCollection);

        $this->getAssociationTypes()->shouldReturn($arrayCollection);
    }

    function it_gets_association_type_from_repository_by_code($entityRepository, AssociationType $associationType, $arrayCollection)
    {
        $entityRepository->findOneBy(array('code' => 'foo'))->willReturn($arrayCollection);

        $arrayCollection->first()->willReturn($associationType);

        $this->getAssociationTypeByCode('foo')->shouldReturn($associationType);
    }

    function it_gets_association_type_choices_from_repository($entityRepository, AssociationType $associationType)
    {
        $entityRepository->findBy(array())->willReturn(array($associationType));
        $associationType->getCode()->willReturn('foo');
        $associationType->getLabel()->willReturn('Foo');

        $this->getAssociationTypeChoices()->shouldReturn(array('foo' => 'Foo'));
    }
}
