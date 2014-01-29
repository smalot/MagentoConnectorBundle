<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class OptionWriterSpec extends ObjectBehavior
{
    function let(
        ChannelManager $channelManager,
        WebserviceGuesser $webserviceGuesser,
        Webservice $webservice
    ) {
        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);

        $this->beConstructedWith($channelManager, $webserviceGuesser);
    }

    function it_calls_soap_client_to_create_options($webservice)
    {
        $webservice->createOption(array('foo'))->shouldBeCalled();
        $webservice->createOption(array('bar'))->shouldBeCalled();

        $this->write(array(array(array('foo'), array('bar'))));
    }
}
