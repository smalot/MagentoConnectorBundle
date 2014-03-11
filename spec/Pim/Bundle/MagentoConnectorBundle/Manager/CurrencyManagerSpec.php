<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\CatalogBundle\Entity\Repository\CurrencyRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CurrencyManagerSpec extends ObjectBehavior
{
    function let(CurrencyRepository $currencyRepository)
    {
        $this->beConstructedWith($currencyRepository);
    }

    function it_gives_currency_choices(CurrencyRepository $currencyRepository, Currency $currency)
    {
        $currencyRepository->findBy(array('activated' => true))->willReturn(array($currency));
        $currency->getCode()->willReturn('EUR');

        $this->getCurrencyChoices()->shouldReturn(array('EUR' => 'EUR'));
    }
}
