<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Webservice;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\MagentoConnectorBundle\Webservice\UrlExplorer;
use Guzzle\Service\ClientInterface;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;
use Guzzle\Common\Collection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UrlExplorerSpec extends ObjectBehavior
{
    function let(
        ClientInterface $client
    )
    {
        $this->beConstructedWith($client);
    }

    function it_success_with_valid_soap_url(
        $client,
        Request $request,
        Response $response,
        Collection $curlOptions
    ){
        $clientParameters = MagentoSoapClientParametersRegistry::getInstance('soap_username', 'soap_api_key', 'http://myvalidsoap.url', '/api/soap/?wsdl', 'default');

        $guzzleParams = array(
            'connect_timeout' => UrlExplorer::CONNECT_TIMEOUT,
            'timeout'         => UrlExplorer::TIMEOUT,
            'auth'            => array($clientParameters->getHttpLogin(), $clientParameters->getHttpPassword())
        );

        $client->get('http://myvalidsoap.url/api/soap/?wsdl', array(), $guzzleParams)->willReturn($request);
        $request->getCurlOptions()->willReturn($curlOptions);
        $curlOptions->set(CURLOPT_CONNECTTIMEOUT, UrlExplorer::CONNECT_TIMEOUT)->willReturn($request);
        $curlOptions->set(CURLOPT_TIMEOUT, UrlExplorer::TIMEOUT)->willReturn($request);
        $client->send($request)->shouldBeCalled()->willReturn($response);

        $response->setHeader('ContentType', 'text/xml');
        $response->isContentType('text/xml')->shouldBeCalled()->willReturn(true);
        $response->getBody(true)->shouldBeCalled()->willReturn('<xml>Some xml as a string</xml>');

        $this->getUrlContent($clientParameters)->shouldReturn('<xml>Some xml as a string</xml>');
    }

    function it_success_with_valid_http_authentication_credentials(
        $client,
        Request $request,
        Response $response,
        Collection $curlOptions
    ){
        $clientParameters = MagentoSoapClientParametersRegistry::getInstance('soap_username', 'soap_api_key', 'http://myvalidsoap.url', '/api/soap/?wsdl', 'default', 'user', 'valid_passwd');

        $guzzleParams = array(
            'connect_timeout' => UrlExplorer::CONNECT_TIMEOUT,
            'timeout'         => UrlExplorer::TIMEOUT,
            'auth'            => array($clientParameters->getHttpLogin(), $clientParameters->getHttpPassword())
        );

        $client->get('http://myvalidsoap.url/api/soap/?wsdl', array(), $guzzleParams)->willReturn($request);
        $request->getCurlOptions()->willReturn($curlOptions);
        $curlOptions->set(CURLOPT_CONNECTTIMEOUT, UrlExplorer::CONNECT_TIMEOUT)->willReturn($request);
        $curlOptions->set(CURLOPT_TIMEOUT, UrlExplorer::TIMEOUT)->willReturn($request);
        $client->send($request)->shouldBeCalled()->willReturn($response);

        $response->setHeader('ContentType', 'text/xml');
        $response->isContentType('text/xml')->shouldBeCalled()->willReturn(true);
        $response->getBody(true)->shouldBeCalled()->willReturn('<xml>Some xml as a string</xml>');

        $this->getUrlContent($clientParameters)->shouldReturn('<xml>Some xml as a string</xml>');
    }

    function it_fails_with_invalid_url(
        $client,
        Request $request,
        Response $response,
        Collection $curlOptions
    ) {
        $clientParameters = MagentoSoapClientParametersRegistry::getInstance('soap_username', 'soap_api_key', 'http://notvalidurl', '/api/soap/?wsdl', 'default');

        $guzzleParams = array(
            'connect_timeout' => UrlExplorer::CONNECT_TIMEOUT,
            'timeout'         => UrlExplorer::TIMEOUT,
            'auth'            => array($clientParameters->getHttpLogin(), $clientParameters->getHttpPassword())
        );

        $client->get('http://notvalidurl/api/soap/?wsdl', array(), $guzzleParams)->willReturn($request);
        $request->getCurlOptions()->willReturn($curlOptions);
        $curlOptions->set(CURLOPT_CONNECTTIMEOUT, UrlExplorer::CONNECT_TIMEOUT)->willReturn($request);
        $curlOptions->set(CURLOPT_TIMEOUT, UrlExplorer::TIMEOUT)->willReturn($request);
        $client->send($request)->shouldBeCalled()->willThrow('Guzzle\Http\Exception\CurlException');

        $response->isContentType(Argument::any())->shouldNotBeCalled();
        $response->getBody(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow('\Pim\Bundle\MagentoConnectorBundle\Validator\Exception\NotReachableUrlException')->duringGetUrlContent($clientParameters);
    }

    function it_fails_with_invalid_http_authentication_credentials(
        $client,
        Request $request,
        Response $response,
        Collection $curlOptions
    ) {
        $clientParameters = MagentoSoapClientParametersRegistry::getInstance('soap_username', 'soap_api_key', 'http://myvalid.url', '/api/soap/?wsdl', 'default', 'user', 'not_valid_pwd');

        $guzzleParams = array(
            'connect_timeout' => UrlExplorer::CONNECT_TIMEOUT,
            'timeout'         => UrlExplorer::TIMEOUT,
            'auth'            => array($clientParameters->getHttpLogin(), $clientParameters->getHttpPassword())
        );

        $client->get('http://myvalid.url/api/soap/?wsdl', array(), $guzzleParams)->willReturn($request);
        $request->getCurlOptions()->willReturn($curlOptions);
        $curlOptions->set(CURLOPT_CONNECTTIMEOUT, UrlExplorer::CONNECT_TIMEOUT)->willReturn($request);
        $curlOptions->set(CURLOPT_TIMEOUT, UrlExplorer::TIMEOUT)->willReturn($request);
        $client->send($request)->shouldBeCalled()->willThrow('Guzzle\Http\Exception\BadResponseException');

        $response->isContentType(Argument::any())->shouldNotBeCalled();
        $response->getBody(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow('\Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidSoapUrlException')->duringGetUrlContent($clientParameters);
    }
}
