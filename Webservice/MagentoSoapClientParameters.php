<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * Magento soap client parameters
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClientParameters
{
    const SOAP_WSDL_URL = '/api/soap/?wsdl';

    /**
     * @var string Soap Username
     */
    protected $soapUsername;

    /**
     * @var string Soap Api Key
     */
    protected $soapApiKey;

    /**
     * @var string Wsdl extension
     */
    protected $wsdlUrl;

    /**
     * @var string Magento Url (only the domain)
     */
    protected $magentoUrl;

    /**
     * Constructor
     *
     * @param string $soapUsername Magento soap username
     * @param string $soapApiKey   Magento soap api key
     * @param string $magentoUrl   Magento url (only the domain)
     * @param string $wsdlUrl      Only wsdl soap api extension
     */
    public function __construct($soapUsername, $soapApiKey, $magentoUrl, $wsdlUrl)
    {
        $this->soapUsername = $soapUsername;
        $this->soapApiKey   = $soapApiKey;
        $this->magentoUrl   = $magentoUrl;
        $this->wsdlUrl      = $wsdlUrl;
    }

    /**
     * get soapUsername
     *
     * @return string Soap magento soapUsername
     */
    public function getSoapUsername()
    {
        return $this->soapUsername;
    }

    /**
     * get soapApiKey
     *
     * @return string Soap magento soapApiKey
     */
    public function getSoapApiKey()
    {
        return $this->soapApiKey;
    }

    /**
     * get soapUrl
     *
     * @return string Soap Url
     */
    public function getSoapUrl()
    {
        return $this->magentoUrl . $this->wsdlUrl;
    }

    /**
     * get wsdlUrl
     *
     * @return string Wsdl Url
     */
    public function getWsdlUrl()
    {
        return $this->wsdlUrl;
    }

    /**
     * get magentoUrl
     *
     * @return string Magento Domain Url
     */
    public function getMagentoUrl()
    {
        return $this->magentoUrl;
    }
}
