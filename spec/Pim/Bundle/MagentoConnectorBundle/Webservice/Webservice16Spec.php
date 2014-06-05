<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Webservice;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use PhpSpec\ObjectBehavior;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Webservice16Spec extends ObjectBehavior
{
    function let(MagentoSoapClient $client)
    {
        $this->beConstructedWith($client);
    }

    function it_gives_an_array_when_you_ask_store_views_list()
    {
        $this->getStoreViewsList()->shouldReturn([]);
    }
}
