<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AssociationTypeManagerSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, EntityRepository $entityRepository)
    {
        $this->beConstructedWith($objectManager, 'class_name');
        $objectManager->getRepository('class_name')->willReturn($entityRepository);
    }

    function it_gets_association_type_from_repository($entityRepository)
    {
        $entityRepository->findBy(array())->willReturn(array('foo'));

        $this->getAssociationTypes()->shouldReturn(array('foo'));
    }

    function it_gets_association_type_from_repository_by_code($entityRepository)
    {
        $entityRepository->findBy(array('code' => 'foo'))->willReturn(array('foo'));

        $this->getAssociationTypesByCode('foo')->shouldReturn(array('foo'));
    }

    function it_gets_association_type_choices_from_repository($entityRepository, AssociationType $associationType)
    {
        $entityRepository->findBy(array())->willReturn(array($associationType));
        $associationType->getCode()->willReturn('foo');
        $associationType->getLabel()->willReturn('Foo');

        $this->getAssociationTypeChoices()->shouldReturn(array('foo' => 'Foo'));
    }
}
