<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Webservice;

use Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidSoapUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exception\NotReachableUrlException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
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
class SoapExplorerSpec extends ObjectBehavior
{
    function let(ClientInterface $client)
    {
        $this->beConstructedWith($client);
    }

    function it_success_with_valid_soap_url(ClientInterface $client, Request $request, Response $response)
    {
        $client->createRequest('GET', 'http://myvalidsoap.url/api/soap/?wsdl')->willReturn($request);
        $client->send($request)->willReturn($response);
        $response->setHeader('ContentType', 'text/xml');
        $response->isContentType('text/xml')->willReturn(true);
        $response->getBody(true)->willReturn('<xml>Some xml as a string</xml>');
        $clientParameters = new MagentoSoapClientParameters('soapUsername', 'soapApiKey', 'http://myvalidsoap.url', '/api/soap/?wsdl');

        $this->getSoapUrlContent($clientParameters)->shouldReturn('<xml>Some xml as a string</xml>');
    }

    function it_success_with_valid_http_authentication_credentials(ClientInterface $client, Request $request, Response $response)
    {
        $client->createRequest('GET', 'http://myvalidsoap.url/api/soap/?wsdl')->willReturn($request);
        $request->setAuth('user', 'valid_credential')->willReturn($request);
        $client->send($request)->willReturn($response);
        $response->setHeader('ContentType', 'text/xml');
        $response->isContentType('text/xml')->willReturn(true);
        $response->getBody(true)->willReturn('<xml>Some xml as a string</xml>');
        $clientParameters = new MagentoSoapClientParameters('soapUsername', 'soapApiKey', 'http://myvalidsoap.url', '/api/soap/?wsdl', 'user', 'valid_credential');


        $this->getSoapUrlContent($clientParameters)->shouldReturn('<xml>Some xml as a string</xml>');
    }

    function it_fails_with_invalid_url(ClientInterface $client, Request $request)
    {
        $client->createRequest('GET', 'http://notvalidsoapurl/api/soap/?wsdl')->willReturn($request);
        $curlException = new CurlException();
        $client->send($request)->willThrow($curlException);
        $clientParameters = new MagentoSoapClientParameters('soapUsername', 'soapApiKey', 'http://notvalidsoapurl', '/api/soap/?wsdl');

        $notReachableException = new NotReachableUrlException();
        $this->shouldThrow($notReachableException)->duringGetSoapUrlContent($clientParameters);
    }

    function it_fails_with_invalid_api_soap_url(ClientInterface $client, Request $request)
    {
        $client->createRequest('GET', 'http://notvalidsoap.url/api/soap/?w')->willReturn($request);
        $badResponseException = new BadResponseException();
        $client->send($request)->willThrow($badResponseException);
        $clientParameters = new MagentoSoapClientParameters('soapUsername', 'soapApiKey', 'http://notvalidsoap.url', '/api/soap/?w');

        $invalidSoapUrlException = new InvalidSoapUrlException();
        $this->shouldThrow($invalidSoapUrlException)->duringGetSoapUrlContent($clientParameters);
    }

    function it_fails_with_invalid_http_authentication_credentials(ClientInterface $client, Request $request)
    {
        $client->createRequest('GET', 'http://myvalidsoap.url/api/soap/?wsdl')->willReturn($request);
        $request->setAuth('user', 'bad_credential')->willReturn($request);
        $badResponseException = new BadResponseException();
        $client->send($request)->willThrow($badResponseException);
        $clientParameters = new MagentoSoapClientParameters('soapUsername', 'soapApiKey', 'http://myvalidsoap.url', '/api/soap/?wsdl', 'user', 'bad_credential');

        $invalidSoapUrlException = new InvalidSoapUrlException();
        $this->shouldThrow($invalidSoapUrlException)->duringGetSoapUrlContent($clientParameters);
    }
}
