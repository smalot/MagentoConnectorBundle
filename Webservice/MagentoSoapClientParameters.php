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
     * Set soapUsername
     * 
     * @param string $soapUsername Soap mangeto soapUsername
     */
    public function setSoapUsername($soapUsername) 
    {
        $this->soapUsername = $soapUsername;

        return $this;
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
     * Set soapApiKey
     * 
     * @param string $soapApiKey Soap mangeto soapApiKey
     */
    public function setSoapApiKey($soapApiKey) 
    {
        $this->soapApiKey = $soapApiKey;

        return $this;
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

    /**
     * Set soapUrl
     * 
     * @param string $soapUrl mangeto soap url
     */
    public function setSoapUrl($soapUrl) 
    {
        $this->soapUrl = $soapUrl;

        return $this;
    }
}