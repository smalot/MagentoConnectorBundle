<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoGroup;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoGroupManagerSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, MagentoGroup $magentoGroup, EntityRepository $entityRepository)
    {
        $this->beConstructedWith($objectManager, $magentoGroup);
        $objectManager->getRepository(Argument::any())->willReturn($entityRepository);
    }

    function it_gives_magento_group_from_id(
        $magentoGroup,
        $entityRepository
    ) {
        $entityRepository->findOneBy(array('magentoGroupId' => 2, 'magentoUrl' => 'http://magento.url'))->shouldBeCalled()->willReturn($magentoGroup);

        $this->getMagentoGroupFromId(2, 'http://magento.url')->shouldReturn($magentoGroup);
    }

    function it_returns_null_if_no_magento_group_were_found(
        $entityRepository
    ) {
        $entityRepository->findOneBy(array('magentoGroupId' => 2, 'magentoUrl' => 'http://magento.url'))->shouldBeCalled()->willReturn(null);

        $this->getMagentoGroupFromId(2, 'http://magento.url')->shouldReturn(null);
    }

    function it_registers_a_magento_group(
        $objectManager,
        $magentoGroup,
        $entityRepository
    ) {
        $entityRepository->findOneBy(array('magentoGroupId' => 2, 'magentoUrl' => 'http://magento.url'))->shouldBeCalled()->willReturn($magentoGroup);

        $magentoGroup->setMagentoGroupId(2)->shouldBeCalled();
        $magentoGroup->setMagentoUrl('http://magento.url')->shouldBeCalled();

        $objectManager->persist($magentoGroup)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->registerMagentoGroup(2, 'http://magento.url');
    }

    function it_registers_new_magento_group_if_it_is_not_found(
        $objectManager,
        $entityRepository
    ) {
        $entityRepository->findOneBy(array('magentoGroupId' => 2, 'magentoUrl' => 'http://magento.url'))->shouldBeCalled()->willReturn(null);

        $objectManager->persist(Argument::type('\Pim\Bundle\MagentoConnectorBundle\Entity\MagentoGroup'))->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->registerMagentoGroup(2, 'http://magento.url');
    }

    function it_removes_a_magento_group(
        $objectManager,
        $magentoGroup,
        $entityRepository
    ) {
        $entityRepository->findOneBy(array('magentoGroupId' => 2, 'magentoUrl' => 'http://magento.url'))->shouldBeCalled()->willReturn($magentoGroup);

        $objectManager->remove($magentoGroup)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->removeMagentoGroup(2, 'http://magento.url');
    }

    function it_returns_false_if_magento_family_doesnt_exist($entityRepository)
    {
        $entityRepository->findOneBy(array('magentoGroupId' => 2, 'magentoUrl' => 'htpp://magento.url'))->willReturn(null);

        $this->magentoFamilyExists('2', 'htpp://magento.url')->shouldReturn(false);
    }

    function it_returns_true_if_magento_family_exists($entityRepository, $magentoGroup)
    {
        $entityRepository->findOneBy(array('magentoGroupId' => 2, 'magentoUrl' => 'htpp://magento.url'))->willReturn($magentoGroup);

        $this->magentoFamilyExists('2', 'htpp://magento.url')->shouldReturn(true);
    }

    function it_gives_all_magento_groups($entityRepository, $groups = array())
    {
        $entityRepository->findAll()->shouldBeCalled()->willReturn($groups);
        $this->getAllMagentoGroups()->shouldReturn($groups);
    }
}
