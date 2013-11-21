<?php 

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

class MagentoSoapClientParameters
{
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
        $this->soapUrl      = $soapUrl;
    }

    /**
     * get soapUsername
     * 
     * @return string Soap mangeto soapUsername
     */
    public function getSoapUsername() 
    {
        return $this->soapUsername;
    }

    /**
     * get soapApiKey
     * 
     * @return string Soap mangeto soapApiKey
     */
    public function getSoapApiKey() 
    {
        return $this->soapApiKey;
    }

    /**
     * get soapUrl
     * 
     * @return string mangeto soap url
     */
    public function getSoapUrl() 
    {
        return $this->soapUrl;
    }
}