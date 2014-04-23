<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Checks;

use Pim\Bundle\MagentoConnectorBundle\Validator\Exceptions\InvalidSoapUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exceptions\NotReachableUrlException;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Service\ClientInterface;

/**
 * Tool in the goal to check your soap url
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SoapChecker
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }
    /**
     * Check the soap url
     *
     * @param string $soapUrl
     *
     * @return string Xml as string
     *
     * @throws NotReachableUrlException
     * @throws InvalidSoapUrlException
     */
    public function checkSoapUrl($soapUrl)
    {
        $request = $this->client->createRequest('GET', $soapUrl);

        try {
            $response = $this->client->send($request);
        } catch (CurlException $e) {
            throw new NotReachableUrlException;
        } catch (BadResponseException $e) {
            throw new InvalidSoapUrlException();
        }

        if (false === $response->isContentType('text/xml')) {
            throw new InvalidSoapUrlException();
        }

        return $response->getBody(true);
    }
}
