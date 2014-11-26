<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * Magento Soap client interface
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MagentoSoapClientInterface
{
    /**
     * Login with the given parameters
     *
     * @param string $username
     * @param string $soapApiKey
     *
     * @throws \SoapFault
     *
     * @return string Session token
     */
    public function login($username, $soapApiKey);
}
