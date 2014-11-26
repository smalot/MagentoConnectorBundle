<?php

namespace Pim\Bundle\MagentoConnectorBundle\Entity;

/**
 * Magento configuration
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoConfiguration
{
    /** @var integer */
    protected $id;

    /** @var string */
    protected $code;

    /** @var string */
    protected $label;

    /** @var string */
    protected $soapUsername;

    /** @var string */
    protected $soapApiKey;

    /** @var string */
    protected $soapUrl;

    /** @var string */
    protected $defaultStoreView;

    /** @var string */
    protected $defaultLocale;

    /** @var string */
    protected $httpLogin;

    /** @var string */
    protected $httpPassword;

    /** @var array */
    protected $rootCategoryMapping;

    /** @var array */
    protected $storeViewMapping;

    /** @var array */
    protected $attributeMapping;

    /**
     * Get identifier of the configuration
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get code of the Magento configuration
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get label of the Magento configuration
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get the label of the configuration
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the label of the configuration
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get SOAP username
     *
     * @return string SOAP magento soapUsername
     */
    public function getSoapUsername()
    {
        return $this->soapUsername;
    }

    /**
     * Set SOAP username
     *
     * @param string $soapUsername
     */
    public function setSoapUsername($soapUsername)
    {
        $this->soapUsername = $soapUsername;
    }

    /**
     * Get the SOAP API key
     *
     * @return string SOAP magento SOAP API key
     */
    public function getSoapApiKey()
    {
        return $this->soapApiKey;
    }

    /**
     * Set the SOAP API key
     *
     * @param string $soapApiKey
     */
    public function setSoapApiKey($soapApiKey)
    {
        $this->soapApiKey = $soapApiKey;
    }

    /**
     * Get the SOAP URL
     *
     * @return string SOAP URL
     */
    public function getSoapUrl()
    {
        return $this->soapUrl;
    }

    /**
     * Set the SOAP URL
     *
     * @param string $soapUrl
     */
    public function setSoapUrl($soapUrl)
    {
        $this->soapUrl = $soapUrl;
    }

    /**
     * Get the default store view
     *
     * @return string Default store view
     */
    public function getDefaultStoreView()
    {
        return $this->defaultStoreView;
    }

    /**
     * Set the default store view
     *
     * @param string $defaultStoreView
     */
    public function setDefaultStoreView($defaultStoreView)
    {
        $this->defaultStoreView = $defaultStoreView;
    }

    /**
     * Get default locale
     *
     * @return string
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * Set default locale
     *
     * @param string $defaultLocale
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Get the HTTP authentication login
     *
     * @return string HTTP login
     */
    public function getHttpLogin()
    {
        return $this->httpLogin;
    }

    /**
     * Set the HTTP authentication login
     *
     * @param string $httpLogin
     */
    public function setHttpLogin($httpLogin)
    {
        $this->httpLogin = $httpLogin;
    }

    /**
     * Get the HTTP authentication password
     *
     * @return string HTTP password
     */
    public function getHttpPassword()
    {
        return $this->httpPassword;
    }

    /**
     * Set the HTTP authentication password
     *
     * @param string $httpPassword
     */
    public function setHttpPassword($httpPassword)
    {
        $this->httpPassword = $httpPassword;
    }

    /**
     * Get the attributes mapping
     *
     * @return array
     */
    public function getAttributeMapping()
    {
        return $this->attributeMapping;
    }

    /**
     * Set the attributes mapping
     *
     * @param array $attributeMapping
     */
    public function setAttributeMapping($attributeMapping)
    {
        $this->attributeMapping = $attributeMapping;
    }

    /**
     * Get root categories mapping
     *
     * @return array
     */
    public function getRootCategoryMapping()
    {
        return $this->rootCategoryMapping;
    }

    /**
     * Set root categories mapping
     *
     * @param array $rootCategoryMapping
     */
    public function setRootCategoryMapping($rootCategoryMapping)
    {
        $this->rootCategoryMapping = $rootCategoryMapping;
    }

    /**
     * Get store views mapping
     *
     * @return array
     */
    public function getStoreViewMapping()
    {
        return $this->storeViewMapping;
    }

    /**
     * Set store views mapping
     *
     * @param array $storeViewMapping
     */
    public function setStoreViewMapping($storeViewMapping)
    {
        $this->storeViewMapping = $storeViewMapping;
    }
}
