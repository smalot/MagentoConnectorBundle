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

    /** @var string $magentoSoapClientClass */
    protected $magentoSoapClientClass;

    /**
     * Constructor
     *
     * @param string $magentoSoapClientClass
     *
     * @throws \LogicException
     */
    public function __construct($magentoSoapClientClass)
    {
        $implementsInterface = in_array(
            'Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientInterface',
            class_implements($magentoSoapClientClass)
        );

        if (false === $implementsInterface) {
            throw new \LogicException(
                'Class you inject in MagentoSoapClientFactory must implement ' .
                'Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientInterface.'
            );
        }
        $this->magentoSoapClientClass = $magentoSoapClientClass;
    }

    /**
     * Create a Magento Soap client with the configuration
     *
     * @param MagentoConfiguration $configuration
     * @param array                $soapOptions
     *
     * @throws \SoapFault
     *
     * @return MagentoSoapClient
     */
    public function createMagentoSoapClient(MagentoConfiguration $configuration, array $soapOptions = [])
    {
        if (empty($soapOptions)) {
            $soapOptions = $this->getSoapOptions($configuration);
        }

        return new $this->magentoSoapClientClass($configuration->getSoapUrl(), $soapOptions);
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
