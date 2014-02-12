<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Manager\SimpleMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ORMStoreViewMapperSpec extends ObjectBehavior
{
    protected $clientParameters;

    function let(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        SimpleMappingManager $simpleMappingManager,
        LocaleManager $localeManager
    ) {
        $this->beConstructedWith($hasValidCredentialsValidator, $simpleMappingManager, 'storeview', $localeManager);
        $this->clientParameters = new MagentoSoapClientParameters('soap_user', 'soap_password', 'soap_url');
    }

    function it_shoulds_return_all_locales_from_database_as_sources($localeManager, $hasValidCredentialsValidator, Locale $locale)
    {
        $this->setParameters($this->clientParameters);
        $hasValidCredentialsValidator->areValidSoapParameters(Argument::any())->willReturn(true);

        $localeManager->getActiveLocales()->willReturn(array($locale));

        $locale->getCode()->willReturn('foo');

        $this->getAllSources()->shouldReturn(array(array('id' => 'foo', 'text' => 'foo')));
    }

    function it_shoulds_return_nothing_as_sources_if_it_is_not_well_configured()
    {
        $this->getAllSources()->shouldReturn(array());
    }
}
