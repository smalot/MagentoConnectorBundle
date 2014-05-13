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
class UrlExplorerSpec extends ObjectBehavior
{
    function let(
        ClientInterface $client,
        MagentoSoapClientParameters $clientParameters
    )
    {
        $this->beConstructedWith($client);
    }

    function it_success_with_valid_soap_url(
        ClientInterface $client,
        Request $request,
        Response $response,
        MagentoSoapClientParameters $clientParameters
    ){
        $client->createRequest('GET', 'http://myvalidsoap.url/api/soap/?wsdl')->willReturn($request);
        $client->send($request)->willReturn($response);
        $response->setHeader('ContentType', 'text/xml');
        $response->isContentType('text/xml')->willReturn(true);
        $response->getBody(true)->willReturn('<xml>Some xml as a string</xml>');
        $clientParameters->getSoapUsername()->willReturn('soapUsername');
        $clientParameters->getSoapApiKey()->willReturn('soapApiKey');
        $clientParameters->getMagentoUrl()->willReturn('http://myvalidsoap.url');
        $clientParameters->getSoapUrl()->willReturn('http://myvalidsoap.url/api/soap/?wsdl');
        $clientParameters->getHttpLogin()->willReturn('');
        $clientParameters->getHttpPassword()->willReturn('');
        $clientParameters->getHash()->willReturn('');

        $this->getUrlContent($clientParameters)->shouldReturn('<xml>Some xml as a string</xml>');
    }

    function it_success_with_valid_http_authentication_credentials(
        ClientInterface $client,
        Request $request,
        Response $response,
        MagentoSoapClientParameters $clientParameters
    ){
        $client->createRequest('GET', 'http://myvalidsoap.url/api/soap/?wsdl')->willReturn($request);
        $request->setAuth('user', 'valid_credential')->willReturn($request);
        $client->send($request)->willReturn($response);
        $response->setHeader('ContentType', 'text/xml');
        $response->isContentType('text/xml')->willReturn(true);
        $response->getBody(true)->willReturn('<xml>Some xml as a string</xml>');
        $clientParameters->getSoapUsername()->willReturn('soapUsername');
        $clientParameters->getSoapApiKey()->willReturn('soapApiKey');
        $clientParameters->getMagentoUrl()->willReturn('http://myvalidsoap.url');
        $clientParameters->getSoapUrl()->willReturn('http://myvalidsoap.url/api/soap/?wsdl');
        $clientParameters->getHttpLogin()->willReturn('user');
        $clientParameters->getHttpPassword()->willReturn('valid_credential');
        $clientParameters->getHash()->willReturn('');


        $this->getUrlContent($clientParameters)->shouldReturn('<xml>Some xml as a string</xml>');
    }

    function it_fails_with_invalid_url(ClientInterface $client, Request $request, MagentoSoapClientParameters $clientParameters)
    {
        $client->createRequest('GET', 'http://notvalidsoapurl/api/soap/?wsdl')->willReturn($request);
        $curlException = new CurlException();
        $client->send($request)->willThrow($curlException);
        $clientParameters->getSoapUsername()->willReturn('soapUsername');
        $clientParameters->getSoapApiKey()->willReturn('soapApiKey');
        $clientParameters->getMagentoUrl()->willReturn('http://notvalidsoapurl');
        $clientParameters->getSoapUrl()->willReturn('http://notvalidsoapurl/api/soap/?wsdl');
        $clientParameters->getHttpLogin()->willReturn('');
        $clientParameters->getHttpPassword()->willReturn('');
        $clientParameters->getHash()->willReturn('');

        $notReachableException = new NotReachableUrlException();
        $this->shouldThrow($notReachableException)->duringGetUrlContent($clientParameters);
    }

    function it_fails_with_invalid_api_soap_url(
        ClientInterface $client,
        Request $request,
        MagentoSoapClientParameters $clientParameters
    ){
        $client->createRequest('GET', 'http://notvalidsoapurl/api/soap/?wsdl')->willReturn($request);
        $badResponseException = new BadResponseException();
        $client->send($request)->willThrow($badResponseException);
        $clientParameters->getSoapUsername()->willReturn('soapUsername');
        $clientParameters->getSoapApiKey()->willReturn('soapApiKey');
        $clientParameters->getMagentoUrl()->willReturn('http://notvalidsoapurl');
        $clientParameters->getSoapUrl()->willReturn('http://notvalidsoapurl/api/soap/?wsdl');
        $clientParameters->getHttpLogin()->willReturn('');
        $clientParameters->getHttpPassword()->willReturn('');
        $clientParameters->getHash()->willReturn('');

        $invalidSoapUrlException = new InvalidSoapUrlException();
        $this->shouldThrow($invalidSoapUrlException)->duringGetUrlContent($clientParameters);
    }

    function it_fails_with_invalid_http_authentication_credentials(
        ClientInterface $client,
        Request $request,
        MagentoSoapClientParameters $clientParameters
    ){
        $client->createRequest('GET', 'http://myvalidsoap.url/api/soap/?wsdl')->willReturn($request);
        $request->setAuth('user', 'bad_credential')->willReturn($request);
        $badResponseException = new BadResponseException();
        $client->send($request)->willThrow($badResponseException);
        $clientParameters->getSoapUsername()->willReturn('soapUsername');
        $clientParameters->getSoapApiKey()->willReturn('soapApiKey');
        $clientParameters->getMagentoUrl()->willReturn('http://myvalidsoap.url');
        $clientParameters->getSoapUrl()->willReturn('http://myvalidsoap.url/api/soap/?wsdl');
        $clientParameters->getHttpLogin()->willReturn('user');
        $clientParameters->getHttpPassword()->willReturn('bad_credential');
        $clientParameters->getHash()->willReturn('');


        $invalidSoapUrlException = new InvalidSoapUrlException();
        $this->shouldThrow($invalidSoapUrlException)->duringGetUrlContent($clientParameters);
    }
}
