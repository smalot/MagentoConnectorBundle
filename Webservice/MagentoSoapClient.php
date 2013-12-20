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
    const SOAP_ACTION_CATALOG_PRODUCT_CURRENT_STORE = 'catalog_product.currentStore';
    const SOAP_ACTION_CATALOG_PRODUCT_LIST          = 'catalog_product.list';
    const SOAP_ACTION_PRODUCT_ATTRIBUTE_OPTIONS     = 'catalog_product_attribute.options';
    const SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST    = 'product_attribute_set.list';
    const SOAP_ACTION_PRODUCT_ATTRIBUTE_LIST        = 'catalog_product_attribute.list';
    const SOAP_ACTION_STORE_LIST                    = 'store.list';
    const SOAP_ACTION_PRODUCT_MEDIA_CREATE          = 'product_media.create';
    const SOAP_ACTION_PRODUCT_MEDIA_LIST            = 'catalog_product_attribute_media.list';
    const SOAP_ACTION_PRODUCT_MEDIA_REMOVE          = 'catalog_product_attribute_media.remove';

    const SOAP_DEFAULT_STORE_VIEW                   = 'default';
    const IMAGES                                    = 'images';
    const SOAP_ATTRIBUTE_ID                         = 'attribute_id';
    const SMALL_IMAGE                               = 'small_image';
    const SELECT                                    = 'select';
    const MULTI_SELECT                              = 'multiselect';

    protected $clientParameters;

    protected $client;
    protected $session;

    protected $calls;

    protected $magentoAttributeSets;
    protected $magentoStoreViewList;
    protected $magentoAttributes = array();

    protected $attributeList       = array();
    protected $attributes          = array();
    protected $attributeSetList    = array();
    protected $attributeOptionList = array();

    /**
     * Init the service with credentials and soap url
     *
     * @param  MagentoSoapClientParameters $clientParameters Soap parameters
     */
    public function init(MagentoSoapClientParameters $clientParameters)
    {
        $this->setParameters($clientParameters);

        if (!$this->isConnected()) {
            $wsdlUrl     = $this->clientParameters->getSoapUrl() . self::SOAP_WSDL_URL;
            $soapOptions = array('encoding' => 'UTF-8', 'trace' => 1, 'exceptions' => true);

            try {
                $this->client = new \SoapClient($wsdlUrl, $soapOptions);
            } catch (\Exception $e) {
                throw new ConnectionErrorException(
                    'The soap connection could not be established',
                    $e->getCode(),
                    $e
                );
            }

            $this->connect();
        }
    }

    /**
     * Set the soap client
     *
     * @param SoapClient $client the soap client
     */
    public function setClient($client)
    {
        $this->client = $client;
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
    protected function getAttributeSetList()
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

        return $this->magentoAttributeSets;
    }

    /**
     * Get options for the given attribute
     *
     * @param  string $attributeCode Attribute code
     * @return array the formated options for the given attribute
     */
    protected function getAttributeOptions($attributeCode)
    {
        $options = $this->call(
            self::SOAP_ACTION_PRODUCT_ATTRIBUTE_OPTIONS,
            array($attributeCode)
        );

        $formatedOptions = array();

        foreach ($options as $option) {
            $formatedOptions[$option['label']] = $option['value'];
        }

        return $formatedOptions;
    }

    /**
     * Get attribute options for all attributes
     *
     * @return array
     */
    public function getAllAttributesOptions()
    {
        $attributeList = $this->getAllAttributes();

        foreach ($attributeList as $attributeCode => $attribute) {
            if (in_array($attribute['type'], array(self::SELECT, self::MULTI_SELECT))) {
                $this->attributeOptionList[$attributeCode] = $this->getAttributeOptions($attributeCode);
            }
        }

        return $this->attributeOptionList;
    }

    /**
     * Get product status in magento (do they exist ?)
     * @param  Product $products the given products
     * @return array
     */
    public function getProductsStatus($products)
    {
        $productsIds = $this->getProductsIds($products);

        $condition        = new \StdClass();
        $condition->key   = 'in';
        $condition->value = $productsIds;

        $fieldFilter        = new \StdClass();
        $fieldFilter->key   = 'sku';
        $fieldFilter->value = $condition;

        $filters = new \StdClass();
        $filters->complex_filter = array(
            $fieldFilter
        );

        return $this->call(
            self::SOAP_ACTION_CATALOG_PRODUCT_LIST,
            $filters
        );
    }

    /**
     * Serialize products id in csv
     *
     * @param  array $products The given products
     * @return string The serialization result
     */
    protected function getProductsIds($products)
    {
        $ids = '';

        foreach ($products as $product) {
            $ids .= $product->getIdentifier() . ',';
        }

        return substr($ids, 0, strlen($ids) - 1);
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
    public function getAttributeSetId($code)
    {
        $this->getAttributeSetList();

        if (isset($this->magentoAttributeSets[$code])) {
            return $this->magentoAttributeSets[$code];
        } else {
            throw new AttributeSetNotFoundException(
                'The attribute set for code "' . $code . '" was not found'
            );
        }
    }

    /**
     * Get magento storeview list from magento
     *
     * @return array
     */
    public function getStoreViewsList()
    {
        if (!$this->magentoStoreViewList) {
            $this->magentoStoreViewList = $this->call(
                self::SOAP_ACTION_STORE_LIST
            );
        }

        return $this->magentoStoreViewList;
    }

    /**
     * Get all attributes from magento
     *
     * @return array
     */
    public function getAllAttributes()
    {
        if (!$this->attributeList) {
            $attributeSetList = $this->getAttributeSetList();

            foreach (array_keys($attributeSetList) as $attributeSet) {
                $attributes = $this->getAttributeList($attributeSet);
                $this->attributeSetList[$attributeSet] = array();

                foreach ($attributes as $attribute) {
                    $this->attributeList[$attribute['code']]                = $attribute;
                    $this->attributeSetList[$attributeSet][$attributeSet]   = $attribute['code'];
                }
            }
        }

        return $this->attributeList;
    }

    /**
     * Get attribute list for a given attribute set code
     *
     * @param string $attributeSetId the attribute set id
     */
    public function getAttributeList($attributeSetCode)
    {
        if (!isset($this->attributes[$attributeSetCode])) {
            $id = $this->getAttributeSetId($attributeSetCode);

            $this->attributes[$attributeSetCode] = $this->call(
                self::SOAP_ACTION_PRODUCT_ATTRIBUTE_LIST,
                $id
            );
        }

        return $this->attributes[$attributeSetCode];
    }

    /**
     * Get all images attached to a product
     *
     * @param  string $sku The product sku
     * @return array
     */
    public function getImages($sku)
    {
        try {
            $images = $this->call(self::SOAP_ACTION_PRODUCT_MEDIA_LIST, $sku);
        } catch (\Exception $e) {
            $images = array();
        }

        return $images;
    }

    /**
     * Delete image for a given sku and a given filename
     * @param  string $sku
     * @param  string $imageFilename
     * @return string
     */
    public function deleteImage($sku, $imageFilename)
    {
        return $this->call(self::SOAP_ACTION_PRODUCT_MEDIA_REMOVE, array('product' => $sku, 'file' => $imageFilename));
    }

    /**
     * Add a call to the soap call stack
     *
     * @param array   $call         A magento soap call
     * @param integer $maximumCalls Send calls envery maximumCalls
     */
    public function addCall(array $call, $maximumCalls = 0)
    {
        $this->calls[] = $call;

        if ($maximumCalls > 0 && (count($this->calls) % $maximumCalls) == 0) {
            $this->sendCalls();
        }
    }

    /**
     * Send pending calls to the magento soap api (with multiCall function)
     *
     * @return mixed The soap response
     */
    public function sendCalls()
    {
        if (count($this->calls) > 0) {
            if ($this->isConnected()) {
                $responses = $this->client->multiCall(
                    $this->session,
                    $this->calls
                );

                foreach ($responses as $response) {
                    $this->processSoapResponse($response);
                }
            } else {
                throw new NotConnectedException();
            }

            $this->calls = array();
        }
    }

    /**
     * Call the soap api on the given resource
     *
     * @param  string $resource The resource to call
     * @param  array  $params   Parameters
     * @return string The soap response
     */
    public function call($resource, $params = null)
    {
        if ($this->isConnected()) {
            $response = $this->client->call(
                $this->session,
                $resource,
                $params
            );

            return $response;
        } else {
            throw new NotConnectedException();
        }
    }

    /**
     * Process the soap response
     *
     * @param  mixed $response The soap response-
     */
    public function processSoapResponse($response)
    {
        if (is_array($response)) {
            if (isset($response['isFault']) && $response['isFault'] == 1) {

            }
        } else {
            if ($response == 1) {

            }
        }
    }
}