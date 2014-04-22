<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Validator\Checks;

use Pim\Bundle\MagentoConnectorBundle\Validator\Exceptions\InvalidUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exceptions\NotReachableUrlException;
use PhpSpec\ObjectBehavior;
use Guzzle\Service\ClientInterface;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UrlCheckerSpec extends ObjectBehavior
{
    function let(ClientInterface $client)
    {
        $this->beConstructedWith($client);
    }

    function it_should_not_allow_invalid_url()
    {
        $e = new InvalidUrlException();

        $this->shouldThrow($e)->duringCheckAnUrl('notAnUrl', '\n0t/url@', 'http://n0turl4t4ll', 'myurl.url');
    }

    function it_should_success_with_valid_url()
    {
        $this->checkAnUrl('http://valid.url')->shouldReturn(true);
    }

    function it_should_fail_if_url_is_not_reachable(ClientInterface $client, Request $request)
    {
        $curlException = new CurlException();
        $notReachableException = new NotReachableUrlException();

        $client->createRequest('GET', 'http://notvalidurl')->willReturn($request);
        $client->send($request)->willThrow($curlException);

        $this->shouldThrow($notReachableException)->duringCheckReachableUrl('http://notvalidurl');
    }

    function it_should_success_if_url_is_reachable(ClientInterface $client, Request $request, Response $response)
    {
        $client->createRequest('GET', 'http://valid.url')->willReturn($request);
        $client->send($request)->willReturn($response);

        $this->checkReachableUrl('http://valid.url')->shouldReturn(true);
    }
}
