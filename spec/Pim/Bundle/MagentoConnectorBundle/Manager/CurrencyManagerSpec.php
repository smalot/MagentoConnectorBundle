<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CurrencyManagerSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, EntityRepository $entityRepository)
    {
        $this->beConstructedWith($objectManager);
        $objectManager->getRepository('PimCatalogBundle:Currency')->willReturn($entityRepository);
    }

    function it_gives_currency_choices($entityRepository, Currency $currency)
    {
        $entityRepository->findBy(array('activated' => true))->willReturn(array($currency));
        $currency->getCode()->willReturn('EUR');

        $this->getCurrencyChoices()->shouldReturn(array('EUR' => 'EUR'));
    }
}
