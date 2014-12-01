<?php

namespace Pim\Bundle\MagentoConnectorBundle\Factory;

use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;

/**
 * Factory to create Magento Soap clients
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClientFactory
{
    /** @staticvar string */
    const SOAP_ENCODING = 'UTF-8';

    /** @staticvar int WSDL cache both (disk and memory) */
    const CACHE_WSDL = 3;

    /** @staticvar int */
    const KEEP_ALIVE = 1;

    /** @var string $soapClientClass */
    protected $soapClientClass;

    /**
     * Constructor
     *
     * @param string $soapClientClass
     */
    public function __construct($soapClientClass)
    {
        $this->soapClientClass = $soapClientClass;
    }

    /**
     * Create a Magento Soap client with the configuration
     *
     * @param MagentoConfiguration $configuration    The Magento configuration
     * @param array                $soapOptionsParam SOAP options you want to override
     *
     * @throws \SoapFault
     *
     * @return MagentoSoapClient
     */
    public function createMagentoSoapClient(MagentoConfiguration $configuration, array $soapOptionsParam = [])
    {
        $soapOptions = $this->getSoapOptions($configuration);

        if (!empty($soapOptionsParam)) {
            $soapOptions = array_merge($soapOptions, $soapOptionsParam);
        }

        return new $this->soapClientClass($configuration->getSoapUrl(), $soapOptions);
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
