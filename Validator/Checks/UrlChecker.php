<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Checks;

use Pim\Bundle\MagentoConnectorBundle\Validator\Exceptions\InvalidUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exceptions\NotReachableUrlException;

/**
 * Check an url
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UrlChecker
{
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
     * Check if the given URL return a 200 http status
     *
     * @param string $url
     *
     * @return true
     *
     * @throws NotReachableUrlException
     */
    public function checkReachableUrl($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        if (false === $output) {
            throw new NotReachableUrlException();
        }

        $header = explode('Date:', $output, 2);

        if (false === strpos($header[0], '200')) {
            throw new NotReachableUrlException();
        }

        return true;
    }
}
