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

    protected $soapUsername;

    protected $soapApiKey;

    protected $soapUrl;

    protected $client;
    protected $session;

    protected $magentoAttributeSets;

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
     * Initialize the soap client with the local informations 
     * (should be refactored)
     *
     * @throws ConnectionErrorException   If the connection to the soap api fail
     * @throws InvalidCredentialException If given credentials are invalid
     * 
     * @return void
     */
    private function initClient()
    {
        // We create the soap client and its session for this writer instance
        if (!$this->client) {
            try {
                $this->client = new \SoapClient(
                    $this->soapUrl . self::SOAP_SUFFIX_URL,
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
                    $this->soapUsername, 
                    $this->soapApiKey
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
     * @return void
     */
    private function getMagentoAttributeSet()
    {
        // On first call we get the magento attribute set list 
        // (to bind them with our proctut's families)
        if (!$this->magentoAttributeSets) {
            $this->initClient();

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

    public function getMagentoAttributeSetId($code)
    {
        $this->getMagentoAttributeSet();

        if (!$this->magentoAttributeSets || !isset(
            $this->magentoAttributeSets[$familyCode]
        )) {
            throw new AttributeSetNotFoundException(
                'The attribute set for code "' . $code . '" was not found'
            );
        }
    }
}