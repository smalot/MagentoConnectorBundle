<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Repository\LocaleRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LocaleManagerSpec extends ObjectBehavior
{
    function let(LocaleRepository $localeRepository)
    {
        $this->beConstructedWith($localeRepository);
    }

    function it_gives_currency_choices($localeRepository, Locale $locale)
    {
        $localeRepository->getActivatedLocales()->willReturn([$locale]);
        $locale->getCode()->willReturn('en_US');

        $this->getLocaleChoices()->shouldReturn(['en_US' => 'en_US']);
    }
}
