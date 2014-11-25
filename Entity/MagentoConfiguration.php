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
    /** @var integer Identifier of the configuration */
    protected $id;

    /** @var string Code of the Magento job configuration */
    protected $code;

    /** @var string Label of the Magento job configuration */
    protected $label;

    /** @var string Soap username */
    protected $soapUsername;

    /** @var string Soap API key */
    protected $soapApiKey;

    /** @var string Soap URL */
    protected $soapUrl;

    /** @var string Default store view */
    protected $defaultStoreView;

    /** @var string Default locale */
    protected $defaultLocale;

    /** @var string Login for http authentication */
    protected $httpLogin;

    /** @var string Password for http authentication */
    protected $httpPassword;

    /** @var array Root categories mapping */
    protected $rootCategoryMapping;

    /** @var array Store views mapping */
    protected $storeViewMapping;

    /** @var array Attributes mapping */
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
     * Get soap username
     *
     * @return string Soap magento soapUsername
     */
    public function getSoapUsername()
    {
        return $this->soapUsername;
    }

    /**
     * Set soap username
     *
     * @param string
     */
    public function setSoapUsername($soapUsername)
    {
        $this->soapUsername = $soapUsername;
    }

    /**
     * Get the soap API key
     *
     * @return string Soap magento soap API key
     */
    public function getSoapApiKey()
    {
        return $this->soapApiKey;
    }

    /**
     * Set the soap API key
     *
     * @param string
     */
    public function setSoapApiKey($soapApiKey)
    {
        $this->soapApiKey = $soapApiKey;
    }

    /**
     * Get the soap URL
     *
     * @return string Soap URL
     */
    public function getSoapUrl()
    {
        return $this->soapUrl;
    }

    /**
     * Set the soap URL
     *
     * @param string
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
     * @param string
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
     * @param string
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
     * @param array
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
     * @param array
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
     * @param array
     */
    public function setStoreViewMapping($storeViewMapping)
    {
        $this->storeViewMapping = $storeViewMapping;
    }
}
