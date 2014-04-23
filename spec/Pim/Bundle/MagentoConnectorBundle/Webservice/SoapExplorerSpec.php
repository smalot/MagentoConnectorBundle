<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Validator\Checks;

use Pim\Bundle\MagentoConnectorBundle\Validator\Exceptions\InvalidSoapUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exceptions\NotReachableUrlException;
use PhpSpec\ObjectBehavior;

use Guzzle\Service\ClientInterface;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SoapCheckerSpec extends ObjectBehavior
{
    function let(ClientInterface $client)
    {
        $this->beConstructedWith($client);
    }

    function it_should_success_with_valid_soap_url(ClientInterface $client, Request $request, Response $response)
    {
        $client->createRequest('GET', 'http://myvalidsoap.url/api/soap/?wsdl')->willReturn($request);
        $client->send($request)->willReturn($response);
        $response->setHeader('ContentType', 'text/xml');
        $response->isContentType('text/xml')->willReturn(true);
        $response->getBody(true)->willReturn('<xml>Some xml as a string</xml>');

        $this->checkSoapUrl('http://myvalidsoap.url/api/soap/?wsdl')->shouldReturn('<xml>Some xml as a string</xml>');
    }

    function it_should_failed_with_invalid_url(ClientInterface $client, Request $request)
    {
        $client->createRequest('GET', 'http://notvalidsoapurl/api/soap/?wsdl')->willReturn($request);
        $curlException = new CurlException();
        $client->send($request)->willThrow($curlException);

        $notReachableException = new NotReachableUrlException();
        $this->shouldThrow($notReachableException)->duringCheckSoapUrl('http://notvalidsoapurl/api/soap/?wsdl');
    }

    function it_should_fail_with_invalid_api_soap_url(ClientInterface $client, Request $request)
    {
        $client->createRequest('GET', 'http://notvalidsoap.url/api/soap/?w')->willReturn($request);
        $badResponseException = new BadResponseException();
        $client->send($request)->willThrow($badResponseException);

        $invalidSoapUrlException = new InvalidSoapUrlException();
        $this->shouldThrow($invalidSoapUrlException)->duringCheckSoapUrl('http://notvalidsoap.url/api/soap/?w');
    }
}
