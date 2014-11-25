<?php

namespace Pim\Bundle\MagentoConnectorBundle\Factory;

use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration;

/**
 * Factory to create Soap clients
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SoapClientFactory
{
    /** @staticvar string */
    const SOAP_ENCODING = 'UTF-8';

    /** @staticvar int WSDL cache both (disk and memory) */
    const CACHE_WSDL = 3;

    /** @staticvar int */
    const KEEP_ALIVE = 1;

    /**
     * Create a soap client with the configuration
     *
     * @param MagentoConfiguration $configuration
     * @param array                $soapOptions
     *
     * @throws \SoapFault
     *
     * @return \SoapClient
     */
    public function createSoapClient(MagentoConfiguration $configuration, array $soapOptions = [])
    {
        if (empty($soapOptions)) {
            $soapOptions = $this->getSoapOptions($configuration);
        }

        return new \SoapClient($configuration->getSoapUrl(), $soapOptions);
    }

    /**
     * Return soap options
     *
     * @param MagentoConfiguration $configuration
     *
     * @return array
     */
    protected function getSoapOptions(MagentoConfiguration $configuration)
    {
        return [
            'encoding'   => static::SOAP_ENCODING,
            'trace'      => true,
            'exceptions' => true,
            'login'      => $configuration->getSoapUsername(),
            'password'   => $configuration->getSoapApiKey(),
            'cache_wsdl' => static::CACHE_WSDL,
            'keep_alive' => static::KEEP_ALIVE
        ];
    }
}
