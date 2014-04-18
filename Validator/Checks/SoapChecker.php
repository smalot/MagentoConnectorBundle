<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Checks;

use Pim\Bundle\MagentoConnectorBundle\Validator\Exceptions\InvalidSoapUrlException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SoapChecker
{
    /**
     * Check the soap url
     *
     * @param string $soapUrl
     *
     * @return string
     *
     * @throws InvalidSoapUrl
     */
    public function checkSoapUrl($soapUrl)
    {
        $curl = curl_init($soapUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        if (false === $output) {
            throw new InvalidSoapUrlException();
        }

        return $output;
    }
}
