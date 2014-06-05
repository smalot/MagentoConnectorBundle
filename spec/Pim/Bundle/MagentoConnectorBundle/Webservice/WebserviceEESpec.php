<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Webservice;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebserviceEESpec extends ObjectBehavior
{
    function let(MagentoSoapClient $client)
    {
        $this->beConstructedWith($client);
    }

    function it_gives_attributes_options($client)
    {
        $client->call(Argument::cetera())->willReturn([['label' => 'label1', 'value' => 'value1'], ['label' => 'label2', 'value' => 'value2']]);

        $this->getAttributeOptions('code')->shouldReturn(['label1' => 'value1', 'label2' => 'value2']);
    }

    function it_returns_an_empty_array_if_attribute_is_returnable($client){
        $client->call(Argument::cetera())->shouldNotBeCalled();

        $this->getAttributeOptions('is_returnable')->shouldReturn(array());
    }
}
