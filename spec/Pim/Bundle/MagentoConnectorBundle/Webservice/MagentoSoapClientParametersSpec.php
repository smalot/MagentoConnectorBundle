<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Webservice;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MagentoSoapClientParametersSpec extends ObjectBehavior
{
    protected $clientParameters;

    function let(
    ) {
        $this->beConstructedWith('soapusername', 'soapapikey', 'magentourl', 'wsdlurl', 'httplogin', 'httppassword');
    }

    function it_returns_a_correct_md5_hash()
    {
        $this->getHash()->shouldReturn('fb5fdc41da78ba8368db20305f3ae840');
    }
}
