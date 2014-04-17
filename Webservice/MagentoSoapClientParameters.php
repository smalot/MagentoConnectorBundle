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

    protected $soapUsername;

    protected $soapApiKey;

    protected $soapUrl;

    /**
     * Constructor
     *
     * @param string $soapUsername Magento soap username
     * @param string $soapApiKey   Magento soap api key
     * @param string $soapUrl      Magento soap url (only the domain)
     */
    public function __construct($soapUsername, $soapApiKey, $soapUrl)
    {
        $this->soapUsername = $soapUsername;
        $this->soapApiKey   = $soapApiKey;
        $this->soapUrl      = $soapUrl . self::SOAP_WSDL_URL ;
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
     * @return string magento soap url
     */
    public function getSoapUrl()
    {
        return $this->soapUrl;
    }
}
