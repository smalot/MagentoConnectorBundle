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
    const SOAP_SUFFIX_URL = '/api/soap/?wsdl';

    const SOAP_ACTION_CATALOG_PRODUCT_CREATE        = 'catalog_product.create';
    const SOAP_ACTION_CATALOG_PRODUCT_UPDATE        = 'catalog_product.update';
    const SOAP_ACTION_CATALOG_PRODUCT_CURRENT_STORE = 
        'catalog_product.currentStore';
    const SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST = 'product_attribute_set.list';

    protected $clientParameters;

    protected $client;
    protected $session;

    protected $calls;

    protected $magentoAttributeSets;


    /**
     * Manage soap client parameters
     *
     * @throws Exception If there is no $clientParameters nor $this->clientParameters
     * 
     * @param MagentoSoapClientParameters $clientParameters soap parameters
     */
    private function setClientParameters($clientParameters = null)
    {
        if (!$clientParameters) {
            if (!$this->clientParameters) {
                throw new \Exception('No soap client parameters given'); 
            }
        } else {
            $this->clientParameters = $clientParameters;
        }
    }

    /**
     * Initialize the soap client with the local informations 
     * (should be refactored)
     *
     * @param MagentoSoapClientParameters $clientParameters soap parameters
     * 
     * @throws ConnectionErrorException   If the connection to the soap api fail
     * @throws InvalidCredentialException If given credentials are invalid
     * 
     * @return void
     */
    private function initClient($clientParameters = null)
    {
        // We create the soap client and its session for this writer instance
        if (!$this->client) {
            $this->setClientParameters($clientParameters);

            try {
                $this->client = new \SoapClient(
                    $this->clientParameters->getSoapUrl() . 
                        self::SOAP_SUFFIX_URL,
                    array(
                        'encoding' => 'UTF-8'
                    )
                );
            } catch (\Exception $e) {
                //We should create a proper exception
                throw new ConnectionErrorException(
                    'The soap connection could not be established with the ' .
                    'error message : ' . $e->getMessage()
                );
            }
            
            try {
                $this->session = $this->client->login(
                    $this->clientParameters->getSoapUsername(), 
                    $this->clientParameters->getSoapApiKey()
                );
            } catch (\Exception $e) {
                throw new InvalidCredentialException(
                    'The given credential are invalid or not allowed to ' .
                    'connect to the soap api. Error message : ' . 
                    $e->getMessage()
                );
            }
        }
    }

    /**
     * Get the magento attributeSet list from the magento platform
     * 
     * @param MagentoSoapClientParameters $clientParameters soap parameters
     * 
     * @return void
     */
    private function getMagentoAttributeSet($clientParameters = null)
    {
        // On first call we get the magento attribute set list 
        // (to bind them with our proctut's families)
        if (!$this->magentoAttributeSets) {
            $this->initClient($clientParameters);

            $attributeSets = $this->client->call(
                $this->session, 
                self::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST
            );

            foreach ($attributeSets as $attributeSet) {
                $this->magentoAttributeSets[$attributeSet['name']] =
                    $attributeSet['set_id'];
            }
        }
    }

    /**
     * Get magento attributeSets from the magento api
     * 
     * @param  string                      $code             the attributeSet id
     * @param  MagentoSoapClientParameters $clientParameters soap parameters
     * @return void
     */
    public function getMagentoAttributeSetId($code, $clientParameters = null)
    {
        $this->getMagentoAttributeSet($clientParameters);

        if ($this->magentoAttributeSets && isset($this->magentoAttributeSets[$code])) {
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
     * @param string                      $name             the storeview name
     * @param MagentoSoapClientParameters $clientParameters soap parameters
     */
    public function setCurrentStoreView($name, $clientParameters = null)
    {
        $this->initClient($clientParameters);

        $this->client->call(
            $this->session, 
            self::SOAP_ACTION_CATALOG_PRODUCT_CURRENT_STORE, 
            $name
        );
    }

    /**
     * Add a call to the soap call stack
     * 
     * @param array                       $call             a magento soap call
     * @param MagentoSoapClientParameters $clientParameters soap paramters
     */
    public function addCall($call, $clientParameters = null)
    {
        $this->initClient($clientParameters);

        $this->calls[] = $call;
    }

    public function flush($clientParameters = null)
    {
        $this->initClient($clientParameters);

        if (count($this->calls) > 0) {
            $this->client->multiCall(
                $this->session, 
                $this->calls
            );

            $this->calls = array();
        }
    }
}