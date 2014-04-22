<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Checks;

use Pim\Bundle\MagentoConnectorBundle\Validator\Exceptions\InvalidUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exceptions\NotReachableUrlException;
use Guzzle\Service\ClientInterface;
use Guzzle\Http\Exception\CurlException;

/**
 * Check an url in different way
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UrlChecker
{
    /**
     * @var \Guzzle\Service\Client
     */
    protected $client;

    /**
     * @param \Guzzle\Service\ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Check if the given string seems to be an url
     *
     * @param string $url
     *
     * @return true
     *
     * @throws InvalidUrlException
     */
    public function checkAnUrl($url)
    {
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidUrlException();
        }

        return true;
    }

    /**
     * Check if the given URL is reachable
     *
     * @param string $url
     *
     * @return true
     *
     * @throws NotReachableUrlException
     */
    public function checkReachableUrl($url)
    {
        $request = $this->client->createRequest('GET', $url);

        try {
            $this->client->send($request);
        } catch (CurlException $ex) {
            throw new NotReachableUrlException();
        }

        return true;
    }
}
