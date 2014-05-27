<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClientParametersRegistry
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
     * @var string Default store view
     */
    protected $defaultStoreView;

    /**
     * @var string Login for http authentication
     */
    protected $httpLogin;

    /**
     * @var string Password for http authentication
     */
    protected $httpPassword;

    /**
     * @var boolean If is valid parameters or not
     */
    protected $isValid;

    /**
     * @var array Contains a unique instance of each group of parameters, identified by md5 hash.
     */
    private static $instance;

    private function __construct(
        $soapUsername,
        $soapApiKey,
        $magentoUrl,
        $wsdlUrl,
        $defaultStoreView = Webservice::SOAP_DEFAULT_STORE_VIEW,
        $httpLogin = null,
        $httpPassword = null
    ) {
        $this->soapUsername     = $soapUsername;
        $this->soapApiKey       = $soapApiKey;
        $this->magentoUrl       = $magentoUrl;
        $this->wsdlUrl          = $wsdlUrl;
        $this->httpLogin        = $httpLogin;
        $this->httpPassword     = $httpPassword;
        $this->defaultStoreView = $defaultStoreView;
        $this->isValid          = null;
    }

    /**
     *
     * @param array $soapParameters Associative array which contains soap parameters
     * @return type
     */
    public static function getInstance(
        $soapUsername,
        $soapApiKey,
        $magentoUrl,
        $wsdlUrl,
        $defaultStoreView,
        $httpLogin = null,
        $httpPassword = null
    ) {
        $hash = md5(
            $soapUsername.
            $soapApiKey.
            $magentoUrl.
            $wsdlUrl.
            $defaultStoreView.
            $httpLogin.
            $httpPassword
        );

        if (!isset(self::$instance[$hash])) {
            self::$instance[$hash] = new self(
                $soapUsername,
                $soapApiKey,
                $magentoUrl,
                $wsdlUrl,
                $defaultStoreView,
                $httpLogin = null,
                $httpPassword = null
            );
        }

        return self::$instance[$hash];
    }

    /**
     * get hash to uniquely identify parameters even in different instances
     *
     * @return string
     */
    public function getHash()
    {
        return md5(
            $this->soapUsername.
            $this->soapApiKey.
            $this->magentoUrl.
            $this->wsdlUrl.
            $this->httpLogin.
            $this->httpPassword
        );
    }

    /**
     * Get soapUsername
     *
     * @return string Soap magento soapUsername
     */
    public function getSoapUsername()
    {
        return $this->soapUsername;
    }

    /**
     * Get the soap api key
     *
     * @return string Soap magento soapApiKey
     */
    public function getSoapApiKey()
    {
        return $this->soapApiKey;
    }

    /**
     * Get the soap Url
     *
     * @return string Soap Url
     */
    public function getSoapUrl()
    {
        return $this->magentoUrl . $this->wsdlUrl;
    }

    /**
     * Get the wsdl Url
     *
     * @return string Wsdl Url
     */
    public function getWsdlUrl()
    {
        return $this->wsdlUrl;
    }

    /**
     * Get the magento Url
     *
     * @return string Magento Domain Url
     */
    public function getMagentoUrl()
    {
        return $this->magentoUrl;
    }

    public function getDefaultStoreView()
    {
        return $this->defaultStoreView;
    }

    /**
     * Get the http authentication login
     *
     * @return string Http login
     */
    public function getHttpLogin()
    {
        return $this->httpLogin;
    }

    /**
     * Get the http authentication password
     *
     * @return string Http password
     */
    public function getHttpPassword()
    {
        return $this->httpPassword;
    }

    /**
     * Get the state of validation
     *
     * @return boolean Is valid or not
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * Allow to change the state of validation
     *
     * @param boolean $state Is valid or not
     */
    public function setValidation($state)
    {
        $this->isValid = $state;
    }
}
