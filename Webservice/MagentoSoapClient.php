<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * A magento soap client to abstract interaction with the magento api
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClient
{
    const SOAP_WSDL_URL = '/api/soap/?wsdl';

    const SOAP_ACTION_CATALOG_PRODUCT_CREATE        = 'catalog_product.create';
    const SOAP_ACTION_CATALOG_PRODUCT_UPDATE        = 'catalog_product.update';
    const SOAP_ACTION_CATALOG_PRODUCT_CURRENT_STORE =
        'catalog_product.currentStore';
    const SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST    = 'product_attribute_set.list';

    protected $clientParameters;

    protected $client;
    protected $session;

    protected $calls;

    protected $magentoAttributeSets;

    /**
     * Init the service with credentials and soap url
     *
     * @param  MagentoSoapClientParameters $clientParameters Soap parameters
     */
    public function init(MagentoSoapClientParameters $clientParameters)
    {
        $this->setParameters($clientParameters);

        if (!$this->isConnected()) {
            $wsdlUrl = $this->clientParameters->getSoapUrl() .
                self::SOAP_WSDL_URL;
            $soapOptions = array('encoding' => 'UTF-8', 'trace' => 1);

            try {
                $client = new \SoapClient($wsdlUrl, $soapOptions);
            } catch (\Exception $e) {
                throw new ConnectionErrorException(
                    'The soap connection could not be established',
                    $e->getCode(),
                    $e
                );
            }

            $this->setClient($client);
            $this->connect();
        }
    }

    /**
     * Set the soap client
     *
     * @param SoapClient $soapClient the soap client
     */
    public function setClient($soapClient)
    {
        $this->client = $soapClient;
    }

    /**
     * Set the soap client parameters
     *
     * @param  MagentoSoapClientParameters $clientParameters Soap parameters
     */
    public function setParameters(MagentoSoapClientParameters $clientParameters)
    {
        $this->clientParameters = $clientParameters;
    }

    /**
     * Initialize the soap client with the local informations
     *
     * @throws ConnectionErrorException   If the connection to the soap api fail
     * @throws InvalidCredentialException If given credentials are invalid
     */
    public function connect()
    {
        if ($this->clientParameters) {
            get_class($this->client);
            try {
                $this->session = $this->client->login(
                    $this->clientParameters->getSoapUsername(),
                    $this->clientParameters->getSoapApiKey()
                );
            } catch (\Exception $e) {
                throw new InvalidCredentialException(
                    'The given credential are invalid or not allowed to ' .
                    'connect to the soap api.',
                    $e->getCode(),
                    $e
                );
            }
        } else {
            throw new ConnectionErrorException(
                'Invalid state : you need to call the init method first'
            );
        }
    }

    /**
     * Get the magento attributeSet list from the magento platform
     *
     * @return void
     */
    private function getMagentoAttributeSet()
    {
        // On first call we get the magento attribute set list
        // (to bind them with our proctut's families)
        if (!$this->magentoAttributeSets) {
            $attributeSets = $this->call(
                self::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST
            );

            foreach ($attributeSets as $attributeSet) {
                $this->magentoAttributeSets[$attributeSet['name']] =
                    $attributeSet['set_id'];
            }
        }
    }

    /**
     * Is the soap client connected ?
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->client && $this->session;
    }

    /**
     * Get magento attributeSets from the magento api
     *
     * @param  string $code the attributeSet id
     * @return void
     */
    public function getMagentoAttributeSetId($code)
    {
        if (!$this->magentoAttributeSets) {
            $this->getMagentoAttributeSet();
        }

        if (isset($this->magentoAttributeSets[$code])) {
            return $this->magentoAttributeSets[$code];
        } else {
            throw new AttributeSetNotFoundException(
                'The attribute set for code "' . $code . '" was not found'
            );
        }
    }

    /**
     * Set the current view store on the magento platform
     *
     * @param string $name the storeview name
     */
    public function setCurrentStoreView($name)
    {
        $this->call(
            self::SOAP_ACTION_CATALOG_PRODUCT_CURRENT_STORE,
            $name
        );
    }

    /**
     * Add a call to the soap call stack
     *
     * @param array $call a magento soap call
     */
    public function addCall(array $call)
    {
        $this->calls[] = $call;
    }

    public function sendCalls()
    {
        if (count($this->calls) > 0) {
            if ($this->isConnected()) {
                $response = $this->client->multiCall(
                    $this->session,
                    $this->calls
                );

                $this->dumpSoapResponse($response);
            } else {
                throw new NotConnectedException();
            }

            $this->calls = array();
        }
    }

    public function call($resource, $params = null)
    {
        if ($this->isConnected()) {
            $response = $this->client->call(
                $this->session,
                $resource,
                $params
            );

            $this->dumpSoapResponse($response);

            return $response;
        } else {
            throw new NotConnectedException();
        }
    }

    public function dumpSoapResponse($response)
    {
        print_r($response);
        print_r('last response ' . $this->client->__getLastResponse());
    }
}